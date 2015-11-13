<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
// yaml parser
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;
// aetools
use site\services\aetools;

/**
 * Service aeMenus
 * - Gestion des menus en YAML
 */
class aeMenus {

	const ARRAY_GLUE = '___';
	const SOURCE_FILES = 'src/';
	const FOLD_RESOURCES = 'Resources';
	const FOLD_MENU = 'menus';
	const BUNDLE_EXTENSION = 'Bundle';
	const GO_TO_ROOT = '/../../../../';
	const MAX_YAML_LEVEL = 10;

	protected $container; 			// container
	protected $aetools; 			// aetools
	protected $languages; 			// array des langues disponibles --> config.yml --> default_locales: "fr|en|en_US|es|it|de"
	protected $bundlesLanguages;	// array des langues disponibles par bundles --> config.yml --> list_locales: "fr|en", etc.
	protected $default_locale; 		// string locale par défaut --> config.yml --> locale: "en"
	protected $fold_menu;			// liste des dossiers contenant les fichiers de traduction
	protected $bundles_list;		// array des bundles/path : $array(bundle => path)
	protected $files_list;			// array des fichiers, classés par bundles
	protected $rootPath;			// Dossier root du site

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
		$this->aetools = new aetools();
		$this->rootPath = __DIR__.self::GO_TO_ROOT;
		$this->aetools->setRootPath("/");
		// récupération de fichiers et check
		$this->initFiles();
	}

	protected function initFiles() {
		// initialisation
		$this->bundles_list = array();
		$this->files_list = array();
		// récupération des dossiers "menus", enfants DIRECTS des dossiers "Resources", uniquement dans "src"
		$fold_resources = $this->aetools->exploreDir(self::SOURCE_FILES, self::FOLD_RESOURCES, "dossiers");
		$this->fold_menu = array();
		foreach ($fold_resources as $fR) {
			$res = $this->aetools->exploreDir($fR['sitepath'].$fR['nom'], self::FOLD_MENU, "dossiers", false); // false --> enfants directs
			if(count($res) > 0) foreach ($res as $folder) {
				$this->fold_menu[] = $folder;
			}
		}
		foreach($this->fold_menu as $folder) {
			$path = $folder['sitepath'].$folder['nom'];
			// constitution de la liste des bundles
			$bundle = $this->getBundle($path);
			$this->bundles_list[$bundle] = $path;
			// recherche des fichiers
			$fileslist = $this->getMenuFiles($path);
			if(count($fileslist) > 0) foreach ($fileslist as $key => $file) {
				// ajout de données
				$name = preg_replace('#\.yml$#', '', $file['nom']);
				$this->files_list[$bundle][$name] = $file;
				$this->files_list[$bundle][$name]['statut_message'] = 'traduction.file_found';
				$this->files_list[$bundle][$name]['statut'] = 1;
				$this->files_list[$bundle][$name]['roles'] = $this->getRightsOfMenu($bundle, $name);
			}
		}
		// echo('<pre>');
		// // echo('<h2>fold_menu</h2>');
		// // var_dump($this->fold_menu);
		// // echo('<h2>bundles_list</h2>');
		// // var_dump($this->bundles_list);
		// echo('<h2>files_list</h2>');
		// var_dump($this->files_list);
		// die('</pre>');
		return $this->files_list;
	}

	/**
	 * Renvoie le menu défini dans les paramètres du site (config.yml -> menus:)
	 * @param string $path
	 * @return array
	 */
	public function getMenu($name) {
		$menus = $this->container->getParameter('menus');
		if(isset($menus[$name])) {
			return $this->getInfoMenu($menus[$name]['bundle'], $menus[$name]['name']);
		} else throw new Exception("Menu ".$name." non présent dans les paramètres de site.", 1);
	}

	/**
	 * Renvoie la liste des fichiers de translation (yaml) contenus dans le dossier $path
	 * @param string $path
	 * @return array
	 */
	protected function getMenuFiles($path) {
		return $this->aetools->exploreDir($path, "\.yml$", "fichiers", false, true);
	}


	/**
	 * Renvoie les informations sur les menus
	 * @param string $bundle
	 * @return array
	 */
	public function getInfoMenus($bundle = null) {
		if($bundle != null && isset($this->files_list[$bundle])) {
			foreach ($this->files_list[$bundle] as $menuName => $menu) {
				$this->getInfoMenu($bundleName, $menuName);
			}
			return $this->files_list[$bundle];
		} else {
			foreach ($this->files_list as $bundleName => $bundle) {
				foreach ($bundle as $menuName => $menu) {
					$this->getInfoMenu($bundleName, $menuName);
				}
			}
			return $this->files_list;
		}
	}

	/**
	 * Renvoie les informations sur le menu $bundle/$name
	 * @param string $bundle
	 * @param string $name
	 * @return array
	 */
	public function getInfoMenu($bundle, $name) {
		if(isset($this->files_list[$bundle][$name])) {
			$this->files_list[$bundle][$name]['menu'] = $this->parse_yaml_fromFile($this->files_list[$bundle][$name]['full']);
			return $this->files_list[$bundle][$name];
		}
		return false;
	}

	/**
	 * Renvoie les droits sur le menu $bundle/$name
	 * @param string $bundle
	 * @param string $name
	 * @return array
	 */
	public function getRightsOfMenu($bundle, $name) {
		$default_roles = array(
			'view' => 'ROLE_TRANSLATOR',
			'edit' => 'ROLE_EDITOR',
			'delete' => 'ROLE_ADMIN',
			);
		if(isset($this->files_list[$bundle][$name])) {
			$menu = $this->parse_yaml_fromFile($this->files_list[$bundle][$name]['full']);
			// Defaults
			if(!isset($menu['roles'])) $menu['roles'] = $default_roles;
			foreach ($default_roles as $action => $role) {
				if(!isset($menu['roles'][$action])) $menu['roles'][$action] = $role;
			}
			return $menu['roles'];
		}
		return false;
	}

	/**
	 * Lecture d'un fichier d'après son path
	 * @param string $path
	 * @return array
	 */
	protected function parse_yaml_fromFile($path) {
		if(!file_exists($path)) return false;
		$yaml = new Parser();
		try {
			$parse = $yaml->parse(file_get_contents($path));
		} catch (ParseException $e) {
			$parse = $e->getMessage();
		}
		return $parse;
	}

	/**
	 * Écriture un fichier d'après path
	 * @param string $path
	 * @return boolean (nb d'octets si success)
	 */
	protected function dump_yaml_toFile($path, $array, $init = true) {
		$dumper = new Dumper();
		$r = file_put_contents(
			$path,
			$dumper->dump($array, self::MAX_YAML_LEVEL)
			);
		if($init) $this->initFiles();
		return $r;
	}



	/**
	 * Renvoie la liste de tous les fichiers
	 * @return array
	 */
	public function getFiles() {
		$liste = array();
		foreach ($this->files_list as $bundle => $files) {
			foreach ($files as $file) {
				$liste[] = $file;
			}
		}
		return $liste;
	}

	/**
	 * Renvoie la liste de tous les menus
	 * @return array
	 */
	public function getMenusNames($byBundle = false) {
		$liste = array();
		foreach ($this->files_list as $bundle => $files) {
			if($byBundle) $liste[$bundle] = array();
			foreach ($files as $name => $file) {
				$byBundle ?
					$liste[$bundle][$name] = $name : $liste[$name] = $name ;
			}
		}
		return $liste;
	}

	/**
	 * Liste des bundles
	 * @return array
	 */
	public function getBundles() {
		return array_keys($this->bundles_list);
	}

	/**
	 * Liste des paths des bundles
	 * @return array
	 */
	public function getPathsBundles() {
		return $this->bundles_list;
	}

	/**
	 * Renvoie le path complet d'un bundle
	 * @return array
	 */
	public function getFullPathBundle($bundle) {
		return isset($this->bundles_list[$bundle]) ? $this->rootPath.$this->bundles_list[$bundle] : false;
	}

	/**
	 * Renvoie le nom du bundle d'après le path
	 * @param string $path
	 * @return array
	 */
	public function getBundle($path) {
		return strtolower(str_replace(array(self::FOLD_RESOURCES, self::FOLD_MENU, self::SOURCE_FILES, self::BUNDLE_EXTENSION, '/'), '', $path));
	}

	/**
	 * Renvoie le path d'un bundle
	 * @param string $bundle
	 * @return array
	 */
	public function getPath($bundle) {
		return isset($this->bundles_list[$bundle]) ? $this->bundles_list[$bundle] : false;
	}


	/**
	 * Change l'ordre des éléments dans un menu et enregistre
	 * @param string $bundle
	 * @param string $name
	 * @param array $data
	 * @return string
	 */
	public function changeOrderInFile($bundle, $name, $data) {
		$menu = $this->parse_yaml_fromFile($this->files_list[$bundle][$name]['full']);
		$menu['menu'] = $this->reorder($this->getFlatItemsOfMenu($menu['menu']), $data);
		$result = $this->dump_yaml_toFile($this->files_list[$bundle][$name]['full'], $menu, false);
		return $result ? $menu['menu'] : $result;
	}

	protected function reorder($flatmenu, $data) {
		$recursmenu = array();
		// $flatmenu = $this->getFlatItemsOfMenu($menu);
		foreach ($data as $key => $item) {
			$recursmenu[$item['id']] = $flatmenu[$item['id']];
			if(isset($item['children'])) $recursmenu[$item['id']]['items'] = $this->reorder($flatmenu, $item['children']);
		}
		return $recursmenu;
	}

	protected function getFlatItemsOfMenu($menu) {
		$items = array();
		foreach ($menu as $id => $item) {
			$items[$id] = $item;
			if(isset($item['items'])) {
				$items = $items + $this->getFlatItemsOfMenu($item['items']);
				unset($items[$id]['items']);
			}
		}
		return $items;
	}


}