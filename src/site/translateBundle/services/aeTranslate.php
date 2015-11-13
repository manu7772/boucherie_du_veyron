<?php
namespace site\translateBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
// yaml parser
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;
// aetools
use site\services\aetools;

/**
 * Service aeTranslate
 * - Gestion des traductions
 */
class aeTranslate {

	const ARRAY_GLUE = '___';
	const SOURCE_FILES = 'src/';
	const FOLD_RESOURCES = 'Resources';
	const FOLD_TRANSLATIONS = 'translations';
	const BUNDLE_EXTENSION = 'Bundle';
	const GO_TO_ROOT = '/../../../../';
	const MAX_YAML_LEVEL = 10;

	protected $container; 			// container
	protected $aetools; 			// aetools
	protected $languages; 			// array des langues disponibles --> config.yml --> default_locales: "fr|en|en_US|es|it|de"
	protected $bundlesLanguages;	// array des langues disponibles par bundles --> config.yml --> list_locales: "fr|en", etc.
	protected $default_locale; 		// string locale par défaut --> config.yml --> locale: "en"
	protected $fold_translations;	// liste des dossiers contenant les fichiers de traduction
	protected $bundles_list;		// array des bundles/path : $array(bundle => path)
	protected $files_list;			// array des fichiers, classés par bundles
	protected $domains;				// array des domaines, classés par bundles
	protected $rootPath;			// Dossier root du site

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
		$this->aetools = new aetools();
		$this->rootPath = __DIR__.self::GO_TO_ROOT;
		$this->aetools->setRootPath("/");
		// langues
		$this->getLanguages();
		$this->getDefaultLocale();
		// récupération de fichiers et check
		$this->initFiles();
		// $this->verifData();
	}

	protected function initFiles() {
		// initialisation
		$this->bundles_list = array();
		$this->files_list = array();
		// récupération des dossiers "translations", enfants DIRECTS des dossiers "Resources", uniquement dans "src"
		$fold_resources = $this->aetools->exploreDir(self::SOURCE_FILES, self::FOLD_RESOURCES, "dossiers");
		$this->fold_translations = array();
		foreach ($fold_resources as $fR) {
			$res = $this->aetools->exploreDir($fR['sitepath'].$fR['nom'], self::FOLD_TRANSLATIONS, "dossiers", false); // false --> enfants directs
			if(count($res) > 0) foreach ($res as $folder) {
				$this->fold_translations[] = $folder;
			}
		}
		foreach($this->fold_translations as $folder) {
			$path = $folder['sitepath'].$folder['nom'];
			// constitution de la liste des bundles
			$bundle = $this->getBundle($path);
			$this->bundles_list[$bundle] = $path;
			// recherche des fichiers
			$listOfFiles = $this->getTranslateFiles($path);
			// liste des domaines
			$this->domains[$bundle] = array();
			foreach ($listOfFiles as $key => $file) {
				$domain = $this->fileGetDomain($file['nom']);
				if(!in_array($domain, $this->domains[$bundle])) $this->domains[$bundle][] = $domain;
				// ajout de données
				$listOfFiles[$key]['statut_message'] = 'traduction.file_found';
				$listOfFiles[$key]['statut'] = 1;
			}
			// initialisation des fichiers du domaine
			foreach ($this->domains[$bundle] as $domain) {
				foreach ($this->getLanguages() as $language) {
					$this->files_list[$bundle][$domain][$language]['statut_message'] = 'traduction.file_not_found';
					$this->files_list[$bundle][$domain][$language]['statut'] = 0;
					$this->files_list[$bundle][$domain][$language]['bundle'] = $bundle;
					$this->files_list[$bundle][$domain][$language]['domain'] = $domain;
					$this->files_list[$bundle][$domain][$language]['language'] = $language;
					$this->files_list[$bundle][$domain][$language]['nom'] = $domain.".".$language.".yml";
				}
			}
			// tri et 
			foreach ($listOfFiles as $file) {
				$domain = $this->fileGetDomain($file['nom']);
				$language = $this->fileGetLanguage($file['nom']);
				$this->files_list[$bundle][$domain][$language] = $file;
				// $this->files_list[$bundle][$domain][$language]['statut_message'] = 'traduction.file_found';
				// $this->files_list[$bundle][$domain][$language]['statut'] = 1;
				$this->files_list[$bundle][$domain][$language]['bundle'] = $bundle;
				$this->files_list[$bundle][$domain][$language]['domain'] = $domain;
				$this->files_list[$bundle][$domain][$language]['language'] = $language;
				$this->files_list[$bundle][$domain][$language]['nom'] = $domain.".".$language.".yml";
			}
			// check complet de files
		}
		// echo('<pre>');
		// var_dump($this->files_list);
		// echo('</pre>');
		return $this->files_list;
	}

	/**
	 * Renvoie le domaine du fichier
	 * @param string $filename
	 * @return string
	 */
	protected function fileGetDomain($filename) {
		return preg_replace("#\.(".$this->container->getParameter('default_locales').")\.yml$#", "", $filename);
	}

	/**
	 * Renvoie la langue du ficher
	 * @param string $filename
	 * @return string
	 */
	protected function fileGetLanguage($filename) {
		$exp = explode(".", $filename);
		return $exp[count($exp) - 2];
	}

	/**
	 * Renvoie la liste des fichiers de translation (yaml) contenus dans le dossier $path
	 * @param string $path
	 * @return array
	 */
	protected function getTranslateFiles($path) {
		return $this->aetools->exploreDir($path, "\.(".$this->container->getParameter('default_locales').")\.yml$", "fichiers", false, true);
	}

	/**
	 * Renvoie la liste des langues définie dans config.yml
	 * @return array
	 */	
	public function getLanguages() {
		if(!is_array($this->languages)) {
			$this->languages = explode("|", $this->container->getParameter('default_locales'));
		}
		return $this->languages;
	}

	/**
	 * Renvoie la liste des langues par BUNDLE définie dans config.yml
	 * @return array
	 */	
	public function getLanguagesByBundles() {
		if(!is_array($this->bundlesLanguages)) {
			$bundles = $this->container->getParameter('list_locales');
			foreach ($bundles as $bundle => $languages) {
				$this->bundlesLanguages[$bundle] = explode("|", $languages);
			}
		}
		return $this->bundlesLanguages;
	}

	/**
	 * Renvoie la locale par défaut (locale, dans config.yml)
	 * @return string
	 */
	public function getDefaultLocale() {
		$this->default_locale = $this->container->getParameter('locale');
		return $this->default_locale;
	}

	/**
	 * Renvoie la locale courante
	 * @return string
	 */
	public function getCurrentLocale() {
		$request = $this->container->get('request');
		return $request->getLocale();
	}

	/**
	 * Renvoie la liste des dossiers "translations"
	 * @return array
	 */
	public function getTranslationFolders() {
		return $this->fold_translations;
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
	 * Renvoie la liste de fichiers, classés par bundle
	 * @return array
	 */
	public function getFilesInBundles() {
		return $this->files_list;
	}

	/**
	 * Renvoie le fichier (info) selon bundle, domaine et langue
	 * @param string $bundle
	 * @param string $domain
	 * @param string $language
	 * @return array
	 */
	public function getFile($bundle, $domain, $language) {
		return isset($this->files_list[$bundle][$domain][$language]) ? $this->files_list[$bundle][$domain][$language] : false;
	}

	/**
	 * Renvoie le statut d'un fichier
	 * @param string $bundle
	 * @param string $domain
	 * @param string $language
	 * @return boolean
	 */
	public function getFileStatut($bundle, $domain, $language) {
		return $this->files_list[$bundle][$domain][$language]['statut'] == 1 ? true : false;
	}

	/**
	 * Renvoie le message de statut d'un fichier
	 * @param string $bundle
	 * @param string $domain
	 * @param string $language
	 * @return string
	 */
	public function getFileStatutMessage($bundle, $domain, $language) {
		return $this->files_list[$bundle][$domain][$language]['statut_message'];
	}

	/**
	 * Renvoie le path complet du fichier
	 * @param string $bundle
	 * @param string $domain
	 * @param string $language
	 * @return string
	 */
	public function getFilePath($bundle, $domain, $language) {
		$fullbundle = $this->getFullPathBundle($bundle);
		if(isset($this->files_list[$bundle][$domain][$language]['full'])) {
			$path = $this->files_list[$bundle][$domain][$language]['full'];
		} else if($fullbundle != false) {
			$path = $fullbundle."/".$domain.".".$language.".yml";
		} else return false;
		return $path;
	}

	/**
	 * Renvoie la liste des domaines
	 * @return array
	 */
	public function getDomains() {
		$liste = array();
		foreach ($this->domains as $bundle => $domains) {
			foreach ($domains as $domain) {
				$liste[] = $domain;
			}
		}
		return $liste;
	}

	/**
	 * Renvoie la liste des domaines, classés par bundle
	 * @return array
	 */
	public function getDomainsInBundles() {
		return $this->domains;
	}

	/**
	 * Renvoie la liste des domaines d'un bundles
	 * @param string $bundle
	 * @return array
	 */
	public function getDomainsByBundle($bundle) {
		return isset($this->domains[$bundle]) ? $this->domains[$bundle] : false;
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
		return strtolower(str_replace(array(self::FOLD_RESOURCES, self::FOLD_TRANSLATIONS, self::SOURCE_FILES, self::BUNDLE_EXTENSION, '/'), '', $path));
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
	 * Renvoie la structure type du domaine. 
	 * En récupérant toutes les variables existantes dans chaque fichier pour générer un type générique
	 * @param string $bundle
	 * @param string $domain
	 * @return array
	 */
	public function getSchemaType($bundle, $domain) {
		$file = array();
		$emptyfile = array();
		foreach ($this->getLanguages() as $lang) {
			if($this->getFileStatut($bundle, $domain, $lang) == true) {
				foreach ($this->getSingleArray($bundle, $domain, $lang) as $key => $value) {
					$emptyfile[$key] = null;
				}
			}
		}
		return $emptyfile;
	}

	/**
	 * ajoute les éléments du schéma type à un fichier
	 * @param string $bundle
	 * @param string $domain
	 * @param string $language
	 * @param boolean $delete = false
	 * @return boolean (nb d'octets si success)
	 */
	public function insertSchemaType($bundle, $domain, $language, $delete = false) {
		$schema = $this->getSchemaType($bundle, $domain);
		$file = $this->getSingleArray($bundle, $domain, $language);
		// if(!is_array($file)) $file = array();
		foreach ($schema as $key => $value) {
			if(!array_key_exists($key, $file) || $delete == true) $file[$key] = $value;
		}
		return $this->saveSingleArray($bundle, $domain, $language, $file);
	}

	/**
	 * ajoute et remplace les éléments par ceux du schéma type à un fichier
	 * @param string $bundle
	 * @param string $domain
	 * @param string $language
	 * @return boolean (nb d'octets si success)
	 */
	public function replaceSchemaType($bundle, $domain, $language) {
		return $this->insertSchemaType($bundle, $domain, $language, true);
	}

	/**
	 * Lecture d'un fichier d'après bundle, domaine et langue
	 * @param string $bundle
	 * @param string $domain
	 * @param string $language
	 * @return array
	 */
	public function parse_yaml($bundle, $domain, $language) {
		return $this->parse_yaml_fromFile($this->getFilePath($bundle, $domain, $language));
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
	 * Checke un fichier d'après bundle, domaine et langue
	 * @param string $bundle
	 * @param string $domain
	 * @param string $language
	 * @return mixed
	 */
	public function check_yaml($bundle, $domain, $language) {
		return $this->check_yaml_fromFile($this->getFilePath($bundle, $domain, $language));
	}

	/**
	 * Checke un fichier d'après path
	 * @param string $path
	 * @return mixed
	 */
	protected function check_yaml_fromFile($path) {
		$parse = $this->parse_yaml_fromFile($path);
		return (is_string($parse) || $parse == false) ? $parse : true;
	}

	/**
	 * Écriture d'un fichier d'après bundle, domaine et langue
	 * @param string $bundle
	 * @param string $domain
	 * @param string $language
	 * @return boolean (nb d'octets si success)
	 */
	public function dump_yaml($bundle, $domain, $language, $array) {
		return $this->dump_yaml_toFile($this->getFilePath($bundle, $domain, $language), $array);
	}

	/**
	 * Écriture un fichier d'après path
	 * @param string $path
	 * @return boolean (nb d'octets si success)
	 */
	protected function dump_yaml_toFile($path, $array) {
		$dumper = new Dumper();
		$r = file_put_contents(
			$path,
			$dumper->dump($array, self::MAX_YAML_LEVEL)
			);
		$this->initFiles();
		return $r;
	}


	/**
	 * Génère les fichiers pour un nouveau domaine
	 * @param string $bundle
	 * @param string $domain
	 * @param array $array - attention : array single (passer par $this->toSingleArray($array) si nécessaire)
	 * @return aeTranslate
	 */
	public function createNewDomainFromSingle($bundle, $domain, $array = null) {
		$domain = str_replace('.', '_', $domain);
		if($array == null || !is_array($array)) $array = array();
		return $this->createNewDomainFromRecursive(
			$bundle,
			$domain,
			$this->toRecursiveArray($array)
			);
	}

	/**
	 * Génère les fichiers pour un nouveau domaine
	 * @param string $bundle
	 * @param string $domain
	 * @param array $array - attention : array récursif (passer par $this->toRecursiveArray($array) si nécessaire)
	 * @return aeTranslate
	 */
	public function createNewDomainFromRecursive($bundle, $domain, $array = null) {
		$domain = str_replace('.', '_', $domain);
		if($array == null || !is_array($array)) $array = array();
		foreach ($this->getLanguages() as $language) {
			$this->dump_yaml(
				$bundle,
				$domain,
				$language,
				$array
			);
		}
		return $this;
	}







	/**
	 * Renvoie les données d'un fichier (bundle/domaine/langue) de translation sous forme d'array à 1 niveau
	 * @param string $file
	 * @return array
	 */
	public function getSingleArray($bundle, $domain, $language) {
		$r =  $this->toSingleArray(
			$this->parse_yaml($bundle, $domain, $language)
			);
		if(count($r) == 1 && reset($r) == false && key($r) == '') $r = array();
		return $r;
	}

	/**
	 * Renvoie les données d'un fichier (chemin complet) de translation sous forme d'array à 1 niveau
	 * @param string $path
	 * @return array
	 */
	public function getSingleArrayFromFile($path) {
		return $this->toSingleArray($this->parse_yaml_fromFile($path));
	}

	/**
	 * Enregistre les données d'un array à 1 niveau dans un bundle/domaine/langue
	 * @param string $bundle
	 * @param string $domain
	 * @param string $language
	 * @param array $array
	 * @return boolean
	 */
	public function saveSingleArray($bundle, $domain, $language, $array) {
		return $this->saveSingleArrayToFile(
			$this->getFilePath($bundle, $domain, $language),
			$this->toRecursiveArray($array)
			);
	}

	/**
	 * Enregistre les données d'un array à 1 niveau dans un fichier yaml
	 * @param string $path
	 * @param array $array
	 * @return boolean
	 */
	public function saveSingleArrayToFile($path, $array) {
		$array = $this->toRecursiveArray($array);
		return $this->dump_yaml_toFile($path, $array);
	}


	/**
	 * Change une valeur dans bundle/domaine/langue/champ
	 * @param string $bundle
	 * @param string $domain
	 * @param string $language
	 * @param string $champ
	 * @param string $newvalue
	 * @return boolean
	 */
	public function changeValue($bundle, $domain, $language, $champ, $newvalue) {
		return $this->changeValueToFile(
			$this->getFilePath($bundle, $domain, $language),
			$champ,
			$newvalue
			);
	}

	/**
	 * Change une valeur de $champ dans un fichier yaml
	 * @param string $filePath
	 * @param string $champ
	 * @param string $newvalue
	 * @return boolean
	 */
	public function changeValueToFile($filePath, $champ, $newvalue) {
		if(!is_string($newvalue)) return false;
		$loadFile = $this->getSingleArrayFromFile($filePath);
		if(array_key_exists($champ, $loadFile)) {
			$loadFile[$champ] = $newvalue;
		} else return false;
		return $this->saveSingleArrayToFile($filePath, $loadFile);
	}







	/**
	 * Transforme un array récursif en array simple
	 * L'index simple sera un seul texte, formé des index des différents niveaux, séparés par des points : 
	 * Ex. : $array['niv1']['niv2']['niv3'] = "texte" donne : $array['niv1.niv2.niv3'] = "texte"
	 * @param array $array
	 * @return array
	 */
	public function toSingleArray($array, $originKey = null) {
		if($originKey == null) {
			$this->singleArray = array();
		}
		if(is_array($array)) {
			if($originKey != null) $originKey .= self::ARRAY_GLUE;
			foreach ($array as $key => $value) $this->toSingleArray($value, $originKey.$key);
		} else {
			$this->singleArray[$originKey] = $array;
		}
		return $this->singleArray;
	}

	/**
	 * Transforme un array simple en array récursif. 
	 * L'index doit avoir un nom déparé par des points pour indiquer les différents niveaux : 
	 * Ex. : $array['niv1.niv2.niv3'] = "texte" donne : $array['niv1']['niv2']['niv3'] = "texte"
	 * @param array $array
	 * @return array
	 */
	public function toRecursiveArray($array) {
		$tempMaster = array();
		foreach ($array as $key => $value) {
			$exp = explode(self::ARRAY_GLUE, $key);
			$exp = array_reverse($exp);
			$temp = $value;
			foreach ($exp as $item) {
				$temp = array($item => $temp);
			}
			$tempMaster = array_merge_recursive($tempMaster, $temp);
		}
		return $tempMaster;
	}


	private function verifData() {
		$bundle = 'sitesiteBundle';
		$array = array(
			'foo' => 'bar',
			'bar' => array('foo' => 'bar', 'bar' => 'baz'),
		);
		// echo('<h3>Création d\'un nouveau domaine dans le bundle <strong>'.$bundle.'</strong></h3>');
		// $this->createNewDomainFromRecursive($bundle, 'domaine_test', $array);
		echo('<pre><h3>getLanguages</h3>');
		var_dump($this->getLanguages());
		echo('</pre>');
		echo('<pre><h3>getDefaultLocale</h3>');
		var_dump($this->getDefaultLocale());
		echo('</pre>');
		echo('<pre><h3>getTranslationFolders</h3>');
		var_dump($this->getTranslationFolders());
		echo('</pre>');
		// echo('<pre><h3>getFiles</h3>');
		// var_dump($this->getFiles());
		// echo('</pre>');
		// echo('<pre><h3>getFilesInBundles</h3>');
		// var_dump($this->getFilesInBundles());
		// echo('</pre>');
		echo('<pre><h3>getDomains</h3>');
		var_dump($this->getDomains());
		echo('</pre>');
		echo('<pre><h3>getBundles</h3>');
		var_dump($this->getBundles());
		echo('</pre>');
		echo('<pre><h3>getPathsBundles</h3>');
		var_dump($this->getPathsBundles());
		echo('</pre>');
	}

}

