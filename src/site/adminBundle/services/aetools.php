<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use Doctrine\DBAL\Types\Type;

use site\adminBundle\services\aeReponse;

use \Exception;
use \DateTime;
use \ReflectionObject;
use \ReflectionClass;
use \ReflectionMethod;


/**
 * Service aeTools
 * - Gestion des fichiers/dossiers
 * - Gestion services Symfony : router / tamplating / etc.
 */
class aetools {

	const NAME					= 'aetools'; 		// nom du service
	const CALL_NAME				= 'aetools.aetools'; // comment appeler le service depuis le controller/container

	const SERVEUR_TYPE			= 'UNIX/LINUX';		// Type de serveur
	const SLASH					= '/';				// slash
	const ASLASH 				= '\\';				// anti-slashes
	const WIN_ASLASH			= '/';				// anti-slashes Windows
	const ALL_FILES 			= "^.+$";			// motif PCRE pour tous textes
	const EOLine				= "\n";				// End of line Terminal
	const TAB1					= "   - ";
	const TAB2					= "      - ";
	const ARRAY_GLUE 			= '___';
	// Paths
	const GO_TO_ROOT 			= '/../../../../';
	const SOURCE_FILES 			= 'src/';
	const WEB_PATH				= 'web/';
	const BUNDLE_EXTENSION 		= 'Bundle';
	const PARAMS_FOLDER			= 'params';
	// Dossiers
	const DEFAULT_CHMOD			= 0755;
	// DateTime
	const FORMAT_DATETIME_SQL	= "Y-m-d H:i:s";
	const DATE_ZERO				= "0000-00-00";
	const TIME_ZERO				= "0:0:0";
	// YAML
	const MAX_YAML_LEVEL 		= 100;

	const PROXIES_NAME			= "Proxies";


	/**
	 * boolean : controller dénini ?
	 */
	protected $ctrlDefined 		= null;
	/**
	 * container autres services
	 */
	protected $container;
	/**
	 * router
	 */
	protected $router;
	/**
	 * asset
	 */
	protected $asset;

	protected $controller;
	protected $requAttributes;
	protected $serviceRequ;
	protected $serviceSess;
	protected $sessionData;
	protected $flashBag;
	protected $securityContext;
	protected $route;
	/**
	 * outils de texte
	 */
	protected $texttools;

	/**
	 * chemin complet du controller
	 */
	protected $controllerPath;
	/**
	 * dossier du controller
	 */
	protected $ctrlFolder;
	/**
	 * nom du controller
	 */
	protected $controllerName;
	/**
	 * nom de la méthode appelée
	 */
	protected $actionName;
	/**
	 * nom de la méthode appelée, sans "Action"
	 */
	protected $singleActionName;

	/**
	 * memo pour savePath pour ce service
	 */
	protected $memo = '__self';
	/**
	 * contenu des mémo pour savePath
	 */
	protected $pathMemo = array();

	/**
	 * service tranlator
	 */
	protected $trans = null;

	/**
	 * paramètres du bundle / aetools
	 */
	protected $labo_parameters = array();

	protected $user;

	protected $currentPath;
	protected $aslash;
	protected $rootPath;
	protected $recursiveTree;
	protected $allRoutes = array();
	protected $nofiles = '^\.';
	protected $liste;
	protected $service = array();
	/**
	 * nom du groupe
	 */
	protected $groupeName;
	/**
	 * nom du bundle
	 */
	protected $bundleName;
	protected $gotoroot;
	protected $console;

	protected $listP = array("groupeName", "bundleName", "ctrlFolder", "controllerName");
	protected $nP;

	public function __construct(ContainerInterface $container = null) {
		$this->console 			= array();
		$this->container 		= $container;
		// initialisation de données nécessaires au service
		$this->initAllData();
		$this->createFolderInWeb(self::PARAMS_FOLDER);
		return $this;
	}

	public function __destruct() {
		$this->close();
	}

	/**
	 * initialise les données de service.
	 * ATTENTION : nécessite la présence du controller !
	 * @return string
	 */
	protected function initAllData() {
		$this->gotoroot 			= __DIR__.self::GO_TO_ROOT;
		if($this->container !== null) {
			$this->router 			= $this->container->get('router');
			$this->asset 			= $this->container->get('templating.helper.assets');
			$this->texttools		= $this->container->get('aetools.textutilities');
			// $this->datetools		= $this->container->get('labobundle.aedates');
		}
		if($this->isControllerPresent()) {
			$this->serviceRequ 			= $this->container->get('request');
			$this->requAttributes		= $this->serviceRequ->attributes;
			$this->serviceSess 			= $this->serviceRequ->getSession();
			$this->controller			= $this->requAttributes->get('_controller');
			$this->route 				= $this->requAttributes->get('_route');
			$this->sessionData			= $this->container->get("session");
			$this->flashBag 			= $this->sessionData->getFlashBag();
			$this->securityContext 		= $this->container->get('security.context');
			$this->trans 				= $this->container->get('translator');
			// $this->setWebPath();
		}
		// slashes
		switch(strtoupper(self::SERVEUR_TYPE)) {
			case "UNIX/LINUX": $this->aslash = self::ASLASH; break;
			case "WINDOWS": $this->aslash = self::WIN_ASLASH; break;
			default: $this->aslash = self::ASLASH; break;
		}
		// $this->aslash = DIRECTORY_SEPARATOR;
		// nom du mémo
		$this->memo = $this->getName().$this->memo;
	}


	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// IDENTIFICATION CLASSE
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function __toString() {
		try {
			$string = $this->getNom();
		} catch (Exception $e) {
			$string = '…';
		}
		return $string;
	}

	public function getNom() {
		return self::NAME;
	}

	public function callName() {
		return self::CALL_NAME;
	}

	/**
	 * Renvoie le nom de la classe
	 * @return string
	 */
	public function getName() {
		return get_called_class();
	}

	/**
	 * Renvoie le nom de la classe
	 * @return string
	 */
	public function getShortName() {
		return $this->getClassShortName($this->getName());
	}

    // abstract public function getClassName();
    public function getClassName() {
        return $this->getClass(true);
    }

	/**
	 * Renvoie la liste (array) des classes des parents de l'entité
	 * @param boolean $short = false
	 * @return array
	 */
	public function getParentClassName($short = false) {
		$class = new ReflectionClass($this->getClass());
		$class = $class->getParentClass();
		if($class)
			return (boolean) $short ?
				$class->getShortName():
				$class->getName();
			else return null;
	}

	/**
	 * Renvoie la liste (array) des classes des parents de l'entité
	 * @param boolean $short = false
	 * @return array
	 */
	public function getParentsClassNames($short = false) {
		$class = new ReflectionClass($this->getClass());
		$parents = array();
		while($class = $class->getParentClass()) {
			(boolean) $short ?
				$parents[] = $class->getShortName():
				$parents[] = $class->getName();
		}
		return $parents;
	}

	/**
	 * Renvoie la liste (array) des classes des parents de l'entité
	 * @param boolean $short = false
	 * @return array
	 */
	public function getParentsShortNames() {
		return $this->getParentsClassNames(true);
	}

	/**
	 * Renvoie le nom de la classe (short name par défaut)
	 * @param boolean $short = false
	 * @return string
	 */
	public function getClass($short = false) {
		$class = new ReflectionClass(get_called_class());
		return (boolean) $short ?
			$class->getShortName():
			$class->getName();
	}

	/**
	 * Renvoie le nom court de la classe
	 * @return string
	 */
	public function getClassShortName($class) {
		if(is_object($class)) $class = get_class($class);
		if(is_string($class)) {
			$shortName = explode(self::ASLASH, $class);
			return end($shortName);
		}
		return false;
	}

	public function getLevel() {
		return count($this->getParentsClassNames());
	}

	public function isLongName($name) {
		return preg_match('#^.+(Entity|Model).+$#', $name);
	}

	public function isProxyClass($name) {
		return preg_match('#^'.self::PROXIES_NAME.'#', $name);
	}



	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// CONTROLLER / CONTAINER
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Renvoie true si le controller est présent
	 * @return boolean
	 */
	public function isControllerPresent() {
		if($this->container !== null) {
			$this->controllerPath = $this->container->get('request')->attributes->get('_controller');
		} else $this->controllerPath === null;
		if($this->controllerPath === null) {
			// pas de controller
			$this->ctrlDefined = false;
		} else {
			// controller présent
			$this->ctrlDefined = true;
			$d = explode("::", $this->controllerPath."");
			if(count($d) < 2)
				$d = explode(":", $this->controllerPath."");
			$this->actionName = $d[1];
			$this->singleActionName = preg_replace("#Action$#", "", $d[1]);
			$e = explode(self::ASLASH, $d[0]);
			if(count($e) < 2) $e = explode(".", $d[0]);
			foreach($e as $idx => $nom) {
				if($idx < (count($this->listP) + 1)) {
					if(isset($this->listP[$idx])) $nP = $this->listP[$idx];
					$this->$nP = $nom;
				}
			}
			$exp = explode('Controller', $this->controllerPath);
			$this->bundleName = str_replace('\\', '', reset($exp));
		}
		return $this->ctrlDefined;
	}

	/**
	 * Renvoie true si le controller est absent
	 * @return boolean
	 */
	public function isControllerAbsent() {
		return !$this->isControllerPresent();
	}

	public function isContainerPresent() {
		return $this->container !== null ? true : false;
	}

	public function isContainerAbsent() {
		return !$this->isContainerPresent();
	}



	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// PATHS
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Ajoute un slash en fin de path s'il n'existe pas
	 * @param string $path
	 * @return string
	 */
	protected function addEndSlash($path) {
		// ajoute un slash en fin si non existant
		if(substr($path, -1, 1) != self::SLASH) $path .= self::SLASH;
		// supprime les slashes doubles…
		$path = preg_replace("#".self::SLASH."(".self::SLASH.")+"."#", self::SLASH, $path);
		// echo($path."<br>");
		return $path;
	}

	/**
	 * Renvoie le chemin vers la racine du site
	 * @return string
	 */
	public function getRootServer() {
		return $this->gotoroot;
	}

	/**
	 * Définit un nouveau path à partir du dossier WEB
	 * @param string $path
	 * @return string
	 */
	public function setWebPath($path = "") {
		$rootPath = $this->addEndSlash($this->gotoroot.self::WEB_PATH.$path);
		if(file_exists($rootPath)) {
			$this->close();
			$this->rootPath = $rootPath;
			$this->currentPath = $rootPath;
			$this->recursiveTree = array(dir($this->currentPath));
			$this->rewind();
			return $this;
		} else return false;
	}

	/**
	 * Définit un nouveau path à partir du dossier ROOT
	 * @param string $path
	 * @return string
	 */
	public function setRootPath($path = "") {
		$rootPath = $this->addEndSlash($this->gotoroot.$path);
		if(file_exists($rootPath)) {
			$this->close();
			$this->rootPath = $rootPath;
			$this->currentPath = $rootPath;
			$this->recursiveTree = array(dir($this->currentPath));
			$this->rewind();
			return $this;
		} else return false;
	}

	public function createFolderInWeb($name) {
		$path = $this->currentPath;
		$this->setWebPath();
		$this->verifDossierAndCreate($name);
		$this->setRootPath($this->currentPath);
	}

	/**
	 * Renvoie le contenu à partir d'un path (ou path courant)
	 * !!! insensible à la casse par défaut
	 * renvoie un tableau : 
	 * 		["path"]	= chemin
	 * 		["nom"]		= nom du fichier
	 * 		["full"]	= chemin + nom
	 *		["type"]	= fichier / dossier
	 * @param string/null - path à analyser (currentPath par défaut) (si "/" au début : "/web/" ou "/", on reprend à la racine du site)
	 * @param string $motif - motif preg pour recherche de nom
	 * @param string $genre - "fichiers" ou "dossiers" ou null (null = tous)
	 * @param boolean $recursive - true par défaut / recherche récursive (true = recherche dans les sous-dossiers également)
	 * @param boolean $casseSensitive - true par défaut
	 * @return array
	 */
	public function exploreDir($path = null, $motif = null, $genre = null, $recursive = true, $casseSensitive = true) {
		$path = $this->addEndSlash($path);
		$this->savePath();
		$this->setRootPath($path);
		$this->liste = array();
		while (false !== ($entry = $this->exploreDirectory($path, $motif, $genre, $recursive, $casseSensitive))) {
			$this->liste[] = $entry;
		}
		$this->close();
		$this->restoreSavedPath();
		return $this->liste;
	}

	protected function exploreDirectory($path = null, $motif = null, $genre = null, $recursive = true, $casseSensitive = true) {
		$path = $this->addEndSlash($path);
		$path2 = array();
		// motif
		if($motif === null) $motif = ".+";
		// genre fichier/dossier
		$genre === "dossiers" ? $fichier = false : $fichier = true ;
		$genre === "fichiers" ? $dossier = false : $dossier = true ;
		// casseSensitive
		$casseSensitive === false ? $sens = "i" : $sens = "" ;
		// parcours…
		while(count($this->recursiveTree) > 0) {
			$d = end($this->recursiveTree);
			if(false !== ($entry = $d->read())) {
				if(!preg_match("#".$this->nofiles."#", $entry)) {
					if((is_file($d->path.$entry)) && (preg_match("#".$motif."#".$sens, $entry)) && ($fichier === true)) {
						// fichier
						$path2["path"] = $d->path;
						$path2["nom"]  = $entry;
						$path2["full"] = $d->path.$entry;
						$path2["sitepath"] = str_replace($this->getRootServer(), "", $path2["path"]);
						$path2["type"] = "fichier";
						return $path2;
					}
					if(is_dir($this->addEndSlash($d->path.$entry))) {
						if((preg_match("#".$motif."#".$sens, $entry)) && ($dossier === true)) {
							// dossier
							$path2["path"] = $d->path;
							$path2["nom"]  = $entry;
							$path2["full"] = $d->path.$entry;
							$path2["sitepath"] = str_replace($this->getRootServer(), "", $path2["path"]);
							$path2["type"] = "dossier";
						}
						// sous-dossiers
						if($recursive === true) {
							if(false !== ($child = dir($d->path.$entry.self::SLASH))) {
								// $this->currentPath = $d->path.$entry.$this->aslash;
								$this->recursiveTree[] = $child;
							}
						}
						if(count($path2) > 0) {
							return $path2;
						}
					}
				}
			} else {
				// supprime le dernier élément de recusriveTree en le fermant (close)
				array_pop($this->recursiveTree)->close();
			}
		}
		return false;
	}
	
	/**
	 * read - OBSOLETE
	 * Recherche un fichier $type dans le dossier courant ou ses enfants
	 * !!! insensible à la casse par défaut
	 * renvoie un tableau : 
	 * 		["path"]	= chemin
	 * 		["nom"]		= nom du fichier
	 * 		["full"]	= chemin + nom
	 * @param string $type (expression régulière)
	 * @return array
	 */
	public function read($type = null, $casseSensitive = false) {
		if($casseSensitive === false) $sens = "i"; else $sens = "";
		while(count($this->recursiveTree)>0) {
			$d = end($this->recursiveTree);
			if((false !== ($entry = $d->read()))) {
				if(!preg_match("#".$this->nofiles."#", $entry)) {
					$path["path"] = $d->path;
					$path["nom"]  = $entry;
					$path["full"] = $d->path.$entry;
					
					if(is_file($d->path.$entry)) {
						if($type !== null) $r=preg_match('#'.$type.'#'.$sens, $entry); else $r = true;
						if($r == true || $r == 1) return $path;
					}
					else if(is_dir($d->path.$entry.$this->aslash)) {
						// $this->currentPath = $d->path.$entry.$this->aslash;
						if($child = @dir($d->path.$entry.$this->aslash)) {
							$this->recursiveTree[] = $child;
						}
					}
				}
			} else {
				array_pop($this->recursiveTree)->close();
			}
		}
		return false;
	}

	/**
	 * readAll - OBSOLETE
	 * renvoie la liste de tous les fichiers contenus dans le dossier et ses enfants
	 * !!! insensible à la casse par défaut
	 * renvoie un tableau : 
	 * 		["path"]	= chemin
	 * 		["nom"]		= nom du fichier
	 * 		["full"]	= chemin + nom
	 * @return array
	 */
	public function readAll($type = null, $path = null, $casseSensitive = true) {
		// if(null !== $path) $this->setWebPath($path);
		// 	else $this->setWebPath($this->rootPath); // réinitialise
		if(null !== $path) $this->setRootPath($path);
			// else $this->setRootPath(); // réinitialise
		$this->liste = array();
		// echo "<span style='color:white;'> Path : ".$this->getRootPath()."</span><br /><br />";
		while (false !== ($entry = $this->read($type, $casseSensitive))) {
			// echo $entry["path"]."<span style='color:pink;'>".$entry["nom"]."</span><br />";
			$this->liste[] = $entry;
		}
		$this->close();
		return $this->liste;
	}

	protected function rewind() {
		$this->closeChildren();
		$this->rewindCurrent();
	}

	protected function rewindCurrent() {
		return end($this->recursiveTree)->rewind();
	}

	protected function close() {
		if(is_array($this->recursiveTree)) while(true === ($d = array_pop($this->recursiveTree))) {
			$d->close();
		}
	}

	protected function closeChildren() {
		while(count($this->recursiveTree) > 1 && false !== ($d = array_pop($this->recursiveTree))) {
			$d->close();
			return true;
		}
		return false;
	}

	/**
	 * getRootPath
	 * Renvoie le dossier racine
	 * @return string
	 */
	public function getRootPath() {
		return isset($this->rootPath) ? $this->rootPath : false ;
	}

	/**
	 * getCurrentPath
	 * Renvoie le dossier courant
	 * @return string
	 */
	public function getCurrentPath() {
		return isset($this->currentPath) ? $this->currentPath : false ;
	}

	/**
	 * Retrouve les fichiers $file dans le dossier courant et tous les dossiers enfants
	 * @param array $files (peut être des expressions régulières => voir la méthode "read()")
	 * @return array
	 */
	public function findFilesEverywhere($files) {
		$r = array();
		if(is_string($files)) $files = array($files);
		foreach($files as $file) {
			$search = $this->readAll($file, null, true);
			if(count($search) > 0) foreach($search as $found) {
				$r[] = $found;
			}
		}
		return $r;
	}

	/**
	 * Retrouve et efface les fichiers $file dans le dossier courant et tous les dossiers enfants
	 * @param array $files (peut être des expressions régulières => voir la méthode "read()")
	 * @return array
	 */
	public function deleteFilesEverywhere($files) {
		$r = array();
		if(is_string($files)) $files = array($files);
		foreach($files as $file) {
			$search = $this->readAll($file, null, false);
			if(count($search) > 0) foreach($search as $erase) {
				$t = $this->deleteFile($erase["full"]);
				if($t === true) $r['succes'][] = $t;
					else $r['echec'][] = $t;
			}
		}
		return $r;
	}

	/**
	 * Efface le fichier $fileName s’il est dans le dossier courant (ou préciser le chemin !)
	 * @param $fileName
	 * @return boolean
	 */
	public function deleteFile($fileName) {
		$r = false;
		if(is_string($fileName)) {
			if(file_exists($fileName)) {
				if(@unlink($fileName)) $r = true;
			}
		}
		return $r;
	}

	/**
	 * Efface tous les fichiers contenus dans le tableau $files, depuis le dossier courant (ou préciser les chemins !)
	 * @param array $files
	 * @return array
	 */
	public function deleteFiles($files) {
		$r = array();
		if(is_string($files)) { $f = $files; $files = array(); $files[0] = $f; }
		$err = 0;
		foreach($files as $file) {
			$res = $this->deleteFile($file);
			if($res === false) $err++; else $r[] = $res;
		}
		if($err > 0) $r = false;
		return $r;
	}

	/**
	 * Efface le dossier $dir (préciser le chemin !)
	 * @param array $files
	 * @param boolean $deleteIn - efface les fichiers contenus avant
	 * @return array
	 */
	public function deleteDir($dir, $deleteIn = false) {
		$r = false;
		if((file_exists($dir)) && (is_dir($dir))) {
			if($deleteIn === true) {
				// efface les fichiers contenus // Ne marche pas POUR L'INSTANT !!!
				$this->findAndDeleteFiles(self::ALL_FILES, $dir);
			}
			if(@rmdir($dir)) $r = true;
				else $r = false;
		} else $r = false;
		return $r;
	}

	/**
	 * Recherche et efface tous les fichiers contenus dans $files
	 * (préciser le chemin de départ ou utilise la valeur de $rootPath)
	 * @param array/string $files
	 * @param string $path - depuis root site
	 */
	public function findAndDeleteFiles($files, $path = null) {
		$r = array();
		if(null !== $path) $this->setRootPath($path);
			// else $this->setWebPath($this->rootPath); // réinitialise
		if(is_string($files)) $files = array($files);
		$err = 0;
		foreach($files as $file) {
			// $this->readAll("^".$file."$");
			$this->readAll($file); // --> dans $this->liste
			if(count($this->liste) > 0) foreach($this->liste as $fichier) {
				$res = $this->deleteFile($fichier['full']);
				if($res === false) $err++; else $r[] = $res;
			}
		}
		if($err > 0) $r = false;
		return $r;
	}

	///// Créations/suppressions de dossiers

	/**
	 * Crée un dossier s'il n'existe pas. 
	 * Crée tout les dossiers intermédiaires si besoin. 
	 * ex. : pour "web/images/thumbnails/mini/" => créera "thumbnails", puis "mini" s'ils n'existent pas
	 * @param string $dossier
	 * @param integer $chmod (en mode octal)
	 * @return boolean / string
	 */
	public function verifDossierAndCreate($dossier, $chmod = null) {
		$result = true;
		$dossiers = preg_split('#['.self::SLASH.']+#', $dossier, -1, PREG_SPLIT_NO_EMPTY);
		if($chmod === null || !preg_match('#^[0-7]{4}$#', $chmod."")) $chmod = self::DEFAULT_CHMOD;
		// création des dossiers
		$cumul = "";
		foreach ($dossiers as $dossier) {
			$doss = $this->getCurrentPath().$cumul.$dossier;
			if(!file_exists($doss)) {
				if(!is_dir($doss)) {
					if(!mkdir($doss, $chmod, true)) {
						return false;
					}
				}
			}
			$cumul .= $dossier.self::SLASH;
		}
		return $result;
	}

	/**
	 * avance de $path depuis le path courant
	 * @param string $path
	 * @return boolean
	 */
	public function gotoFromCurrentPath($path = null) {
		$rootPath = $this->getCurrentPath().$path;
		if(file_exists($rootPath)) {
			$this->close();
			$this->rootPath = $rootPath;
			$this->currentPath = $rootPath;
			$this->recursiveTree = array(dir($this->currentPath));
			$this->rewind();
			return $this;
		} else return false;
	}

	/**
	 * Vérifie si un dossier existe (le crée si nécessaire) et s'y place en tant que dossier courant
	 * @param string $type - type de rapport
	 * @return string - chemin courant
	 */
	public function verifAndGotoFromCurrentPath($type = null) {
		$this->rootpath = $this->fmparameters['dossiers']['pathrapports'];
		// vérifie la présence du dossier pathrapports et pointe dessus
		$this->setWebPath();
		$this->verifDossierAndCreate($this->rootpath);
		$this->setWebPath($this->rootpath);
		if(is_string($type)) {
			$path = $this->rootpath.$type.self::SLASH;
			$this->verifDossierAndCreate($type);
			$this->setWebPath($path);
			// echo('Current path : '.$this->getCurrentPath().'<br>');
			return $path;
		}
		return $this->rootpath;
	}


	///// Mémorisations de chemins courants (paths)

	/**
	 * sauvegarde le chemin courant avec un nom
	 * @param $nom
	 * @return aetools
	 */
	public function savePath($nom = null) {
		if(!is_string($nom)) $nom = $this->memo;
		$this->pathMemo[$nom] = $this->getCurrentPath();
		return $this;
	}

	/**
	 * Récupère la liste des paths sauvagardés
	 * @return array
	 */
	public function getSavedPaths() {
		return $this->pathMemo;
	}

	/**
	 * Récupère la liste des noms des paths sauvagardés
	 * @return array
	 */
	public function getSavedPathNames() {
		return array_keys($this->pathMemo);
	}

	/**
	 * Supprime les paths sauvegardés (tous, ou celui nommé / ceux nommés)
	 * si $nom = true, supprime tous les paths
	 * @param mixed $nom
	 * @return aetools
	 */
	public function reinitSavePath($nom = null) {
		if(!is_string($nom) && $nom !== true) $nom = $this->memo;
		if($nom !== true) {
			if(is_string($nom)) $nom = array($nom);
			foreach($nom as $n) if(isset($this->pathMemo[$n])) {
				$this->pathMemo[$n] = null;
				unset($this->pathMemo[$n]);
			}
		} else {
			$this->pathMemo = array();
		}
		return $this;
	}

	/**
	 * Revient au chemin sauvegardé, avec un nom
	 * @param $nom
	 * @return string
	 */
	public function restoreSavedPath($nom = null) {
		if(!is_string($nom)) $nom = $this->memo;
		if(isset($this->pathMemo[$nom])) {
			$rootPath = $this->pathMemo[$nom];
			if(file_exists($rootPath)) {
				$this->close();
				$this->rootPath = $rootPath;
				$this->currentPath = $rootPath;
				$this->recursiveTree = array(dir($this->rootPath));
				$this->rewind();
				return $this->rootPath;
			}
		}
		return false;
	}

	///// Xxxxxxxxxx

	///// Xxxxxxxxxx

	///// Xxxxxxxxxx

	///// Xxxxxxxxxx

	///// Xxxxxxxxxx

	///// Xxxxxxxxxx


	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// STRUCTURE DES DOSSIERS
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Renvoie la liste des dossiers de src (donc la liste des groupes)
	 * @param boolean $path = true
	 * @return array
	 */
	public function getSrcGroupes($path = true) {
		// return $this->getDirs("/src/");
		$groupesPaths = $this->exploreDir("src/", null, "dossiers", false);
		if($path) return $groupesPaths;
		$groupesNames = array();
		foreach($groupesPaths as $name) $groupesNames[] = $name['nom'];
		return $groupesNames;
	}

	public function getDirs($path = null) {
		$this->savePath();
		if($path !== null) $this->setRootPath($path);
		$list = array();
		// lecture du contenu du dossier
		while($file = @readdir()) {
			if(is_dir($file) && !preg_match("#".$this->nofiles."#", $file)) $list[] = $file;
		}
		$this->restoreSavedPath();
		return $list;
	}


	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ARRAY FUNCTIONS
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function array_unique_in_array2_recursive($array1, $array2) {
		$result = array();
		foreach ($array2 as $key => $value) {
			if(is_array($array2[$key]) && isset($array1[$key])) {
				if(is_array($array1[$key])) $result[$key] = $this->array_unique_in_array2_recursive($array1[$key], $array2[$key]);
			} else if(is_array($array1)) {
				if(!in_array($array2[$key], $array1)) $result[$key] = $array2[$key];
			}
		}
		return $result;
	}


	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// SERVICE EVENTS
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	// /**
	//  * Initialise le service - attention : cette méthode est appelée en requête principale par EventListener !!!
	//  * @param FilterControllerEvent $event
	//  * @param boolean $reLoad
	//  */
	// public function serviceEventInit(FilterControllerEvent $event, $reLoad = false) {
	// 	$this->service = array();
	// 	// paramètres URL et route
	// 	$this->service['actuelpath'] 		= $this->getURL();
	// 	$this->service['baseURL'] 			= $this->getBaseUrl();
	// 	$this->service['URL'] 				= $this->getURLentier();
	// 	$this->service['route'] 			= $this->getRoute();
	// 	$this->service['parameters'] 		= $this->getRouteParameters();
	// 	$this->service['controller'] 		= $this->getController();
	// 	$this->service['actionName'] 		= $this->getActionName();
	// 	$this->service['groupeName'] 		= $this->getGroupeName();
	// 	$this->service['bundleName'] 		= $this->getBundleName();
	// 	$this->service['controllerName'] 	= $this->getControllerName();
	// 	$this->service['environnement'] 	= $this->getEnv();
	// 	$this->service['clientIP'] 			= $this->getIP();
	// 	$this->siteListener_InSession();
	// }

	// /**
	// * Dépose les informations de l'entité dans la session
	// * @return aetools
	// */
	// public function siteListener_InSession() {
	// 	$this->serviceSess->set($this->getShortName(), $this->service);
	// 	return $this;
	// }

	// /**
	// * Renvoie true si les informations de l'entité sont bien dans la session
	// * @return boolean
	// */
	// public function isSiteListener_InSession() {
	// 	return $this->serviceSess->get($this->getShortName()) !== null ? true : false;
	// }


	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ROUTES & URL
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Renvoie l'url de base
	 * @return string
	 */
	public function getBaseUrl() {
		return $this->isControllerPresent() ? $this->serviceRequ->getBaseUrl() : null;
	}

	/**
	 * Renvoie le path (string)
	 * @return string
	 */
	public function getURL() {
		return $this->isControllerPresent() ? $this->serviceRequ->getPathInfo() : null;
	}

	/**
	 * Renvoie l'URL entier
	 * @return string
	 */
	public function getURLentier() {
		return $this->isControllerPresent() ? $this->serviceRequ->getUri() : null;
	}

	/**
	 * Renvoie un array des routes contenant le préfixe $prefix
	 * @param $prefix
	 * @return array
	 */
	public function getAllRoutes($prefix = null) {
		if(is_string($prefix)) $pattern = '/^'.$prefix.'/'; // commence par $prefix
			else $pattern = '/.*/';
		$this->allRoutes = array();
		foreach($this->router->getRouteCollection()->all() as $nom => $route) {
			if(preg_match($pattern, $nom)) $this->allRoutes[] = $nom;
		}
		return $this->allRoutes;
	}

	/**
	 * Renvoie la route actuelle
	 * @return string
	 */
	public function getRoute() {
		return $this->route;
	}

	// /**
	//  * Renvoie un array des paramètres de route
	//  * @return array
	//  */
	// public function getRouteParameters() {
	// 	if($this->isControllerPresent()) {
	// 		$r = array();
	// 		$params = explode($this->aslash, $this->getURL());
	// 		foreach($params as $nom => $pr) if(strlen($pr) > 0) $r[$nom] = $pr;
	// 		// return $this->requAttributes->all();
	// 		// if(count($r) == 0) $r = null;
	// 		return $r;
	// 	} else return null;
	// }


	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// BUNDLES
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Renvoie la liste des bundles disponibles
	 * @param boolean $all = false
	 * @return array
	 */
	public function getBundlesList($all = false) {
		if($this->isContainerPresent()) {
			$bundles = $this->container->getParameter('kernel.bundles');
			if($all == false) $this->selectBundles($bundles);
			return $bundles;
		} else {
			$pathToConfig = $this->gotoroot.'web/'.self::PARAMS_FOLDER.'/bundles.yml';
			if(!@file_exists($pathToConfig)) return array();
			$yaml = new Parser();
			$config = $yaml->parse(@file_get_contents($pathToConfig));
			return $config;
		}
	}

	/**
	 * Enregistre la liste des bundles disponibles dans un fichier YAML
	 * dans : web/'.self::PARAMS_FOLDER.'/bundles.yml
	 * @return boolean
	 */
	public function updateBundlesInConfig() {
		$r = false;
		if($this->isContainerPresent()) {
			$bundles = $this->container->getParameter('kernel.bundles');
			$this->selectBundles($bundles);
			$pathToConfig = $this->gotoroot.'web/'.self::PARAMS_FOLDER.'/bundles.yml';
			$dumper = new Dumper();
			$r = @file_put_contents($pathToConfig, $dumper->dump($bundles, self::MAX_YAML_LEVEL));
		}
		if($r == false) throw new Exception("Mise à jour des bundles dans web/'.self::PARAMS_FOLDER.'/bundles.yml : impossible d'écrire dans le fichier.", 1);
		return $r;
	}

	/**
	 * Sélectionne uniquement les bundles du dossier src/
	 * @param array &$bundles
	 */
	protected function selectBundles(&$bundles) {
		$grps = array();
		$bnds = array();
		$groupes = $this->exploreDir('/src', null, 'dossiers', false);
		foreach ($groupes as $groupe) $grps[] = $groupe['nom'];
		$motif = '#^('.implode('|', $grps).')#';
		foreach($bundles as $key => $bundle) if(preg_match($motif, $bundle)) $bnds[$key] = $bundle;
		$bundles = $bnds;
	}

	/**
	 * Renvoie le nom du bundle courant
	 * @return string
	 */
	public function getBundleName() {
		return $this->isControllerPresent() ? $this->bundleName : null;
	}

	/**
	 * Affiche la liste des bundles
	 */
	protected function afficheBundles() {
		$this->writeTableConsole('Liste des Bundles présents détectés par Symfony2', $this->getBundlesList());
	}


	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// CONTROLLER
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Renvoie le controller complet
	 * @return string
	 */
	public function getController() {
		return $this->isControllerPresent() ? $this->controllerPath : null;
	}

	/**
	 * Renvoie le dossier du controller
	 * @return string
	 */
	public function getCtrlFolder() {
		return $this->isControllerPresent() ? $this->ctrlFolder : null;
	}

	/**
	 * Renvoie le nom du controller
	 * @return string
	 */
	public function getControllerName() {
		return $this->isControllerPresent() ? $this->controllerName : null;
	}

	/**
	 * Renvoie le nom du groupeName
	 * @return string
	 */
	public function getGroupeName() {
		return $this->isControllerPresent() ? $this->groupeName : null;
	}

	/**
	 * Renvoie le nom de la méthode appelée dans le controller
	 * @return string
	 */
	public function getActionName() {
		return $this->isControllerPresent() ? $this->actionName : null;
	}

	/**
	 * Renvoie le nom de la méthode, sans "Action" appelée dans le controller
	 * @return string
	 */
	public function getSingleActionName() {
		return $this->isControllerPresent() ? $this->singleActionName : null;
	}


	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// IP
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Renvoie l'adresse IP utilisateur
	 * @return string
	 */
	public function getIP() {
		return $this->serviceRequ->getClientIp();
	}


	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ENVIRONNEMENT
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Renvoie mode d'environnement (dev, test, prod…)
	 * @return string
	 */
	public function getEnv() {
		return $this->isControllerPresent() ? $this->container->get('kernel')->getEnvironment() : null;
	}

	/**
	 * Renvoie mode d'environnement (dev, test, prod…)
	 * @return string
	 */
	public function isDev() {
		return $this->getEnv() === 'dev' ? true : false;
	}


	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// YML FILES
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function getYmlContent($file = 'config.yml') {
		$pathFile = $this->gotoroot.'app/config/'.$file;
		if(file_exists($pathFile)) {
			$yaml = new Parser();
			return $yaml->parse(file_get_contents($pathFile));
		}
		else throw new Exception("Le fichier YML ".$file." n'a pu être trouvé.", 1);
	}

	public function getConfigParameters($file, $data = null) {
		$content = $this->getYmlContent($file);
		if(is_string($data)) {
			if(isset($content['parameters'][$data])) return $content['parameters'][$data];
				else throw new Exception("aetools::getConfigParameters() : le paramètre \"".$data."\" n'existe pas dans le fichier ".$file.".", 1);
		}
		return $content['parameters'];
	}

	public function getLaboParam($data = null, $file = 'labo_parameters.yml') {
		$content = $this->getYmlContent($file);
		if(is_string($data)) {
			if(isset($content['parameters'][$data])) return $content['parameters'][$data];
				else throw new Exception("aetools::getConfigParameters() : le paramètre \"".$data."\" n'existe pas dans le fichier ".$file.".", 1);
		}
		return $content['parameters'];
	}

	// languages

	/**
	 * @dev voir pour charger la locale par défaut via Parser sur le fichier app/config.yml si le controller est absent
	 * Get parsed translation files
	 * @param string $domain = "messages"
	 * @param string $lang = null
	 * @param string $path = "src"
	 * @return array
	 */
	public function getTranslations($domain = "messages", $lang = null, $path = "src") {
		if($lang == null) 
			$this->isControllerPresent() ? $lang = $this->container->get('request')->getLocale() : $lang = "fr";
		$files = $this->exploreDir($this->gotoroot.$path, $domain.'.'.$lang.'.yml', 'fichiers', true, true);
		$result = array();
		$yaml = new Parser();
		foreach ($files as $file) {
			$result[] = $yaml->parse(file_get_contents($file['full']));
		}
		return $result;
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// USER
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	// /**
	//  * Charge l'utilisateur
	//  * @return User
	//  */
	// public function loadCurrentUser() {
	// 	$this->user = false;
	// 	if($this->isControllerPresent()) {
	// 		if($this->container->get('security.context')->isGranted('ROLE_USER')) {
	// 			$this->user = $this->container->get('security.context')->getToken()->getUser();
	// 		}
	// 	}
	// 	return $this->user;
	// }

	/**
	 * Renvoie roles hierarchy
	 * @return array
	 */
	public function getRolesHierarchy() {
		$hierarchy = false;
		if($this->isControllerPresent()) {
			$hierarchy = $this->container->getParameter('security.role_hierarchy');
		} else {
			// $pathToSecurity = $this->gotoroot.'app/config/security.yml';
			// $yaml = new Parser();
			// $rolesArray = $yaml->parse(file_get_contents($pathToSecurity));
			$rolesArray = $this->getYmlContent('security.yml');
			$hierarchy = $rolesArray['security']['role_hierarchy'];
		}
		return $hierarchy;
	}

	public function getListOfRoles() {
		$r = array();
		$roles = array_keys($this->getRolesHierarchy());
		foreach ($roles as $role) {
			$r[$role] = $role;
		}
		return $r;
	}

	public function getListOfRolesForSelect() {
		$r = array();
		$roles = array_keys($this->getRolesHierarchy());
		foreach ($roles as $role) {
			$r[$role] = 'roles.'.$role;
		}
		return $r;
	}

	/**
	 * Renvoie l'utilisateur
	 * @return User
	 */
	public function getUser() {
		$this->user = $this->user = $this->container->get('security.context')->getToken()->getUser();
		return $this->user;
	}

	// ////////////////////////////////////////////////////////////////////////////////////////////////////////
	// // SERIALIZATION
	// ////////////////////////////////////////////////////////////////////////////////////////////////////////

	// public function aeSerialize($data) {
	// 	if(is_array($data)) foreach($data as $key => $value) {
	// 		if(is_object($value)) {
	// 			$class = explode(self::ASLASH, get_class($value));
	// 			switch (end($class)) {
	// 				case 'ArrayCollection':
	// 					$data[$key] = $value->toArray();
	// 					break;
	// 				case 'DateTime':
	// 					// $data[$key] = $value->format('Y-m-d H:i:s');
	// 					break;
	// 				default:
	// 					// $data[$key] = $value;
	// 					break;
	// 			}
	// 		}
	// 	}
	// 	return $data;
	// }


	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// STRUCTURES DE CLASSES
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Renvoie un array de la hiérarchie de la classe
	 * @param mixed $className - nom de la classe (AVEC namespace !!) ou objet
	 * @param string $format - 'string' ou 'array' (défaut)
	 * @return array
	 */
	public function getClassHierarchy($className, $format = 'array') {
		if(!is_string($format)) $format = 'array';
		if(is_object($className)) $className = get_class($className);
		$parents = array();
		$treeB = $this->getClassTree($className);
		do {
			$treeB = reset($treeB);
			$parents[] = $treeB['shortName'];
			$treeB = $treeB['parent'];
		} while ($treeB !== false);
		unset($treeB);
		return strtolower($format) === 'string' ? implode(" ".self::SLASH." ", $parents) : $parents;
	}

	public function getClassTree($className) {
		if(is_object($className)) $className = get_class($className);
		$tree = array();
		$ReflectionClass = new ReflectionClass($className);
		// $meth = get_class_methods($className);
		// foreach ($meth as $key => $method) {
		// 	$methods[$method] = $this->getInfoMethod($ReflectionClass, $method);
		// }
		$tree[$className]['shortName'] = $this->getClassShortName($className);
		$tree[$className]['longName'] = $className;
		$tree[$className]['abstract'] = $ReflectionClass->isAbstract();
		// $tree[$className]['docComment'] = trim(str_replace(array("/**", "*/", "\n", "\r"), "", $ReflectionClass->getDocComment()));
		// $tree[$className]['methods'] = $methods;
		// parents
		$tree[$className]['parent'] = false;
		$parentClassName = get_parent_class($className);
		if($parentClassName !== false) $tree[$className]['parent'] = $this->getClassTree($parentClassName);
		return $tree;
	}

	// protected function getInfoMethod($ReflectionClass, $method) {
	// 	if($ReflectionClass->getMethod($method)->isPrivate()) 	$scope = 'private';
	// 	if($ReflectionClass->getMethod($method)->isProtected()) $scope = 'protected';
	// 	if($ReflectionClass->getMethod($method)->isPublic()) 	$scope = 'public';
	// 	$annotations = array();
	// 	$docDocument = $ReflectionClass->getMethod($method)->getDocComment();
	// 	// preg_match_all('#( )?@(.*?)\n#s', $docDocument, $annotations);
	// 	// returns
	// 	preg_match_all('#\ ?@return\ (.*?)\n#s', $docDocument, $result);
	// 	$annotations['return'] = $result[1];
	// 	// params
	// 	preg_match_all('#\ ?@param\ (.*?)\n#s', $docDocument, $result);
	// 	$annotations['param'] = $result[1];
	// 	// fulltext
	// 	// preg_match_all('#\*[^\*\\]\ ?((@param\ )|(@return\ )|(.*?))[\n\r]#s', $docDocument, $result);
	// 	$annotations['fulltext'] = trim(str_replace(array("/**", "*/", "\n", "\r"), "", $docDocument));
	// 	return array(
	// 		'scope'			=> $scope,
	// 		'static'		=> $ReflectionClass->getMethod($method)->isStatic(),
	// 		'abstract'		=> $ReflectionClass->getMethod($method)->isAbstract(),
	// 		'docComment'	=> $annotations
	// 	);
	// }


	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Fonctionnalités diverses
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Renvoie le nom de la méthode en fonction de l'attribut et du préfix
	 * On peut vérifier si la méthode est dans l'objet $testEntity en ajoutant cet objet ou sa classe (pas de shortname)
	 * @param string $attribute
	 * @param string $prefix = "set"
	 * @param mixed $testEntity = null
	 * @return string / false
	 */
	public function getMethodNameWith($attribute, $prefix = "set", $testEntity = null) {
		if(in_array($prefix, array("remove", "add"))) $attribute = preg_replace("#s$#i", "", $attribute);
		if(is_string($testEntity)) {
			try {
				$testEntity = new $testEntity();
			} catch (Exception $e) {
				throw new Exception('aetools:getMethodNameWith() : $testEntity ne correspond pas à un objet valide ! '.$e->getMessage(), 1);
			}
		}
		$method = $prefix.ucfirst($attribute);
		if(is_object($testEntity) && !method_exists($testEntity, $method)) $method = false;
		return $method;
	}

	/**
	 * Renvoie un nom aléatoire (normalement) impossible à avoir en doublon
	 * @return string
	 */
	static public function getRandomName() {
		return time().'_'.rand(10000, 99999);
	}

	public function slugify($text) { 
		// replace non letter or digits by -
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text);
		// trim
		$text = trim($text, '-');
		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		// lowercase
		$text = strtolower($text);
		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);
		if(empty($text)) return 'n-a';
		return $text;
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Fonctionnalités pour fixtures
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * getNameFixturesFileXML
	 * Renvoie le nom de fichier standard pour les données fixtures en XML
	 * @return string
	 */
	public function getNameFixturesFileXML($EntityClassName) {
		return "fixtures_".$this->getClassShortName($EntityClassName)."s.xml";
	}

	/**
	 * getNameFixturesFileCSV
	 * Renvoie le nom de fichier standard pour les données fixtures en CSV
	 * @return string
	 */
	public function getNameFixturesFileCSV($EntityClassName) {
		return "fixtures_".$this->getClassShortName($EntityClassName)."s.csv";
	}

	/**
	 * getDossierTextFiles
	 * Renvoie le nom du dossier contenant les fichiers texte
	 * @return string
	 */
	public function getDossierTextFiles() {
		return "txt";
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// AFFICHAGES HORS CONTROLLER (pour Terminal ou Fixtures)
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function writeConsole($t, $color = "normal", $rt = true) {
		if($this->isControllerAbsent()) {
			if(is_string($t)) printf($this->returnConsole($t, $color, $rt));
			if(is_array($t)) var_dump($t);
		}
	}

	public function echoMemoryHorsController($texte) {
		$this->writeConsole('Mémoire PHP : '.memory_get_usage().' '.$texte);
	}

	public function writeTableConsole($titre, $table, $l1 = 50, $l2 = 25) {
		$this->afficheTitre($titre, $l1, $l2);
		if(is_array($table)) {
			foreach($table as $nom => $value) {
				if(is_object($value)) $value = gettype($value)." (".count($value).")";
				if(is_array($value)) {
					$st1 = implode("\", \"", $value);
					$str = substr($st1, 0, $l2 - 10);
					if(strlen($st1) > strlen($str)) $cont = "…"; else $cont = "";
					$value = gettype($value)." (".count($value).") \"".$str.$cont."\"";
				}
				$this->afficheLine($nom, $value, $l1, $l2);
			}
		} else throw new Exception('Élément fourni n\'est pas un array : '.gettype($table));
		$this->echoRT();
	}

	public function afficheTitre($texte, $l1 = 50, $l2 = 25) {
		$this->writeConsole($this->texttools->fillOfChars($texte, $l1 + $l2 + 6), "table_titre", true);
	}

	public function afficheLine($name, $value, $l1 = 50, $l2 = 25) {
		$this->writeConsole($this->texttools->fillOfChars($name, $l1)." | ".$this->texttools->fillOfChars($value, $l2), "table_line", true);
	}

	public function returnConsole($t, $color = "normal", $rt = true) {
		switch ($color) {
			case 'error':
				return "\033[1;7;31m".$t."\033[00m".$this->getXRT($rt);
				break;
			case 'succes':
				return "\033[1;42;30m".$t."\033[00m".$this->getXRT($rt);
				break;
			case 'headline':
				return "\033[1;46;34m".$t."\033[00m".$this->getXRT($rt);
				break;
			case 'table_titre':
				return "\033[1;44;36m".$t."\033[00m".$this->getXRT($rt);
				break;
			case 'table_line':
				return "\033[1;40;37m".$t."\033[00m".$this->getXRT($rt);
				break;
			default:
				return "\033[00m".$t.$this->getXRT($rt);
				break;
		}		
	}

	public function getXRT($n = 1) {
		$rt = "";
		if($n !== false) {
			if($n === true) $n = 1;
			for ($i=0; $i < $n; $i++) { 
				$rt .= self::EOLine;
			}
		}
		return $rt;
	}

	public function echoRT($n = 1) {
		if($this->isControllerAbsent()) printf($this->getXRT($n));
	}

	public function dump_debug($name, $input, $limit = 4) {
		echo('<pre><h3>'.$name.' : '.gettype($input).'</h3>');
		var_dump($this->dump_debug_recursive($input, $limit));
		echo('</pre>');
	}

	public function dump_debug_recursive($input, $limit = 4) {
		if($limit > 0) {
			$data = array();
			switch (gettype($input)) {
				case Type::TARRAY:
					foreach ($input as $key => $value) $data[gettype($input).':'.count($input)][$key] = $this->dump_debug_recursive($value, $limit - 1);
					break;
				case Type::OBJECT:
					$ReflectionClass = new ReflectionClass(get_class($input));
					$methods = $ReflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
					foreach ($methods as $method) {
						$method = $method->getName();
						$parameters = $ReflectionClass->getMethod($method)->getParameters();
						if(preg_match('#^get#', $method)) {
							if(count($parameters) < 0) {
								try {
									$rdata = $this->dump_debug_recursive($input->$method(), $limit - 1);
								} catch (Exception $e) {
									$rdata = $e->getMessage();
								}
								$data[get_class($input)]['Public:'.$method]['getter:data'] = $rdata;
							} else {
								$data[get_class($input)]['Public:'.$method]['getter:params'] = $this->dump_debug_recursive($parameters, $limit - 1);
							}
						} else if(preg_match('#^set#', $method)) {
							$data[get_class($input)]['Public:'.$method]['setter:params'] = $this->dump_debug_recursive($parameters, $limit - 1);
						} else if(preg_match('#^(is|has)#', $method)) {
							$data[get_class($input)]['Public:'.$method]['test:params'] = $this->dump_debug_recursive($parameters, $limit - 1);
						} else {
							$data[get_class($input)]['Public:'.$method]['divers:params'] = $this->dump_debug_recursive($parameters, $limit - 1);
						}
					}
					break;
				case Type::DATETIME:
				case Type::DATETIMETZ:
				case Type::DATE:
				case Type::TIME:
					$data[gettype($input)] = $input->format(self::FORMAT_DATETIME_SQL);
					break;
				case Type::STRING:
				case Type::TEXT:
				case Type::BLOB:
					$data[gettype($input).':'.strlen($input)] = json_encode($input);
					break;
				default:
					$data[gettype($input)] = json_encode($input);
					break;
			}
			return $data;
		} else return '…';
	}

	public function getListOfServices() {
		return array(
			// Entities
			'aetools.aetools',
			'aetools.aeEntity',
			'aetools.aeSubEntity',
			// subEntity
			'aetools.aeItem',
			'aetools.aeMedia',
			'aetools.aeTier',
			// entités "libres"
			'aetools.aeTag',
			'aetools.aeStatut',
			'aetools.aePanier',
			'aetools.aeMessage',
			// entités à niveaux
			'aetools.aeCategorie',
			'aetools.aeSite',
			'aetools.aeArticle',
			'aetools.aePageweb',
			'aetools.aeFiche',
			'aetools.aeImage',
			'aetools.aePdf',
			'aetools.aeRawfile',
			// listener Entities
			'aetools.entityUtils',
			// autres services
			'aetools.debug',
			'aetools.aefixtures',
			'aetools.textutilities',
			'aetools.aeReponse',
			// JsTree
			'aetools.aeJstree',
			);
	}

	public function getReflexionMethodConstants() {
		return array(
			ReflectionMethod::IS_STATIC => 'static',
			ReflectionMethod::IS_PUBLIC => 'public',
			ReflectionMethod::IS_PROTECTED => 'protected',
			ReflectionMethod::IS_PRIVATE => 'private',
			ReflectionMethod::IS_ABSTRACT => 'abstract',
			ReflectionMethod::IS_FINAL => 'final',
			);
	}

	public function getReflexionClassConstants() {
		return array(
			ReflectionClass::IS_IMPLICIT_ABSTRACT => 'implicit abstract',
			ReflectionClass::IS_EXPLICIT_ABSTRACT => 'explicit abstract',
			ReflectionClass::IS_FINAL => 'final',
			);
	}

	public function getObjectReflectionClass($object) {
		if(is_object($object)) {
			return new ReflectionObject($object);
		} else {
			throw new Exception("aetools::getObjectReflectionClass() : le paramètre $object doit être un objet ! Type ".json_encode(gettype($object))." fourni en paramètre.", 1);
		}
	}

	public function getObjectProperties($object) {
			$result = array();
			if(is_object($object)) {
				$result['object'] = $object;
				$ReflectionClass = new ReflectionObject($object);
			} else if(is_string($object)) {
				$result['object'] = new $object();
				$ReflectionClass = new ReflectionClass($object);
			}
			$result['class']['comments'] = $this->commentsParser($ReflectionClass->getDocComment(), false);
			$result['class']['filename'] = $ReflectionClass->getFileName();
			$methods = $ReflectionClass->getMethods();
			foreach ($methods as $key => $method) {
				// access
				$result['methods'][$method->getName()]['comments'] = $this->commentsParser($method->getDocComment());
				$result['methods'][$method->getName()]['access'] = array();
				if($method->isStatic()) $result['methods'][$method->getName()]['access'][] = 'static';
				if($method->isPublic()) $result['methods'][$method->getName()]['access'][] = 'public';
				if($method->isProtected()) $result['methods'][$method->getName()]['access'][] = 'protected';
				if($method->isPrivate()) $result['methods'][$method->getName()]['access'][] = 'private';
				if($method->isAbstract()) $result['methods'][$method->getName()]['access'][] = 'abstract';
				if($method->isFinal()) $result['methods'][$method->getName()]['access'][] = 'final';
				// constructor
				$result['methods'][$method->getName()]['constructor'] = false;
				if($method->isConstructor()) $result['methods'][$method->getName()]['constructor'] = true;
				// desctructor
				$result['methods'][$method->getName()]['destructor'] = false;
				if($method->isDestructor()) $result['methods'][$method->getName()]['destructor'] = true;
			}
			$properties = $ReflectionClass->getProperties();
			foreach ($properties as $key => $property) {
				// access
				$result['properties'][$property->getName()]['comments'] = $this->commentsParser($property->getDocComment());
				$result['properties'][$property->getName()]['access'] = array();
				if($property->isStatic()) $result['properties'][$property->getName()]['access'][] = 'static';
				if($property->isPublic()) $result['properties'][$property->getName()]['access'][] = 'public';
				if($property->isProtected()) $result['properties'][$property->getName()]['access'][] = 'protected';
				if($property->isPrivate()) $result['properties'][$property->getName()]['access'][] = 'private';
				if($property->isDefault()) $result['properties'][$property->getName()]['access'][] = 'default';
				// if($property->isAbstract()) $result['properties'][$property->getName()]['access'][] = 'abstract';
				// if($property->Final()) $result['properties'][$property->getName()]['access'][] = 'final';
			}
			$constants = $ReflectionClass->getConstants();
			foreach ($constants as $key => $constant) {
				$result['constants'][$key] = $constant;
			}
			return $result;
	}

	/**
	 * Parse des commentaires PHP    		
	 * @param string $comments
	 * @param boolean $findKeys = true
	 * @return array
	 */
	public function commentsParser($comments, $findKeys = true) {
		$comments = preg_split('#(\\r|\\n)#', $comments);
		$lines = array();
		foreach ($comments as $comment) {
			if(!preg_match('#^[[:space:]]*(\/\*\*|\*\/)[[:space:]]*#', $comment)) {
				// ligne ok
				$line = trim(preg_replace('#^[[:space:]]*\*[[:space:]]*#', '', $comment));
				if(strlen($line) > 0) $lines[] = $line;
			}
		}
		if(!$findKeys) return $lines;
		$result = array();
		$result['texts'] = array();
		$result['keys'] = array();
		foreach ($lines as $line) {
			if(preg_match('#^@[A-Za-z]{2,}\s*#', $line)) {
				// clé détectée
				$split = preg_split('#[[:space:]]+#', $line, 3);
				if(!isset($split[1])) $split[1] = '';
				if(!isset($split[2])) $split[2] = '';
				$tmp = array();
				$tmp['key'] = '';
				$tmp['var'] = '';
				$tmp['type'] = '';
				// key
				$tmp['key'] = preg_replace('#^@#', '', $split[0]);
				unset($split[0]);
				// type
				if(preg_match('#^\$#', $split[2])) {
					$tmp['var'] = $split[2];
					$tmp['type'] = $split[1];
				} else if(preg_match('#^\$#', $split[1])) {
					// pas de type mais une var
					$tmp['var'] = implode(' ', $split);
				} else {
					// ni type ni var, juste un commentaire
					$tmp['var'] = implode(' ', $split);
				}
				$result['keys'][] = $tmp;
			} else {
				$result['texts'][] = preg_replace('#(\\t|\\s)+#', ' ', $line);
			}
		}
		return $result;
	}

}

