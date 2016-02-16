<?php
namespace site\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
// yaml parser
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;
// aetools
use site\services\aetools;

/**
 * Service aeFixtures
 * - Gestion des fixtures
 */
class aeFixtures {

	const ARRAY_GLUE = '___';
	const SOURCE_FILES = 'src/';
	const FOLD_DATAFIXTURES = 'DataFixtures';
	const FOLD_ORM = 'ORM';
	const BUNDLE_EXTENSION = 'Bundle';
	const GO_TO_ROOT = '/../../../';
	const MAX_YAML_LEVEL = 10;

	protected $container; 			// container
	protected $aetools; 			// aetools
	protected $languages; 			// array des langues disponibles --> config.yml --> default_locales: "fr|en|en_US|es|it|de"
	protected $bundlesLanguages;	// array des langues disponibles par bundles --> config.yml --> list_locales: "fr|en", etc.
	protected $default_locale; 		// string locale par défaut --> config.yml --> locale: "en"
	protected $fold_ORM;			// liste des dossiers contenant les fichiers de traduction
	protected $bundles_list;		// array des bundles/path : $array(bundle => path)
	protected $files_list;			// array des fichiers, classés par bundles
	protected $classnames;			// array des classnames
	protected $rootPath;			// Dossier root du site

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
		$this->aetools = new aetools();
		$this->rootPath = __DIR__.self::GO_TO_ROOT;
		$this->aetools->setRootPath("/");
		// récupération de fichiers et check
		$this->initFiles();
		// $this->verifData();
	}

	protected function initFiles() {
		// initialisation
		$this->bundles_list = array();
		$this->files_list = array();
		// récupération des dossiers "translations", enfants DIRECTS des dossiers "Resources", uniquement dans "src"
		$fold_datafixtures = $this->aetools->exploreDir(self::SOURCE_FILES, self::FOLD_DATAFIXTURES, "dossiers");
		$this->fold_ORM = array();
		foreach ($fold_datafixtures as $fR) {
			$res = $this->aetools->exploreDir($fR['sitepath'].$fR['nom'], self::FOLD_ORM, "dossiers", false); // false --> enfants directs
			if(count($res) > 0) foreach ($res as $folder) {
				$this->fold_ORM[] = $folder;
			}
		}
		foreach($this->fold_ORM as $folder) {
			$path = $folder['sitepath'].$folder['nom'];
			// constitution de la liste des bundles
			$bundle = $this->getBundle($path);
			$this->bundles_list[$bundle] = $path;
			// recherche des fichiers
			$listOfFiles = $this->getFixturesFiles($path);
			// liste des domaines
			$this->fixturesFiles[$bundle] = array();
			foreach ($listOfFiles as $key => $file) {
				$entityName = $this->fileGetEntity($file['nom']);
				if(!in_array($entityName, $this->fixturesFiles[$bundle]))
					$this->fixturesFiles[$bundle][] = $entityName;
				// ajout de données
				$listOfFiles[$key]['statut_message'] = 'fixtures.file_found';
				$listOfFiles[$key]['statut'] = 1;
			}
			// initialisation des fichiers du domaine
			foreach ($this->fixturesFiles[$bundle] as $entityName) {
				// $this->files_list[$entityName][$bundle]['path'] = $path;
				$this->files_list[$entityName][$bundle]['fullpath'] = $this->rootPath.$path."/".$entityName."s.yml";
				// $this->files_list[$entityName][$bundle]['entityName'] = $entityName;
				// $this->files_list[$entityName][$bundle]['nom'] = $entityName."s.yml";
				// $this->files_list[$entityName][$bundle]['data'] = $this->parse_yaml_fromFile($this->rootPath.$path."/".$entityName."s.yml");
			}
		}
		// echo('<pre>');
		// var_dump($this->files_list);
		// die('</pre>');
		return $this->files_list;
	}

	/**
	 * Renvoie le domaine du fichier
	 * @param string $filename
	 * @return string
	 */
	protected function fileGetEntity($filename) {
		return preg_replace("#s\.yml$#", "", $filename);
	}

	/**
	 * Liste des bundles
	 * @return array
	 */
	public function getBundles() {
		return array_keys($this->bundles_list);
	}

	/**
	 * Renvoie le nom du bundle d'après le path
	 * @param string $path
	 * @return array
	 */
	public function getBundle($path) {
		return strtolower(str_replace(array(self::FOLD_DATAFIXTURES, self::FOLD_ORM, self::SOURCE_FILES, self::BUNDLE_EXTENSION, '/'), '', $path));
	}

	/**
	 * Renvoie la liste des fichiers de translation (yaml) contenus dans le dossier $path
	 * @param string $path
	 * @return array
	 */
	protected function getFixturesFiles($path) {
		return $this->aetools->exploreDir($path, "s\.yml$", "fichiers", false, true);
	}


	public function getInfoFiles() {
		$data = array();
		foreach ($this->files_list as $entity => $bundle) {
			$dataE = array();
			$ord = array();
			foreach ($bundle as $bundleName => $file) {
				$temp = $this->parse_yaml_fromFile($file['fullpath']);
				$dataE = array_merge($dataE, $temp[$entity]['data']);
				$ord[] = $temp[$entity]['order'];
			}
			$ord = min($ord);
			$data[$ord]['ord'] = $ord;
			$data[$ord]['nb'] = count($dataE);
			$data[$ord]['name'] = $entity;
		}
		ksort($data);
		$newdata = array();
		foreach ($data as $key => $ent) {
			$newdata[$ent['name']] = $ent;
		}
		unset($data);
		// echo('<pre>');
		// var_dump($newdata);
		// die('</pre>');
		return $newdata;
	}

	/**
	 * 
	 * 
	 * 
	 */
	public function fillDataWithFixtures($entity) {
		if(array_key_exists($entity, $this->files_list)) {
			if(count($this->files_list[$entity] > 0)) {
				$data = array();
				foreach ($this->files_list[$entity] as $bundle => $file) {
					$data = array_merge($data, $this->parse_yaml_fromFile($file['fullpath'])[$entity]['data']);
				}
				return $this->fillEntity($data, $entity);
			}
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
	 * Vide l'entité $entite
	 * @param string $entite
	 * @return integer
	 */
	protected function emptyEntity($entite) {
		$em = $this->container->get('doctrine')->getManager();
		$number = 0;
		switch ($entite) {
			case 'User':
				$number = $this->container->get('service.users')->deleteAllUsers();
				break;
			case 'fileFormat':
				$number = $this->container->get('aetools.media')->eraseAllFormats();
				break;
			default:
				$entities = $em->getRepository($this->getClassname($entite))->findAll();
				foreach ($entities as $ent) {
					$em->remove($ent);
					$number++;
				}
				$em->flush();
				break;
		}
		// remise à zéro de l'index de la table
		$em->getConnection()->executeUpdate("ALTER TABLE ".$entite." AUTO_INCREMENT = 1;");
		return $number;
	}

	/**
	 * Hydrate l'entité $entite avec $data
	 * @param array $data
	 * @param string $entite
	 * @return array
	 */
	protected function fillEntity($data, $entite, $empty = true) {
		$classname = $this->getClassname($entite);
		if($empty === true) $this->emptyEntity($entite);
		$em = $this->container->get('doctrine')->getManager();
		$defaultStatut = $em->getRepository('site\adminBundle\Entity\statut')->defaultVal();
		$defaultTaux = $em->getRepository('site\adminBundle\Entity\tauxTva')->defaultVal();
		$defaultStatut = reset($defaultStatut);
		$defaultTaux = reset($defaultTaux);
		$newdata = array();
		$attrMethods = array('set', 'add');
		foreach ($data as $key => $dat) {
			$newdata[$key] = new $classname();
			if(method_exists($newdata[$key], 'setStatut')) {
				// ajout statut
				$newdata[$key]->setStatut($defaultStatut);
			}
			if(method_exists($newdata[$key], 'setTauxTva')) {
				// ajout TVA
				$newdata[$key]->setTauxTva($defaultTaux);
			}
			// $newdata[$key] = $this->container->get('aetools.aeEntities')->newObject($classname, true, false);
			foreach ($dat as $attribute => $value) {
				foreach ($attrMethods as $method) {
					$m = $method.ucfirst($attribute);
					if(method_exists($newdata[$key], $m)) $newdata[$key]->$m($value);
				}
			}
			$em->persist($newdata[$key]);
		}
		if(count($newdata) > 0) $em->flush();
		return $newdata;
	}

	protected function getEntities() {
		$this->classnames = array_flip($this->container->get('aetools.aeEntities')->getListOfEnties());
		return $this->classnames;
	}

	protected function getClassname($entite) {
		$this->getEntities();
		return isset($this->classnames[$entite]) ? $this->classnames[$entite] : false;
	}

}

