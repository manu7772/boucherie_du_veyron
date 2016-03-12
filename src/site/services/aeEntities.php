<?php
namespace site\services;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;
// aetools
use site\services\aetools;

// informations classes
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\ClassMetadata;
use site\adminBundle\services\flashMessage;

use site\adminBundle\Entity\baseEntity;

use \ReflectionMethod;
use \Exception;
use \DateTime;
use \Date;
use \Time;

class aeEntities extends aetools {

	const NOM_OBJET_TYPE 		= "objet_type";		// nom de l'objet type basique
	const NOM_OBJET_READY 		= "objet_ready";	// nom de l'objet rempli avec les valeurs par défaut
	const REPO_DEFAULT_VAL		= "defaultVal";		// méthode repository pour récupération des entités par défaut
	const ONLY_CONCRETE			= true;				// ne récupère que les entités concrètes (non abstract ou interface)
	const CURRENT_ADDED			= 'current';		// paramètre pour new object : ajouter la version courante
	const DEFAULT_ADDED			= 'defaultVersion';	// paramètre pour new object : ajouter la version par défaut (champ : 'defaultVersion' = 1)
	const CHAMP_SUBSTVERSION	= 'slug';			// nom du champ substitutif pour version d'une entité (pour éviter une requête supplémentaire lors de la recherche de la version de l'entité)
	const VALUE_DEFAULT			= "defaultVal";

	const COLLECTION_ASSOC_NAME	= "collection";		// nom pour le type collection
	const SINGLE_ASSOC_NAME		= "single";			// nom pour le type single
	const MULTI_ASSOC 			= "";				// ajout au getter de relation type collection
	// VERSION
	const ALLVERSIONS_NAME		= "allVersions";
	const CURRENTVERSION_NAME	= "currentVersion";

	// ENTITÉS / ENTITÉ COURANTE
	protected $entity = array();			// tableau des entités
	protected $current = null;				// className (nom long) de l'entité courante
	protected $onlyConcrete;

	protected $currentVersion;				// objet version courante

	protected $CMD;							// array de classMetaData
	protected $_em = false;					// entity_manager
	protected $repo;						// repository

	protected $version;						// données de version en session (array)
	protected $versionClassName = false;	// className de l'entité servant de version
	protected $versionsActives = null;		// boolean : système de version actif ou non
	protected $champSubstitutifForVersion;	// champ substitutif contenant le slug (ou autre) de la version de l'entité

	protected $listOfEnties = null;			// liste des entités de src
	protected $completeListOfEnties = null;	// liste des entités complète


	public function __construct(ContainerInterface $container = null, $em = null) {
		parent::__construct($container);
		$this->_em = $em; // ---> IMPORTANT : l'entityListener fournit SON entityManager !!
		$this->initDataaeEntities();
		return $this;
	}

	/**
	 * Initialise les données pour le service
	 * @return aeEntities
	 */
	protected function initDataaeEntities() {
		// echo("<p style='color:red'>SERVICE classe : ".$this->getShortName()."</p>");
		// autres données sans controller
		$this->getEm();
		$this->repo = array();
		$this->CMD = array();
		// Détection automatique du mode FIXTURES
		if($this->isControllerPresent() === true) {
			// autre données dépendant du controller
		}
		$this->setOnlyConcrete();
		// Versions
		$this->currentVersion = false;
		if($this->isVersionActive() !== false) {
			$this->getCurrentVersion();
			$this->champSubstitutifForVersion = $this->getMethodNameWith(self::CHAMP_SUBSTVERSION, $this->getVersionEntityShortName());
		}
		return $this;
	}





    /**
     * Check entity after change (edit…)
     * @param baseEntity &$entity
	 * @return aeEntities
     */
    public function checkAfterChange(baseEntity &$entity) {
        // Check statut… etc.
        $this->checkStatuts($entity, false);
        $this->checkInversedLinks($entity, false);
        return $this;
    }

	/**
	 * Persist en flush a baseEntity
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	public function save(baseEntity &$entity) {
		$aeReponse = $this->container->get('aetools.aeReponse');
		$response = true;
		$sadmin = false;
		$user = $this->container->get('security.context')->getToken()->getUser();
		if(is_object($user)) if($user->getBestRole() == 'ROLE_SUPER_ADMIN') $sadmin = true;
		$message = 'Entité enregistrée.';
		try {
			$this->_em->persist($entity);
		} catch (Exception $e) {
			$response = false;
			if(($this->isDev() && $sadmin) === true)
				$message = $e->getMessage();
				else $message = 'Erreur système.';
		}
		try {
			$this->_em->flush();
		} catch (Exception $e) {
			$response = false;
			if(($this->isDev() && $sadmin) === true)
				$message = $e->getMessage();
				else $message = 'Erreur système.';
		}
		return $aeReponse
			->setResult($response)
			->setMessage($message)
			->setData(array('id' => $entity->getId()))
			;
		// return $this;
	}

	/**
	 * Persist en flush a baseEntity / pour tests
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	public function NOsave(baseEntity &$entity) {
		$response = true;
		$message = 'Entité enregistrée.';
		$this->_em->persist($entity);
		$this->_em->flush();
		return $this
			->container->get('aetools.aeReponse')
			->setResult($response)
			->setMessage($message)
			->setData(array('id' => $entity->getId()))
			;
	}






	/**
	 * Définit le mode de récupération de la liste des entités
	 * @param boolean $val - true : ne récupère que les entités concrètes / false : récupère tout
	 * @return boolean
	 */
	public function setOnlyConcrete($val = null) {
		$val !== false ? $this->onlyConcrete = self::ONLY_CONCRETE : $this->onlyConcrete = !self::ONLY_CONCRETE;
		return $this->onlyConcrete;
	}

	/**
	 * Appel direct à une méthode du repository de l'entité courante
	 * @param string $methode
	 * @param mixed $params
	 * @return mixed
	 */
	// public function __call($methode, $params = null) {
	// 	return $this->callRepo($methode, $params);
	// }

	// public function callRepo($methode, $params = null) {
	// 	$repo = $this->getRepo($this->current);
	// 	if($repo !== false) {
	// 		if(method_exists($repo, $methode)) {
	// 			$reFunc = new ReflectionMethod($repo, $methode);
	// 			$funParams = $reFunc->getParameters();
	// 			// var_dump($reFunc->getParameters());
	// 			$required = 0;
	// 			$optional = 0;
	// 			$nbparams = 0;
	// 			foreach($funParams as $RP) {
	// 				$RP->isOptional() === true ? $optional++ : $required++;
	// 				$nbparams++;
	// 				// echo($RP->getPosition().' Nom : '.$RP->getName()."<br>");
	// 				// echo(' - Position : '.$RP->getPosition()."<br>");
	// 				// echo(' - Default : '.$RP->isDefaultValueAvailable()." (".gettype($RP->isDefaultValueAvailable()).")<br>");
	// 				// if($RP->isDefaultValueAvailable()) echo(' - Valeur default : '.$RP->getDefaultValue()."<br>");
	// 				// echo(' - Optionnel : '.$RP->isOptional()." (".gettype($RP->isOptional()).")<br>");
	// 				// echo("<br>");
	// 			}
	// 			if($params < $required) throw new Exception("Nombre de paramètres insuffisant : ".$required." requis, seulement ".$params." fournis.", 1);
	// 			switch(count($params)) {
	// 				case 1: $result = $this->getRepo()->$methode($params[0]); break;					
	// 				case 2: $result = $this->getRepo()->$methode($params[0], $params[1]); break;					
	// 				case 3: $result = $this->getRepo()->$methode($params[0], $params[1], $params[2]); break;					
	// 				case 4: $result = $this->getRepo()->$methode($params[0], $params[1], $params[2], $params[3]); break;					
	// 				case 5: $result = $this->getRepo()->$methode($params[0], $params[1], $params[2], $params[3], $params[4]); break;					
	// 				case 6: $result = $this->getRepo()->$methode($params[0], $params[1], $params[2], $params[3], $params[4], $params[5]); break;					
	// 				case 7: $result = $this->getRepo()->$methode($params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6]); break;					
	// 				default: $result = $this->getRepo()->$methode(); break;
	// 			}
	// 			return $result;
	// 		} else throw new Exception("Méthode \"".$methode."\" inconnue (Repository : \"".get_class($this->getRepo())."\")", 1);
	// 	}
	// 	return false;
	// }

	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// VERSIONS
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Renvoie si le système de versions est actif (si une entité de type version existe)
	 * @return boolean
	 */
	public function isVersionActive() {
		if($this->versionsActives === null) {
			$this->versionsActives = false;
			$this->setOnlyConcrete(true);
			foreach($this->getListOfEnties(false) as $entity => $shortname) {
				$entity = new $entity;
				if(method_exists($entity, "__call")) {
					if($entity->isVersion()) {
						$this->versionClassName = get_class($entity);
						$this->versionsActives = true;
					}
				}
			}
			if($this->versionsActives === false) $this->writeConsole(">>>>> SYSTÈME DE VERSIONS INACTIF ! <<<<<", 'error');
		}
		// throw new Exception("Service version : aucune entité de version n'existe.");
		return $this->versionsActives;
	}

	/**
	 * Renvoie les données sur la version courante
	 * Données stockées en session
	 * @return array
	 */
	public function getCurrentVersion() {
		// // pas de version en activité
		// if($this->isVersionActive() !== true) return false;
		// // version mémorisée, on la renvoie
		// if($this->currentVersion !== false) return $this->currentVersion;
		// // récupération de la version si elle n'était pas mémorisée
		// if($this->isControllerPresent()) {
		// 	// controller présent
		// 	$ver = $this->getEm()
		// 		->getRepository($this->getVersionEntityClassName())
		// 		->findVersionWithLinks($ver, 'slug', $this->getConfig('version_in_session'));
		// 	if(count($ver) > 0) $this->currentVersion = reset($ver);
		// }
		return $this->currentVersion;
	}

	/**
	 * Renvoie le slug la version courante
	 * @return string
	 */
	public function getCurrentVersionSlug() {
		if($this->isVersionActive() !== true) return false;
		if($this->getCurrentVersion() !== false) {
			return $this->currentVersion->getSlug();
		}
		return false;
	}

	/**
	 * Renvoie le nom la version courante
	 * @return string
	 */
	public function getCurrentVersionNom() {
		if($this->isVersionActive() !== true) return false;
		if($this->getCurrentVersion() !== false) {
			return $this->currentVersion->getNom();
		}
		return false;
	}

	/**
	 * Renvoie le nom de la classe servant de version
	 * @return string - false si aucune entité version
	 */
	public function getVersionEntityClassName() {
		if($this->isVersionActive() !== true) return false;
		return $this->versionClassName;
	}

	/**
	 * Renvoie le nom court de la classe servant de version
	 * @return string - false si aucune entité version
	 */
	public function getVersionEntityShortName() {
		if($this->isVersionActive() !== true) return false;
		return $this->getClassShortName($this->getVersionEntityClassName());
	}

	/**
	 * Renvoie le nom de la classe
	 * @return string
	 */
	public function getName() {
		return get_class($this);
		// return get_called_class();
	}

	/**
	 * Renvoie le nom de la classe
	 * @return string
	 */
	public function getShortName() {
		return $this->getClassShortName($this->getName());
	}

	/**
	 * Vérifie si une entité existe : si oui, renvoie le className
	 * @param mixed $name - nom long ou court (ou objet)
	 * @param boolean $extended - recherche étendue à toutes ou uniquement /src
	 * @param boolean $getShortName - true = renvoie le nom court plutôt que le className
	 * @return string / false si l'entité n'existe pas
	 */
	public function entityClassExists($name, $extended = false, $getShortName = false) {
		if(is_bool($name)) return false;
		// if(is_array($name)) $name = reset($name);
		// echo('Type : '.gettype($name).'<br>');
		if(is_object($name)) $name = get_class($name);
		if(in_array($name, $this->getListOfEnties($extended))) {
			$find = array_keys($this->completeListOfEnties, $name);
			return $getShortName === true ? $name : reset($find);
		}
		// le nom est déjà un nom long : on le renvoie tel quel
		if(array_key_exists($name, $this->getListOfEnties($extended))) {
			return $getShortName === true ? $this->completeListOfEnties[$name] : $name;
		}
		// sinon, renvoie false : l'entité n'existe pas
		return false;
	}

	/**
	 * Renvoie le className de l'entité courante (ou de l'entité passée en paramètre) si elle existe
	 * @param mixed $entity
	 * @return string / false si l'entité n'existe pas
	 */
	public function getEntityClassName($entity = null) {
		if($entity === null) $entity = $this->current;
		if(is_object($entity)) $entity = get_class($entity);
		return $this->entityClassExists($entity, false, false);
	}

	/**
	 * Renvoie le nom court de l'entité courante (ou de l'entité passée en paramètre) si elle existe
	 * @param mixed $entity
	 * @return string / false si l'entité n'existe pas
	 */
	public function getEntityShortName($entity = null) {
		if($entity === null) $entity = $this->current;
		if(is_object($entity)) $entity = get_class($entity);
		return $this->entityClassExists($entity, false, true);
	}

	/**
	 * Renvoie un array des entités contenues dans src (ou toutes les entités, si $extended = true)
	 * Sous la forme liste[shortName] = nameSpace
	 * @param boolean $extended
	 * @param boolean $force
	 * @return array
	 */
	public function getListOfEnties($extended = false, $force = false) {
		if($this->listOfEnties === null || $this->completeListOfEnties === null || $force === true) {
			$this->listOfEnties = array();
			$this->completeListOfEnties = array();
			$entitiesNameSpaces = $this->getEm()->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
			// $this->writeTableConsole('Configuration > MetadataDriverImpl > AllClassNames > ', $entitiesNameSpaces);
			// recherche de tous les dossiers de src/ (donc tous les groupes de bundles)
			$groupesSRC = $this->exploreDir("src/", null, "dossiers", false);
			$groupes = array();
			foreach($groupesSRC as $nom) $groupes[] = $nom['nom'];
			// var_dump($groupes);die();
			foreach($entitiesNameSpaces as $ENS) {
				$do_it = true;
				if($this->onlyConcrete === true) {
					// supprime les classes abstraites et les interfaces
					$CMD = $this->getClassMetadata($ENS, true);
					if(is_object($CMD)) {
						$reflectionClass = $CMD->getReflectionClass();
						if($reflectionClass->isAbstract() || $reflectionClass->isInterface()) $do_it = false;
					} else $do_it = false;
				}
				if($do_it === true) {
					$EE = $this->getClassShortName($ENS);
					$exp = explode(self::ASLASH, $ENS);
					$group = reset($exp);
					if(in_array($group, $groupes)) $this->listOfEnties[$ENS] = $EE;
					$this->completeListOfEnties[$ENS] = $EE;
				}
			}
			// affichage terminal des listes d'entités
		}
		// var_dump($this->listOfEnties);die();
		return $extended === false ? $this->listOfEnties : $this->completeListOfEnties ;
	}

	/**
	 * initialise avec le nom de l'entité : !!! format "groupe\bundle\dossier\entite" !!!
	 * @param string $classEntite
	 */
	public function defineEntity($classEntite) {
		// $this->consoleLog("defineEntity 1 : ".$classEntite);
		// récupère le nom long s'il est en version courte
		$classEntite = $this->entityClassExists($classEntite);
		// $this->consoleLog("defineEntity 2 : ".$classEntite);
		$shortName = $this->getEntityShortName($classEntite);
		// $this->consoleLog("defineEntity 3 : ".$shortName);
		if($classEntite !== false) {
			// l'entité existe
			// $this->writeConsole('***** Changement d\'entité : '.$classEntite." *****", 'error');
			$this->current = $classEntite;
			$this->serviceNom = $shortName;
			// $this->consoleLog("defineEntity 4 : ".$this->current);
			if(!$this->isDefined($this->current)) {
				// $this->consoleLog("defineEntity 5 : ".$this->current." - non définie…");
				// l'entité n'est pas initialisée, on la crée
				$this->entity[$this->current] = array();
				// $this->entity[$this->current][self::NOM_OBJET_TYPE] = $this->newObject($classEntite, false);
				// $this->entity[$this->current][self::NOM_OBJET_READY] = $this->newObject($classEntite, true);
				$this->entity[$this->current]['className'] = $this->current;
				$this->entity[$this->current]['name'] = $shortName;
			}
			// Objet Repository
			$this->getRepo($this->current);
		} else {
			// l'entité n'existe pas
			return false;
		}
		return $this;
	}

	/**
	* Renvoie si l'entité est déjà définie
	* @param string $classEntite
	* @return boolean
	*/
	public function isDefined($classEntite) {
		// récupère le nom long si c'est un court
		$classEntite = $this->entityClassExists($classEntite);
		return array_key_exists($classEntite, $this->entity);
	}

	/**
	* Renvoie si l'entité est l'entité courante
	* @param string $classEntite
	* @return boolean
	*/
	public function isCurrent($classEntite) {
		// récupère le nom long si c'est un court
		$classEntite = $this->entityClassExists($classEntite);
		return $classEntite === $this->current;
	}

	/**
	* Renvoie les données de l'entité courante
	* @return array
	*/
	public function getCurrent() {
		return $this->current !== null ? $this->entity[$this->current] : false;
	}

	public function getNewEntity($classname = null) {
		$newEntity = new $classname();
		$this->fillAllAssociatedFields($newEntity);
		return $newEntity;
	}

	/**
	 * Renvoie un nouvel objet entité
	 * si $loadDefaults = true, charge les valeurs par défaut des entités liées
	 * $version -> si string, préciser le champ, puis la valeur, séparés par un pipe "|" (ex. : "cible|v1")
	 *			-> objet version
	 *          -> pour la version courante, mettre aeEntities::CURRENT_ADDED ou true
	 *          -> pour la version par défaut, mettre aeEntities::DEFAULT_ADDED (version par défaut (champ : 'defaultVersion' = 1))
	 *          -> pour ne pas ajouter de version, mettre false
	 * $loadDefaults
	 *          -> boolean ou array de booleans/repoMethodes des champs à remplir avec entités par défaut
	 *          (ex. : ['version'] = true / ['version'] = 'defaultVal' / ['version'] = aeEntities::CURRENT_ADDED / ['version'] = false)
	 * @param string $classEntite
	 * @param mixed $loadDefaults
	 * @param mixed $version - champ|valeur ou objet de la version
	 * @return object
	 */
	public function newObject($classEntite = null, $loadDefaults = false, $version = true) {
		if($version === true) $version = self::CURRENT_ADDED;
		if($version === false) $version = null;
		// entité par défaut si non précisée
		$classEntite = $this->getEntityClassName($classEntite);
		if($classEntite !== false) {
			// l'entité existe… on la crée
			$newObject = new $classEntite();
			$associationFieldNames = $this->getAssociationNamesOfEntity($newObject);
			// version par défaut demandée
			if(in_array($this->getVersionEntityShortName(), $associationFieldNames) && $this->isVersionActive()) {
				$versionClassName = $this->getVersionEntityClassName();
				// un champ "version" existe…
				if($version === self::CURRENT_ADDED) {
					// version courante
					if($this->getCurrentVersionSlug() !== false) {
						$version = 'slug|'.$this->getCurrentVersionSlug();
					} else $version = null;
				} else if($version === self::CURRENT_ADDED) {
					// version par défaut
					$version .= '|true';
				}
				// IMPORTANT : ATTRIBUTION DE LA VERSION EN PREMIER LIEU !!
				// recherche de la version si précisée en texte…
				if(is_string($version)) {
					$versionFind = explode('|', $version);
					if($versionFind[1] == "true") $versionFind[1] = true;
					if($versionFind[1] == "false") $versionFind[1] = false;
					if(count($versionFind) == 2) {
						$this->fillAssociatedField($this->getVersionEntityShortName(), $newObject, array($versionFind[0] => $versionFind[1]), false);
					}
				} else if($version instanceOf $versionClassName) {
					$this->fillAssociatedField($this->getVersionEntityShortName(), $newObject, $version, false);
				}
			}
			if($loadDefaults !== false) {
				// ajout des relations par défaut
				$defaultFields = array();
				if(!is_array($loadDefaults)) {
					// création du tableau des entités
					foreach($associationFieldNames as $field) $defaultFields[$field] = self::REPO_DEFAULT_VAL;
				} else {
					// tableau fourni en paramètre
					foreach ($loadDefaults as $field => $repoMethod) {
						if($repoMethod === true) $repoMethod = self::REPO_DEFAULT_VAL;
						if(is_string($repoMethod) && in_array($field, $associationFieldNames)) $defaultFields[$field] = $repoMethod;
					}
				}
				// load…
				// echo('<h3>Entite : '.$classEntite.'</h3>');
				foreach($defaultFields as $field => $value) {
					// echo('Field : '.$field.' / val. : '.$value.'<br>');
					$this->fillAssociatedField($field, $newObject, array($field => $value), true);
				}
			}
		} else {
			$tp = 'type ';if(!is_string($classEntite)) $classEntite = gettype($classEntite);else $tp = '';
			throw new Exception("Entité non reconnue (".$tp."\"".$classEntite."\"). (".$this->getName()."::newObject() / Ligne ".__LINE__.")", 1);
		}
		return $newObject;
	}

	/**
	 * Remplit les données d'une entité $object 
	 * Gère les relations bidirectionnelles si elles ne sont pas gérées par les entités elles-mêmes
	 * $what : éléments à ajouter : ARRAY de :
	 *    - string "défaults" pour ajouter les éléments obtenus du repository via la méthode self::REPO_DEFAULT_VAL
	 *    - objet
	 *    - array (d'objets)
	 *    - array associatif $what[champ] = array(valeurs)
	 *      --> pour les array, ajouter $what["defaults"] = true (ou méthode du repository), pour ajouter les valeurs par défaut en plus
	 * @param string $field
	 * @param object &$object
	 * @param mixed $what
	 * @param boolean $testVersions
	 */
	public function fillAllAssociatedFields(&$object, $what = null, $testVersions = true) {
		if($what === null) $what = self::VALUE_DEFAULT;
		if($this->isVersionActive() !== true) $testVersions = false;
		if($this->entityClassExists($object) !== false && is_object($object)) {
			$this->writeConsole('Remplissage de l\'entité '.get_class($object), 'succes');
			// echo('<h2>'.$object->getClassName().'</h2>');
			foreach($this->getAssociationNamesOfEntity($object) as $field) {
				// echo('- '.$field.' :');
				$whatFor = $what;
				if(is_array($what)) {
					if(isset($what[$field])) $whatFor = $what[$field];
				}
				$testVersionsFor = $testVersions;
				if(is_array($testVersions)) {
					if(isset($testVersions[$field])) if(is_bool($testVersions[$field])) $testVersionsFor = $testVersions[$field];
				}
				// echo(' '.$whatFor.'<br>');
				$this->fillAssociatedField($field, $object, $whatFor, $testVersionsFor);
			}
		} else $this->writeConsole(self::TAB1."L'entité ".get_class($object)." n'existe pas. (".$this->getName()."::fillAllAssociatedFields() / Ligne ".__LINE__.")", 'error');
	}

	/**
	 * Teste si deux entités on des versions compatibles
	 * @param object $obj1
	 * @param object $obj2
	 * @return boolean (ou array des classnames des objet n'ayant pas de version)
	 */
	public function isVersionComptatible($obj1, $obj2) {
		if($this->isVersionActive() !== true) return true;
		// test si les objets sont bien des entités…
		if(is_object($obj1) && is_object($obj2)) {
			$verMethode1 = $this->getEntityVersionSlug($obj1);
			$verMethode2 = $this->getEntityVersionSlug($obj2);
			if($verMethode1 !== false && $verMethode2 !== false) {
				if($verMethode1 === $verMethode2) {
					return true;
				} else {
					$this->writeConsole('Versions incompatibles : '.$verMethode1.' < = > '.$verMethode2.' ', 'error');
					return false;
				}
			} else return true;
		} else {
			$error = array();
			if(!is_object($obj1)) $error[] = "obj1 (".gettype($obj1).")";
			if(!is_object($obj2)) $error[] = "obj2 (".gettype($obj2).")";
			throw new Exception("Entité(s) ".implode(' / ', $error)." non reconnue(s) (".$this->getName()."::isVersionComptatible() / Ligne ".__LINE__.")", 1);
		}
	}

	/**
	 * Renvoie le slug de version d'une entité
	 * @param object $entity
	 * @return string / false si aucun
	 */
	public function getEntityVersionSlug($entity) {
		if(is_object($entity) && $this->entityClassExists($entity)) {
			$verMethode = $this->getMethodOfGetting($this->champSubstitutifForVersion, $entity);
			if($verMethode !== false) {
				$versionSlug = $entity->$verMethode();
				if(is_string($versionSlug)) return $versionSlug;
			}
		}
		return false;
	}

	/**
	 * Remplit les données d'une entité $object 
	 * Gère les relations bidirectionnelles si elles ne sont pas gérées par les entités elles-mêmes
	 * $what : éléments à ajouter
	 *    - string "defaults" pour ajouter les éléments obtenus du repository via la méthode self::REPO_DEFAULT_VAL
	 *    - objet
	 *    - array (d'objets)
	 *    - array associatif $what[champ] = array(valeurs)
	 *      --> pour les array, ajouter $what["defaults"] = true (ou (string)méthode du repository), pour ajouter les valeurs par défaut en +
	 * @param string $field
	 * @param object &$object
	 * @param mixed $what
	 * @param boolean $testVersions
	 * @return boolean (true si au moins une association a pu être réalisée)
	 */
	public function fillAssociatedField($field, &$object, $what = null, $testVersions = true) {
		if($what === null) $what = self::VALUE_DEFAULT;
		// $this->writeTableConsole("Recherches :", $what);
		if($this->isVersionActive() !== true) $testVersions = false;
		$result = false;
		if($this->entityClassExists($object) !== false) {
			// l'objet est bien une entité existante…
			if($this->hasAssociation($field, $object)) {
				// récupère la classe associée : $targetClass
				$targetClass = $this->getTargetEntity($field, $object);

				$verMeth = false;
				if($testVersions === true) $verMeth = $this->getEntityVersionSlug($object);
				$add = "<inconnu>";
				if(is_string($verMeth)) $add = $verMeth;
				if($verMeth === false) $add = "* pas de test de version * ";
				if($verMeth === null) $add = "* version courante en session * ";
				$this->writeConsole(self::TAB1.'Vérification de version -> versionSlug : '.$add);
				// Repository
				$this->writeConsole(self::TAB1."repository d'entité target : ".$targetClass);
				$tar_repo = $this->getRepo($targetClass, $verMeth);

				if($tar_repo != false) {
					$associates = array();
					// valeurs par défaut
					if($what === self::VALUE_DEFAULT || isset($what[self::VALUE_DEFAULT])) {
						$defaultMethod = self::REPO_DEFAULT_VAL;
						if(isset($what[self::VALUE_DEFAULT])) if(is_string($what[self::VALUE_DEFAULT])) {
							if(method_exists($tar_repo, $what[self::VALUE_DEFAULT])) $defaultMethod = $what[self::VALUE_DEFAULT];
						}
						$this->writeConsole(self::TAB1.'Ajout des valeurs par défaut : (->'.$defaultMethod.'())', 'normal');
						$associates = $tar_repo->$defaultMethod();
						// $this->writeConsole($defaultMethod.'()');
						if(is_object($associates)) $associates = array($associates);
					}
					if(!is_array($associates)) $associates = array();
					// echo('<pre>');
					// var_dump($associates);
					// echo('</pre>');
					// foreach ($associates as $key => $value) {
					// 	echo('<p> - '.$value->getNom().'</p>');
					// }
					//
					if(is_object($what)) $what = array($what);
					if(is_array($what)) {
						// + valeurs précisées (strings et/ou objets…)
						foreach($what as $tar_field => $tar_Entite) if(!in_array($tar_field, array(self::VALUE_DEFAULT))) {
							if($tar_Entite  instanceOf $targetClass) {
								$associates[] = $tar_Entite;
							}
							if(is_array($tar_Entite)) foreach($tar_Entite as $one_tar_Entite) {
								if(is_string($one_tar_Entite)) {
									$methode = $this->getMethodNameWith($tar_field, "findBy");
									$find = $tar_repo->$methode($one_tar_Entite);
									if(is_object($find)) $associates[] = $find;
									if(is_array($find)) $associates = array_merge($associates, $find);
								}
								if($one_tar_Entite instanceOf $targetClass) $associates[] = $one_tar_Entite;
							}
						}
					}
					if(count($associates) > 0) {
						// on a des résultats
						$this->writeConsole(self::TAB2.$field." : ".count($associates)." objet(s) \"".$this->getEntityShortName($targetClass)."\" à associer : ", 'normal');
						// associes les entités (avec/sans test de versions)
						$compte = 0;
						foreach($associates as $key => $value) if($value instanceOf $targetClass) {
							// $this->writeConsole("Self entity : ".gettype($object)." / Target entity : ".gettype($value), 'error');
							if($this->attachEachSides($field, $object, $value, $testVersions)) {
								// echo('<p> - Attachement : '.$field.' -> '.$value->getNom().'</p>');
								// l'association a eu lieu avec succès
								$compte++;
								if($this->getTypeOfAssociation($field) == self::SINGLE_ASSOC_NAME) break;
							}
						} else $this->writeConsole('Association incompatible : '.get_class($value).' < = > '.$targetClass, 'error');
						if($compte != count($associates)) { $style = 'error';$add = ' ('.(count($associates) - $compte).' manquants)'; } else { $style = 'normal';$add = ''; }
						$this->writeConsole(" ---> ".$compte." objet(s) associés.".$add, $style);
					} else $this->writeConsole(self::TAB2.$field." : aucun objet \"".$this->getEntityShortName($targetClass)."\" à associer.");
				}
			} // else throw new Exception("Ce champ ".$field." n'a pas d'association.", 1);
			else $this->writeConsole(self::TAB2."Ce champ ".$field." n'a pas d'association (ligne ".__LINE__.").", 'error');
		}
		return $result;
	}

	/**
	 * Vide les données d'un champ de l'objet $object 
	 * Gère les relations bidirectionnelles si elles ne sont pas gérées par les entités elles-mêmes
	 * @param string $field
	 * @param object &$object
	 * @param $object
	 */
	public function emptyField($field, &$object, $destroyOtherSides = false) {
		if(is_object($object) && $this->entityClassExists($object)) {
			if(!$this->hasAssociation($field, $object)) {
				// champ
				switch($this->getTypeOfField($field, $object)) {
					case Type::TARRAY:
						$gets = $this->getMethodOfGetting($field, $object);
						$object->$gets()->clear();
						break;
					case Type::DECIMAL:
					case Type::INTEGER:
					case Type::BIGINT:
					case Type::SMALLINT:
						$set = $this->getMethodOfSetting($field, $object);
						$this->isNullable($field, $object) ?
							$object->$set(null):
							$object->$set(0);
						break;
					case Type::FLOAT:
						$set = $this->getMethodOfSetting($field, $object);
						$this->isNullable($field, $object) ?
							$object->$set(null):
							$object->$set(0);
						break;
					case Type::BOOLEAN:
						$set = $this->getMethodOfSetting($field, $object);
						$this->isNullable($field, $object) ?
							$object->$set(false):
							$object->$set(true);
						break;
					case Type::OBJECT:
						$set = $this->getMethodOfSetting($field, $object);
						if($this->isNullable($field, $object)) $object->$set(null);
						break;
					case Type::DATETIME:
					case Type::DATETIMETZ:
					case Type::DATE:
					case Type::TIME:
						$set = $this->getMethodOfSetting($field, $object);
						$datetime = new DateTime(self::DATE_ZERO." ".self::TIME_ZERO);
						$this->isNullable($field, $object) ?
							$object->$set(null):
							$object->$set($datetime->format(self::FORMAT_DATETIME_SQL));
						break;
					default:
						// autres…
						// Typr::STRING
						// Typr::TEXT
						// Typr::BLOB
						$set = $this->getMethodOfSetting($field, $object);
						$this->isNullable($field, $object) ?
							$object->$set(null):
							$object->$set("");
						break;
				}
			} else {
				// association
				$this->detachEachSides($field, $object, $destroyOtherSides);
			}
			return true;
		}
		return false;
	}

	/**
	 * Détache une entité liée 
	 * Gère les relations bidirectionnelles si elles ne sont pas gérées par les entités elles-mêmes
	 * si $destroyOtherSides == true, supprime les entités liées qui ne peuvent avoir une relation nulle (attention : méthode RECURSIVE !!!)
	 * @param string $field
	 * @param object $entity
	 * @param boolean $destroyOtherSides
	 */
	public function detachEachSides($field, $entity, $destroyOtherSides = false) {
		if($this->hasAssociation($field, $entity)) {
			$obj_SET = $this->getMethodOfSetting($field, $entity);
			$obj_GET = $this->getMethodOfGetting($field, $entity);
			if(is_string($obj_GET) && is_string($obj_SET)) {
				$otherObj = $entity->$obj_GET();
				if(is_object($otherObj)) {
					$otherObj = array($otherObj);
					$obj_Type = self::SINGLE_ASSOC_NAME;
				} else if($otherObj instanceOf ArrayCollection) {
					$obj_Type = self::COLLECTION_ASSOC_NAME;
				} else throw new Exception("Retour de type non géré pour \"".get_class($entity)."::".$field."\" (\"".gettype($otherObj)."\"). (".$this->getName()."::detachEachSides() / Ligne ".__LINE__.")", 1);
				if(is_array($otherObj) || ($otherObj instanceOf ArrayCollection)) {
					foreach($otherObj as $oneOtherObj) {
						// $otherClass = get_class($oneOtherObj);
						$otherSideField = $this->get_OtherSide_sourceField($field, $entity);
						$tar_SET = $this->getMethodOfSetting($otherSideField, $oneOtherObj);
						$tar_GET = $this->getMethodOfGetting($otherSideField, $oneOtherObj);
						if(is_string($obj_GET) && is_string($obj_SET)) {
							$inverseElements = $oneOtherObj->$tar_GET();
							$contains = false;
							if(is_object($inverseElements)) {
								$tar_Type = self::SINGLE_ASSOC_NAME;
								if(($inverseElements === $entity) && ($inverseElements->getId() === $entity->getId())) $contains = true;
							} else if($inverseElements instanceOf ArrayCollection) {
								$tar_Type = self::COLLECTION_ASSOC_NAME;
								$contains = $inverseElements->contains($entity);
							} else throw new Exception("Retour de type non géré pour \"".get_class($oneOtherObj)."::".$otherSideField."\" (\"".gettype($inverseElements)."\"). (".$this->getName()."::detachEachSides() / Ligne ".__LINE__.")", 1);
							if($contains === true) {
								// Il faut le supprimer, car c'est lui
								if(!$this->isNullableField($otherSideField, $oneOtherObj)) {
									if($tar_Type === self::SINGLE_ASSOC_NAME) {
										// Purée !! on est bien ennuyés, là ! Il faut le supprimer alors qu'il ne peut être null…
									}
									if($tar_Type === self::COLLECTION_ASSOC_NAME && (count($inverseElements) === 1)) {
										// Purée !! on est bien ennuyés, là ! Il n'en reste qu'un, il faut le supprimer alors qu'il ne peut être null…
									}
								} else {
									// …peut être null (ouf !)
									// ENTITY INVERSE
									if($tar_Type === self::SINGLE_ASSOC_NAME) {
										$entity->$tar_SET(null);
									}
									if($tar_Type === self::COLLECTION_ASSOC_NAME) {
										$oneOtherObj->$tar_GET()->removeElement($entity);
									}
									// ENTITY
									if($obj_Type === self::SINGLE_ASSOC_NAME) {
										$entity->$obj_SET(null);
									}
									if($obj_Type === self::COLLECTION_ASSOC_NAME) {
										$entity->$obj_GET()->removeElement($oneOtherObj);
									}
								}
							}
						} else throw new Exception("Getter et/ou Setter absent (\"".gettype($oneOtherObj)."::".$otherSideField."\"). (".$this->getName()."::detachEachSides() / Ligne ".__LINE__.")", 1);
					}
					// on flush…
					// $this->getEm()->flush();
				}
			} else throw new Exception("Getter et/ou Setter absent (\"".gettype($entity)."::".$field."\"). (".$this->getName()."::detachEachSides() / Ligne ".__LINE__.")", 1);
		}
	}

	/**
	 * Attache une entité liée (ou pas)
	 * Gère les relations bidirectionnelles si elles ne sont pas gérées par les entités elles-mêmes
	 * @param string $field
	 * @param object $entity1
	 * @param object $entity2
	 * @param boolean $testVersions
	 * @return boolean (true = association réussie)
	 */
	public function attachEachSides($field, &$entity1, &$entity2, $testVersions = true) {
		// if($this->isVersionActive() !== true) return true;
		if($this->isVersionComptatible($entity1, $entity2)) {
			if($this->hasAssociation($field, $entity1)) {
				$obj_SET = $this->getMethodOfSetting($field, $entity1);
				// echo('<p>setter : '.$obj_SET.'</p>');
				$obj_GET = $this->getMethodOfGetting($field, $entity1);
				if(is_string($obj_SET)) {
					// setting pour $entity1
					$entity1->$obj_SET($entity2);
					// echo('<p style="color:orange;">- Attached '.$field.' : '.$entity1->$obj_GET().'</p>');
					$this->writeConsole('     • Association ok : '.$this->getEntityShortName($entity1).'->'.$obj_SET.'('.$entity2->getSlug().')', 'succes');
					if($this->isBidirectional($field, $entity1)) {
						// oui, bidirectionnelle
						$otherSideField = $this->get_OtherSide_sourceField($field, $entity1);
						if(is_string($otherSideField)) {
							$tar_SET = $this->getMethodOfSetting($otherSideField, $entity2);
							// setting pour $entity2
							$entity2->$tar_SET($entity1);
							$this->writeConsole('     • Reverse Side ok : '.$this->getEntityShortName($entity2).'->'.$tar_SET.'('.$entity1->getSlug().')', 'succes');
							return true;
						} else throw new Exception(self::TAB1."Données bidirectionnelles incomplètes : champ cible inconnu (\"".gettype($entity1)."::".$field."\" => \"".gettype($entity1)."::<INCONNU>\"). (".$this->getName()."::attachEachSides() / Ligne ".__LINE__.")", 1);
					}
					return true;
				} else throw new Exception(self::TAB1."Setter absent (\"".gettype($entity1)."::".$field."\"). (".$this->getName()."::attachEachSides() / Ligne ".__LINE__.")", 1);
			}
		} else $this->writeConsole(self::TAB1."Versions incompatibles : association impossible.", "error");
		return false;
	}


	// INFORMATIONS SUR LES CHAMPS D'ENTITÉS

	/**
	 * Renvoie si le champ existe
	 * @return boolean
	 */
	public function hasField($field, $entite = null) {
		$CMD = $this->getClassMetadata($entite);
		if(is_object($CMD)) {
			$isAbstract = $CMD->getReflectionClass()->isAbstract();
			return !$isAbstract ? $CMD->hasField($field) : false;
		}
		return false;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::hasField() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie la liste des champs (sans association)
	 * @return array
	 */
	public function getFieldNamesOfEntity($entite) {
		$CMD = $this->getClassMetadata($entite);
		if(is_object($CMD)) {
			$isAbstract = $CMD->getReflectionClass()->isAbstract();
			return !$isAbstract ? $CMD->getFieldNames(): array();
		}
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::getFieldNamesOfEntity() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie la liste des noms des associations
	 * @return array
	 */
	public function getAssociationNamesOfEntity($entite) {
		$CMD = $this->getClassMetadata($entite);
		if(is_object($CMD)) {
			$isAbstract = $CMD->getReflectionClass()->isAbstract();
			return !$isAbstract ? $CMD->getAssociationNames(): array();
		}
		return array();
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::getAssociationNamesOfEntity() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie la liste des noms des champs + associations
	 * @return array
	 */
	public function getAllFieldNamesOfEntity($entite) {
		if($this->getClassMetadata($entite) !== false) {
			$a = $this->getFieldNamesOfEntity($entite);
			$b = $this->getAssociationNamesOfEntity($entite);
			if($a !== false && $b !== false) return array_merge($a, $b);
				else return false;
		}
		return false;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::getAllFieldNamesOfEntity() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie le className l'entité associée
	 * @param string $field
	 * @param object $entite
	 * @return string
	 */
	public function getTargetEntity($field, $entite = null) {
		$CMD = $this->getClassMetadata($entite);
		if(is_object($CMD)) {
			if($this->hasAssociation($field, $entite)) {
				$obj_mapping = $CMD->getAssociationMapping($field);
				return $obj_mapping['targetEntity'];
			}
		}
		return null;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::hasField() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie si le champ doit être unique
	 * @param string $field
	 * @param object $entite
	 * @return boolean
	 */
	public function isUniqueField($field, $entite = null) {
		$CMD = $this->getClassMetadata($entite);
		if(is_object($CMD)) {
			if($this->hasField($field, $entite)) {//throw new Exception("Champ (".$entite."::".$field.") inexistant. (".$this->getName()."::isUniqueField() / Ligne ".__LINE__.")", 1);
				return $CMD->isUniqueField($field);
			}
			if($this->hasAssociation($field, $entite)) {
				// association
				$mapping = $CMD->getAssociationMapping($field);
				if(isset($mapping["joinColumns"][0]["unique"])) return $mapping["joinColumns"][0]["unique"];
					// !!!! cas d'association type collection… à améliorer
					else return true;
			}
		}
		return true;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::isUniqueField() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie si le champ doit être unique
	 * @param string $field
	 * @param object $entite
	 * @return boolean
	 */
	public function isNullableField($field, $entite = null) {
		$CMD = $this->getClassMetadata($entite);
		if(is_object($CMD)) {
			if($this->hasField($field, $entite)) {//throw new Exception("Champ (".$entite."::".$field.") inexistant. (".$this->getName()."::isNullableField() / Ligne ".__LINE__.")", 1);
				return $CMD->isNullable($field);
			}
			if($this->hasAssociation($field, $entite)) {
				// association
				$mapping = $CMD->getAssociationMapping($field);
				if(isset($mapping["joinColumns"][0]["nullable"])) return $mapping["joinColumns"][0]["nullable"];
					// !!!! cas d'association type collection… à améliorer
					else return true;
			}
		}
		return true;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::isNullableField() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie le nom de la méthode d'attribution / false si la méthode est manquante
	 * @param string $field
	 * @param object $entite
	 * @return string
	 */
	public function getTypeOfField($field, $entite = null) {
		$CMD = $this->getClassMetadata($entite);
		// $this->writeConsole(self::TAB2.'Info '.__LINE__." : "."getTypeOfField = ", 'headline', false);
		if(is_object($CMD)) {
			if($this->hasField($field, $entite)) { // throw new Exception("Champ (".$entite."::".$field.") inexistant. (".$this->getName()."::getTypeOfField() / Ligne ".__LINE__.")", 1);
				$type = $CMD->getTypeOfField($field);
				// $this->writeConsole('Type de champ : '.$type);
				return $type;
			} else return false;
		}
		return false;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::getTypeOfField() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie le nom de la méthode d'attribution / false si la méthode est manquante
	 * @param string $field
	 * @param object $entite
	 * @return string
	 */
	public function getMethodOfSetting($field, $entite = null) {
		// $this->writeConsole(self::TAB2.'Info '.__LINE__." : "."getMethodOfSetting", 'headline');
		$methode = false;
		if($this->getClassMetadata($entite) !== false) {
			$TOF = $this->getTypeOfField($field, $entite);
			if($TOF !== false) {
				if($TOF === Type::TARRAY) {
					// Type arrayCollection
					$methode = $this->getMethodNameWith($field, 'add');
				} else {
					$methode = $this->getMethodNameWith($field, 'set');
				}
			} else {
				switch ($this->getTypeOfAssociation($field, $entite)) {
					case self::COLLECTION_ASSOC_NAME: // collection
						// $this->writeConsole(self::TAB2.'Info '.__LINE__." : ".self::COLLECTION_ASSOC_NAME, 'headline');
						$methode = $this->getMethodNameWith($field, 'add');
						break;
					case self::SINGLE_ASSOC_NAME: // single
						// $this->writeConsole(self::TAB2.'Info '.__LINE__." : ".self::SINGLE_ASSOC_NAME, 'headline');
						$methode = $this->getMethodNameWith($field, 'set');
						break;
				}
			}
			// $this->writeConsole(self::TAB2.'Info '.__LINE__." : ".$methode, 'headline');
			if(method_exists($entite, $methode)) return $methode;
				else return null;
				// else throw new Exception("Setter (".$methode.") inexistant : VOUS DEVEZ LE CRÉER. (".$this->getName()."::getMethodOfSetting() / Ligne ".__LINE__.")", 1);
		}
		return null;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::getMethodOfSetting() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie le nom de la méthode de suppression / false si la méthode est manquante
	 * @param string $field
	 * @param object $entite
	 * @return string
	 */
	public function getMethodOfRemoving($field, $entite = null) {
		// $this->writeConsole(self::TAB2.'Info '.__LINE__." : "."getMethodOfSetting", 'headline');
		$methode = false;
		if($this->getClassMetadata($entite) !== false) {
			$TOF = $this->getTypeOfField($field, $entite);
			if($TOF !== false) {
				if($TOF === Type::TARRAY) {
					// Type arrayCollection
					$methode = $this->getMethodNameWith($field, 'remove');
				} else {
					$methode = false;
				}
			} else {
				switch ($this->getTypeOfAssociation($field, $entite)) {
					case self::COLLECTION_ASSOC_NAME: // collection
						// $this->writeConsole(self::TAB2.'Info '.__LINE__." : ".self::COLLECTION_ASSOC_NAME, 'headline');
						$methode = $this->getMethodNameWith($field, 'remove');
						break;
					case self::SINGLE_ASSOC_NAME: // single
						// $this->writeConsole(self::TAB2.'Info '.__LINE__." : ".self::SINGLE_ASSOC_NAME, 'headline');
						$methode = false;
						break;
				}
			}
			// $this->writeConsole(self::TAB2.'Info '.__LINE__." : ".$methode, 'headline');
			if(method_exists($entite, $methode)) return $methode;
				else return null;
				// else throw new Exception("Setter (".$methode.") inexistant : VOUS DEVEZ LE CRÉER. (".$this->getName()."::getMethodOfSetting() / Ligne ".__LINE__.")", 1);
		}
		return false;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::getMethodOfRemoving() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie le nom de la méthode de récupération / false si la méthode est manquante
	 * @param string $field
	 * @param object $entite
	 * @return string
	 */
	public function getMethodOfGetting($field, $entite = null) {
		$methode = false;
		if($this->getClassMetadata($entite) !== false) {
			if($this->hasField($field, $entite)) { // throw new Exception("Champ (".$entite."::".$field.") inexistant. (".$this->getName()."::getMethodOfGetting() / Ligne ".__LINE__.")", 1);
				$methode = $this->getMethodNameWith($field, 'get');
			} else if($this->hasAssociation($field, $entite)) {
				switch ($this->getTypeOfAssociation($field, $entite)) {
					case self::COLLECTION_ASSOC_NAME: // collection
						$methode = $this->getMethodNameWith($field, 'get');
						break;
					case self::SINGLE_ASSOC_NAME: // single
						$methode = $this->getMethodNameWith($field, 'get');
						break;
				}
			}
			if(method_exists($entite, $methode)) return $methode;
				else return null;
				// else throw new Exception("Getter (".$methode.") inexistant : VOUS DEVEZ LE CRÉER. (".$this->getName()."::getMethodOfGetting() / Ligne ".__LINE__.")", 1);
		}
		return false;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::getMethodOfGetting() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie si le champ est de type association
	 * @param string $field
	 * @param object $entite
	 * @return boolean
	 */
	public function isAssociationWithSingleJoinColumn($field, $entite = null) {
		$CMD = $this->getClassMetadata($entite);
		if(is_object($CMD)) {
			// $this->writeConsole(self::TAB2.'Info '.__LINE__.' : isAssociationWithSingleJoinColumn ? '.$field." ---> ".get_class($entite), 'headline');
			// if(!$this->hasField($field, $entite)) throw new Exception("Champ (".$entite."::".$field.") inexistant. (".$this->getName()."::isAssociationWithSingleJoinColumn() / Ligne ".__LINE__.")", 1);
			// $this->isAssociationWithSingleJoinColumn($field) ? $this->writeConsole('OUI !') : $this->writeConsole('NON !');
			return $CMD->isAssociationWithSingleJoinColumn($field, $entite);
		}
		return false;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::isAssociationWithSingleJoinColumn() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie si le champ est de type association
	 * @param string $field
	 * @param object $entite
	 * @return boolean
	 */
	public function hasAssociation($field, $entite = null) {
		$CMD = $this->getClassMetadata($entite);
		if(is_object($CMD)) {
			// $this->writeConsole(self::TAB2.'Info '.__LINE__.' : hasAssociation ? '.$field." ---> ".get_class($entite), 'headline');
			// if(!$this->hasField($field, $entite)) throw new Exception("Champ (".$entite."::".$field.") inexistant. (".$this->getName()."::hasAssociation() / Ligne ".__LINE__.")", 1);
			// $CMD->hasAssociation($field) ? $this->writeConsole('OUI !') : $this->writeConsole('NON !');
			return $CMD->hasAssociation($field);
		}
		return false;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::hasAssociation() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie si une relation est de type bidirectionnelle
	 * true si oui
	 * false si non ou si pas d'association 
	 * @param string $field
	 * @param object $entite
	 * @return boolean
	 */
	public function isIdentifier($field, $entite = null) {
		$CMD = $this->getClassMetadata($entite);
		if(is_object($CMD)) {
			return $CMD->isIdentifier($field);
		}
		return false;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::isBidirectional() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie si une relation est de type bidirectionnelle
	 * true si oui
	 * false si non ou si pas d'association 
	 * @param string $field
	 * @param object $entite
	 * @return boolean
	 */
	public function isBidirectional($field, $entite = null) {
		$CMD = $this->getClassMetadata($entite);
		if(is_object($CMD)) {
			// if(!$this->hasField($field, $entite)) throw new Exception("Champ (".$entite."::".$field.") inexistant. (".$this->getName()."::isBidirectional() / Ligne ".__LINE__.")", 1);
			if(!$this->hasAssociation($field, $entite)) return false;
			$tar_entity = $this->getTargetEntity($field, $entite);
			$tar_field = $this->get_OtherSide_sourceField($field, $entite);
			return ($this->isAssociationInverseSide($field, $entite) || $this->isAssociationInverseSide($tar_field, $tar_entity));
		}
		return false;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::isBidirectional() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Vérifie et associe les champs liés + bidirectionnels + inverseSide
	 * @param object $entity
	 * @param boolean $flush = true
	 * @return boolean
	 */
	public function checkInversedLinks(&$entity, $flush = true) {
		$r = true;
		// $this->getEm()->persist($entity);
		// $this->getEm()->flush(); // flush : sinon on obtient de objets Doctrine\ORM\PersistentCollection
		$classname = get_class($entity);
		$shortname = $this->getEntityShortName($classname);
		// echo('<p><strong>Classe entité : '.$classname.' / '.$shortname.'</strong></p>');
		$fields = $this->getInverseSideFields($classname);
		foreach ($fields as $field) {
			// uniquement si "mappedBy"…
			// echo('<p>- field : '.$field.'</p>');
			$otherSideSource = $this->get_OtherSide_sourceField($field, $classname);
			if(is_string($otherSideSource)) {
				// echo('<p>--> field mapped : '.$otherSideSource.'</p>');
				// il faut rattacher…
				$target = $this->getEntityClassName($this->getTargetEntity($field, $classname));
				// $targetRepo = $this->getEm()->getRepository($target);
				$get = $this->getMethodOfGetting($field, $classname);
				// echo('<p>--> target : '.$target.' -> '.$get.'()</p>');
				// return Doctrine\ORM\PersistentCollection->getValues()
				$data = $entity->$get()->getValues();
				// if(get_class($data) == $target) $data = array($data);
				// if(gettype($data) == Type::TARRAY) $data = 
				if(is_array($data)) {
					// echo('<p>--> éléments : '.count($data).'</p>');
					foreach ($data as $item) if($target != $classname) {
						$targetClass = get_class($item);
						$nom = $item->getId();
						if(method_exists($item, 'getNom')) $nom .= '/'.$item->getNom();
						// echo('<p style="color:green;">--> vérif : '.$target.' = '.$targetClass.' => '.$nom.'</p>');
						$dataSet = $this->getMethodOfSetting($otherSideSource, $targetClass);
						$dataGet = $this->getMethodOfGetting($otherSideSource, $targetClass);
						$dataFromGet = $item->$dataGet();
						if(method_exists($dataFromGet, 'getValues')) {
							$entities = $dataFromGet->getValues();
							if(is_object($entities)) $entities = array($entities);
							$entities = new ArrayCollection($entities);
							if(!$entities->contains($entity)) $item->$dataSet($entity);
						}
					}
				} else {
					// echo('<p>--> éléments : <span style="color:red;">'.get_class($data).'</span></p>');
				}
				// check les liens éventuellement perdus…
				$this->checkLosts($field, $entity, $flush);
			}
		}
		// die('<h3>fin :-)</h3>');
		// flush…
		if($flush == true) $r = $this->save($entity);
		return $r;
	}

	/**
	 * Répare (supprime) les inverseSide manquants sur une entité
	 * @param object $field
	 * @param string $entity
	 * @param boolean $flush = true
	 * @return boolean
	 */
	public function checkLosts($field, $entity, $flush = true) {
		$r = true;
		$classname = get_class($entity);
		// liste des champs liés + bidirectionnels + inverseSide
		$fields = $this->getInverseSideFields($classname);
		// flush…
		if($flush == true) $r = $this->getEm()->flush();
		// throw new Exception("checkLosts non encore programmé !", 1);
		return $r;
	}

	/**
	 * Renvoie les champs INVERSESIDE (mappedBy)
	 * @param string $entite
	 * @return array
	 */
	public function getInverseSideFields($entite) {
		$list = array();
		$shortname = $this->getEntityShortName($entite);
		$fields = $fields = $this->getAssociationNamesOfEntity($entite);
		foreach ($fields as $field) {
			if($field != $shortname && $this->isAssociationInverseSide($field, $entite) && !$this->isAssociationWithSingleJoinColumn($field, $entite)) {
				$list[] = $field;
			}
		}
		return $list;
	}

	/**
	 * Renvoie les champs PROPRIÉTAIRES (inversedBy)
	 * @param string $entite
	 * @return array
	 */
	public function getProprietarySideFields($entite) {
		$list = array();
		$shortname = $this->getEntityShortName($entite);
		$fields = $fields = $this->getAssociationNamesOfEntity($entite);
		foreach ($fields as $field) {
			if($field != $shortname && !$this->isAssociationInverseSide($field, $entite) && !$this->isAssociationWithSingleJoinColumn($field, $entite)) {
				$list[] = $field;
			}
		}
		return $list;
	}

	/**
	 * Renvoie si une relation bidirectionnelle est propriétaire
	 * true si oui
	 * false si non ou si pas d'association 
	 * @param string $field
	 * @param object $entite
	 * @return boolean
	 */
	public function isAssociationMappedSide($field, $entite = null) {
		return !$this->isAssociationInverseSide($field, $entite);
	}

	/**
	 * Renvoie si une relation bidirectionnelle est inverse
	 * true si oui
	 * false si non ou si pas d'association 
	 * @param string $field
	 * @param object $entite
	 * @return boolean
	 */
	public function isAssociationInverseSide($field, $entite = null) {
		$CMD = $this->getClassMetadata($entite);
		if(is_object($CMD)) {
			// if(!$this->hasField($field, $entite)) throw new Exception("Champ (".$entite."::".$field.") inexistant. (".$this->getName()."::isAssociationInverseSide() / Ligne ".__LINE__.")", 1);
			// if(!$this->isBidirectional($field, $entite)) return false;
			return $CMD->isAssociationInverseSide($field);
		} else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::isAssociationInverseSide() / Ligne ".__LINE__.")", 1);
	}

	/**
	 * Renvoie le type d'association du champ / false si aucune
	 * @param string $field
	 * @param object $entite
	 * @return string / false si aucune association
	 */
	public function getTypeOfAssociation($field, $entite = null) {
		// $this->writeConsole(self::TAB2.'Info '.__LINE__.' : getTypeOfAssociation ? '.$field." ---> ".get_class($entite), 'headline');
		$CMD = $this->getClassMetadata($entite);
		if(is_object($CMD)) {
			// $this->writeConsole(self::TAB2.'Info '.__LINE__.' : getTypeOfAssociation ? '.$field." ---> ".get_class($entite), 'headline');
			// if(!$this->hasField($field, $entite)) throw new Exception("Champ (".$entite."::".$field.") inexistant. (".$this->getName()."::getTypeOfAssociation() / Ligne ".__LINE__.")", 1);
			// Champ non associatif
			if(!$this->hasAssociation($field, $entite)) return false;
			// Champ associatif : renvoie le type : "single" / "collection"
			if($CMD->isCollectionValuedAssociation($field)) return self::COLLECTION_ASSOC_NAME;
			if($CMD->isSingleValuedAssociation($field)) return self::SINGLE_ASSOC_NAME;
		}
		return false;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::getTypeOfAssociation() / Ligne ".__LINE__.")", 1);
	}

	// ASSOCIATION : ENTITÉS INVERSES OU MAPPED

	/**
	 * Renvoie le champ de l'entité inverse (ou mapped) / sinon null
	 * sinon renvoie false dans tous les autres cas
	 * @param string $field
	 * @param object $entite
	 * @return string
	 */
	public function get_OtherSide_sourceField($field, $entite = null) {
		$CMD = $this->getClassMetadata($entite);
		if(is_object($CMD)) {
			// if($this->hasField($field, $entite)) {
				if($this->hasAssociation($field, $entite)) {
					$association = $CMD->getAssociationMapping($field);
					if(is_string($association["inversedBy"])) return $association["inversedBy"];
					return $association["mappedBy"];
				}
			// }
			return false;
		}
		return false;
		// else throw new Exception("Entité (".gettype($entite)." : ".$entite.") inexistante. (".$this->getName()."::getFieldNamesOfEntity() / Ligne ".__LINE__.")", 1);
	}


	// DEFAULT

	public function setAsDefault(&$entite, $set = null) {
		if($entite->getDefault() === $set) return;
		if($set === false || ($set == null && $entite->getDefault() === true)) {
			// set false
			// echo('- Default '.$entite.' : false !<br>');
			$entite->setDefault(false);
		} else {
			// set true => vérification
			$entite->setDefault(true);
			$dm = $entite->isDefaultMultiple();
			// echo('nb max : '.$dm.'<br>');
			if($dm !== true) {
				$items = $this->getRepo($entite->getClassName())->findByDefault(true);
				// echo('Trouvés : '.count($items).' = '.implode(', ', $items).'<br>');
				if(count($items) > 0) foreach ($items as $key => $oneItem) if($entite->getId() == $oneItem->getId()) unset($items[$key]);
				// echo('Retenus : '.count($items).' = '.implode(', ', $items).'<br>');
				if(is_int($dm)) {
					if($dm < 1) $dm = 1;
				} else $dm = 1;
				// echo('nb max : '.$dm.'<br>');
				if($dm == 1) {
					$cpt = count($items) - $dm + 1;
					if($cpt > 0) foreach ($items as $oneItem) {
						$this->setAsDefault($oneItem, false);
						$cpt--;
						if($cpt < 1) break;
					}
				} else if($dm > 1 && (count($items) + 1) > $dm) {
					//
					$message = $this->container->get('flash_messages')->send(array(
						'title'		=> 'Default max. dépassé',
						'type'		=> flashMessage::MESSAGES_ERROR,
						'text'		=> 'Vous ne pouvez plus attribuer d\'élément supplémentaire. Désactivez un autre pour pouvoir attribuer celui-ci.',
					));
					// annule les changements sur $entite
					$this->getEm()->refresh($entite);
				}
			}
		}
		// flush
		// die('END');
		$this->getEm()->flush();
	}


	// DELETIONS

	/**
	 * Supprime une entité sans la retirer de la base (sauf si 'deleted')
	 * 4 États : actif => inactif => deleted => et enfin, suppression de la base
	 * @param baseEntity &$entite
	 */
	public function softDeleteEntity(&$entite) {
		if(method_exists($entite, 'setStatut')) {
			// si un champ statut existe : Règle :
			// actif ---> inactif
			// inactif ou expired ---> deleted (uniquement visible du SUPER ADMIN)
			$niveau = $entite->getStatut()->getNiveau();
			if(in_array($niveau, array('IS_AUTHENTICATED_ANONYMOUSLY'))) {
				$statut = $this->getEm()->getRepository('site\adminBundle\Entity\statut')->findInactif();
			} else if(in_array($niveau, array('ROLE_TRANSLATOR', 'ROLE_EDITOR', 'ROLE_ADMIN'))) {
				$statut = $this->getEm()->getRepository('site\adminBundle\Entity\statut')->findDeleted();
			} else {
				$statut = 'delete';
			}
			if(is_array($statut)) $statut = reset($statut);
			if(is_object($statut)) {
				$entite->setStatut($statut);
				// gestion de la suppression de default…
				$this->setAsDefault($entite, false);
			} else {
				$this->getEm()->remove($entite);
			}
		} else {
			// sinon on la supprime
			$this->getEm()->remove($entite);
		}
		// flush
		$this->getEm()->flush();
	}

	/**
	 * Supprime toutes les entités qui ont un statut temporaire
	 * option : array $listOfId - si null, supprime tous
	 * @param string $shortName
	 * @param array $listOfId = null
	 * @return aeEntities
	 */
	public function deleteAllTemp($shortName, $listOfId = null) {
		$items = $this->getRepo($shortName)->findTempStatut($listOfId);
		foreach ($items as $item) $this->softDeleteEntity($item);
		// 	try {
		// 		// voir ici pour comparer l'heure, et éviter de supprimer les fichiers trop récents
		// 		$this->_em->remove($item);
		// 	} catch (Exception $e) {
		// 		// rien…
		// 	}
		// }
		// try {
		// 	$this->_em->flush();
		// } catch (Exception $e) {
		// 	// rien…
		// }
		return $this;
	}

	/**
	 * Rétablit une entité inactive
	 * @param baseEntity &$entite
	 */
	public function softActivateEntity(&$entite) {
		if(method_exists($entite, 'setStatut')) {
			// si un champ statut existe
			$actif = $this->getEm()->getRepository('site\adminBundle\Entity\statut')->findActif();
			$entite->setStatut($actif);
			$this->getEm()->flush();
		}
	}



	// STATUT

	/**
	 * Vérifie le statut et l'attribue si null
	 * @param object &$entity
	 * @param boolean $flush = true
	 */
	public function checkStatuts(&$entity, $flush = true) {
		// echo('<h4>Check statut sur '.get_class($entity).'</h4>');
		if(method_exists($entity, 'getStatut')) {
			// echo('<p>getStatut existe</p>');
			if($entity->getStatut() == null) {
				// echo('<p>Statut NON rempli !!!</p>');
				$statut = $this->getEm()->getRepository('site\adminBundle\Entity\statut')->defaultVal();
				// var_dump($statut);
				if(is_array($statut)) $statut = reset($statut);
				if(is_object($statut)) $entity->setStatut($statut);
			}
			// else echo('<p>Statut déjà rempli : ok</p>');
		}
		if($flush == true) $this->save($entity);
	}


	// ENTITY MANAGER ET REPOSITORY

	/**
	 * Renvoie l'Entity Manager
	 * @return manager
	 */
	public function getEm() {
		if($this->_em == null) {
			if(is_object($this->container)) $this->_em = $this->container->get('doctrine')->getManager();
		}
		return $this->_em;
	}

	/**
	 * Renvoie le Repository de l'entité courante (ou fournie)
	 * $version -> null : version par défaut (defaultVersion = true)
	 *          -> false = pas de test de version
	 *          -> string = slug de la version à recherche
	 *          -> 'current' = version courante
	 * @param mixed $entity - shortname, classeName ou objet entité
	 * @param string $versionSlug / si false, ne teste pas la version
	 * @return repository / false
	 */
	public function getRepo($entity = null, $versionSlug = 'current') {
		if($this->isVersionActive() !== true) $versionSlug = false;
		if(is_object($entity)) $entity = get_class($entity);
		$entity = $this->getEntityClassName($entity);
		if($entity !== false) {
			$this->repo[$entity] = $this->getEm()->getRepository($entity);
			// initialisation du repository
			if(method_exists($this->repo[$entity], "setVersion")) {
				if($versionSlug === 'current') $versionSlug = $this->getCurrentVersionSlug();
				$this->repo[$entity]->setVersion($versionSlug);
			} // else $this->writeConsole('Aucune méthode de version prévue dans le repository !!!', 'error');
			// $this->writeConsole('Repository défini pour '.$entity.' : OK.');
			if(method_exists($this->repo[$entity], "declareContext")) {
				$this->repo[$entity]->declareContext($this);
			}
			return $this->repo[$entity];
		}
		return false;
	}

	/**
	 * Renvoie la ClassMetadataInfo de l'entité / ou de l'entité courante
	 * @param mixed &$entity (nom ou objet)
	 * @return ClassMetadata
	 */
	public function getClassMetadata(&$entity = null, $extended = false) {
		$entityCopy = $this->getEntityClassName($entity);
		if($extended === true) $entityCopy = $entity;
		if($entityCopy !== false) {
			if(!isset($this->CMD[$entityCopy])) {
				$this->CMD[$entityCopy] = $this->getEm()->getClassMetadata($entityCopy);
			}
			// renvoie la classe dans l'objet entity SI ça n'est pas un objet. Sinon on la garde telle quelle.
			if(!is_object($entity)) $entity = $entityCopy;
			// Renvoie l'objet ClassMetadata
			return $this->CMD[$entityCopy];
		} else {
			return null;
			// return throw new Exception("Entité \"".$entity."\" inexistante. (".$this->getName()."::getClassMetadata() / Ligne ".__LINE__.")", 1);
		}
	}

	// /**
	//  * Renvoie la description de l'entité
	//  * @param mixed $entityClassName (nom ou objet)
	//  * @return array
	//  */

	// public function getMetaInfo($className) {
	// 	$r = array();
	// 	$r['CMData'] = $this->getClassMetadata($className);
	// 	if($r['CMData'] !== false) {
	// 		// informations sur la classe (entité)
	// 		$r['classInfo']['className'] = $r['CMData']->getName();
	// 		$r['classInfo']['tableName'] = $r['CMData']->getTableName();
	// 		$r['classInfo']['repoName'] = $r['CMData']->customRepositoryClassName;
	// 		$r['classInfo']['reflexProp'] = $r['CMData']->getReflectionProperties();
	// 		// $r['classInfo']['lifecycleCallbacks'] = $r['CMData']->getLifecycleCallbacks(!!!!!!!argument!!!!!!!!);
	// 		// $r['CMDataMethods'] = get_class_methods($r['CMData']);
	// 		// $colNoAssoc = $r['CMData']->getColumnNames();
	// 		$colNoAssoc = $r['CMData']->getFieldNames();
	// 		$colWtAssoc = $r['CMData']->getAssociationNames();
	// 		foreach(array_merge($colNoAssoc, $colWtAssoc) as $nom) {
	// 			// if((substr($nom, -1) == "s" && substr($nom, -2, -1) != "s") || (substr($nom, -2) == "ss")) $nom = substr($nom, 0, -1);
	// 			$r['listColumns'][$r['CMData']->getFieldName($nom)] = $this->getMetaInfoField($className, $r['CMData']->getFieldName($nom));
	// 		}
	// 		// Liste des libellés du tableau -> pour admin
	// 		$rr = array();
	// 		foreach($r['listColumns'] as $val) {
	// 			foreach($val as $nom => $val2) {
	// 				$rr[$nom] = $nom;
	// 			}
	// 		}
	// 		$r['libelles'] = $rr;
	// 	} else return false;
	// 	// $r['entiteName'] = $className;
	// 	return $r;
	// }

	// /**
	//  * Renvoie la description d'un champ de l'entité
	//  * @param mixed $entityClassName (nom ou objet)
	//  * @param string $field
	//  * @return array
	//  */
	// public function getMetaInfoField($className, $field) {
	// 	$CMD = $this->getClassMetadata($className);
	// 	if(is_object($CMD)) {
	// 		$r = array();
	// 		// $field = $CMD->getFieldForColumn($column);
	// 		if($CMD->hasAssociation($field) === false) {
	// 			// Sans association
	// 			$r = $CMD->getFieldMapping($field);
	// 			$r['Association'] = "aucune";
	// 		} else {
	// 			// Avec association
	// 			$r = $CMD->getAssociationMapping($field);
	// 			if($CMD->isSingleValuedAssociation($field)) {
	// 				$r['Association'] = self::SINGLE_ASSOC_NAME;
	// 				$r['unique'] = $r["joinColumns"][0]["unique"];
	// 				$r['nullable'] = $r["joinColumns"][0]["nullable"];
	// 			} else if($CMD->isCollectionValuedAssociation($field)) {
	// 				$r['Association'] = self::COLLECTION_ASSOC_NAME;
	// 				// $r['nullable'] = $CMD->isNullable($field);
	// 				// $r['unique'] = $CMD->isUniqueField($field);
	// 			} else {
	// 				// Association inconnue !!!
	// 				$r['Association'] = "[inconnue]";
	// 			}
	// 		}
	// 	} else return false;
	// 	return $r;
	// }


	// AFFICHAGE DES INFORMATIONS

	/**
	 * Affiche la liste des entités
	 */
	protected function afficheEntities() {
		$this->writeTableConsole('Liste des entités présentes utilisées pour Fixtures', $this->entitiesList);
	}

	/**
	 * Affiche la liste des entités
	 */
	protected function afficheEntitiesFound() {
		$this->writeTableConsole('Liste des entités présentes détectées par Doctrine2', $this->completeListOfEnties);
	}



}