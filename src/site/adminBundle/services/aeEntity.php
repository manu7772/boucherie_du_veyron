<?php
namespace site\adminBundle\services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
// aetools
use site\adminBundle\services\aetools;
use site\adminBundle\services\aeReponse;

// informations classes
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\ClassMetadata;
use site\adminBundle\services\flashMessage;

use site\adminBundle\Entity\baseEntity;
use site\adminBundle\Entity\statut;

use \ReflectionMethod;
use \Exception;
use \DateTime;
use \Date;
use \Time;

/**
 * @dev classe temporairement en travaux
 */
class aeEntity extends aetools {

	const NAME					= 'aeEntity'; 		// nom du service
	const CALL_NAME				= 'aetools.aeEntity'; // comment appeler le service depuis le controller/container

	const REPO_DEFAULT_VAL		= "defaultVal";		// méthode repository pour récupération des entités par défaut

	const COLLECTION_ASSOC_NAME	= "collection";		// nom pour le type collection
	const SINGLE_ASSOC_NAME		= "single";			// nom pour le type single


	// ENTITÉS / ENTITÉ COURANTE
	protected $entity = array();			// tableau des entités
	protected $current = null;				// className (nom long) de l'entité courante
	protected $onlyConcrete;

	protected $CMD;							// array de classMetaData
	protected $_em = false;					// entity_manager
	protected $repo;						// repository

	protected $listOfEnties = null;			// liste des entités de src
	protected $listOfExtendedEnties = null;	// liste des entités complète

	/**
	 * Constructeur
	 * @valid méthode validée
	 * @param ContainerInterface $container = null
	 * @param EntityManager $em = null
	 * @return aeEntity
	 */
	public function __construct(ContainerInterface $container = null, EntityManager $em = null) {
		parent::__construct($container);
		$this->_em = $em; // ---> IMPORTANT : l'entityListener fournit SON entityManager !!
		$this->getEm();
		$this->repo = array();
		$this->CMD = array();
		// Détection automatique du mode FIXTURES
		if($this->isControllerPresent() === true) {
			// autre données dépendant du controller
		}

		$this->setOnlyConcrete(true);
		$this->container->get('aetools.debug')->debugNamedFile('listOfEntities', array("onlyConcrete" => $this->getOnlyConcrete()), true, true);
		$this->getListOfEnties(true, true);
		return $this;
	}

	public function getNom() {
		return self::NAME;
	}

	public function callName() {
		return self::CALL_NAME;
	}



	/**
	 * Renvoie le service de l'entité
	 * Renvoie les services/entités parents dans l'ordre, puis aeEntity en dernier recours
	 * @param mixed $entity
	 * @return object / null
	 */
	public function getEntityService($entity) {
		if(is_object($entity)) {
			$entityClassName = get_class($entity);
			$entityShortName = $entity->getClassName();
		} else if(is_string($entity)) {
			if($this->isLongName($entity)) {
				// $entity = nom long
				$entityClassName = $entity;
				$entityShortName = $this->getEntityShortName($entity);
			} else {
				// $entity = nom court
				$entityClassName = $this->getEntityClassName($entity);
				$entityShortName = $entity;
			}
			$entity = new $entityClassName();
		}
		// parent classes
		if(method_exists($entity, 'getParentsClassNames')) {
			$parents = $entity->getParentsClassNames(true);
		} else {
			$parents = array(
				$entityShortName,
				'entity',
			);
		}
		// Recherche du service le plus proche de l'entité
		$service = null;
		foreach ($parents as $parent) {
			$aeServiceName = "aetools.ae".ucfirst(preg_replace('#^base#', '', $parent));
			if($this->container->has($aeServiceName)) {
				$service = $this->container->get($aeServiceName);
				if($parent != $entityShortName) $service->defineEntity($entityShortName);
				return $service;
			}
		}
		return null;
	}






	/**
	 * Set mode de récupération de la liste des entités
	 * @param boolean $onlyConcrete - true : ne récupère que les entités concrètes / false : récupère tout
	 * @return boolean
	 */
	public function setOnlyConcrete($onlyConcrete = true) {
		$this->onlyConcrete = (boolean) $onlyConcrete;
		return $this;
	}

	/**
	 * Get mode de récupération de la liste des entités
	 * @return boolean
	 */
	public function getOnlyConcrete() {
		return $this->onlyConcrete;
	}




	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// CLASSES D'ENTITES
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Vérifie si une entité existe : si oui, renvoie le className
	 * @param string $name - className ou shortName
	 * @param boolean $extended - recherche étendue à toutes ou uniquement /src
	 * @param boolean $getShortName - true = renvoie le nom court plutôt que le className
	 * @return string / false si l'entité n'existe pas
	 */
	public function entityClassExists($name, $extended = false, $getShortName = false) {
		if(is_object($name)) $name = get_class($name);
		if(!is_string($name)) return false;
		if($this->isProxyClass($name)) {
			// Proxies
			return false;
		}
		// Extended
		$extended ? $list = $this->getListOfAllEntities() : $list = $this->getListOfEnties() ;
		// search…
		if(in_array($name, $list)) {
			$find = array_keys($list, $name)[0];
			return $getShortName === true ? $name : $find;
		}
		// le nom est déjà un nom long : on le renvoie tel quel
		if(array_key_exists($name, $list)) {
			return $getShortName === true ? $list[$name] : $name;
		}
		// sinon, renvoie false : l'entité n'existe pas
		return false;
	}

	/**
	 * Vérifie si une entité proxy existe dans le dossier /src
	 * Si oui, renvoie le className de l'entité (non proxy, donc)
	 * @dev Méthode à développer
	 * @param string $proxyName - className de type proxy
	 * @param boolean $getShortName - true = renvoie le nom court plutôt que le className
	 * @return string / false si l'entité n'existe pas
	 */
	public function isProxyInSrcEntities($proxyName, $getShortName = false) {
		return $proxyName;
	}

	/**
	 * Renvoie le className de l'entité courante (ou de l'entité passée en paramètre) si elle existe
	 * @param mixed $entity
	 * @param boolean $extended - recherche étendue à toutes ou uniquement /src
	 * @return string / false si l'entité n'existe pas
	 */
	public function getEntityClassName($entity = null, $extended = false) {
		if($entity === null) $entity = $this->current;
		if(is_object($entity)) $entity = get_class($entity);
		return $this->entityClassExists($entity, $extended, false);
	}

	/**
	 * Renvoie le nom court de l'entité courante (ou de l'entité passée en paramètre) si elle existe
	 * @param mixed $entity
	 * @param boolean $extended - recherche étendue à toutes ou uniquement /src
	 * @return string / false si l'entité n'existe pas
	 */
	public function getEntityShortName($entity = null, $extended = false) {
		if($entity === null) $entity = $this->current;
		if(is_object($entity)) $entity = get_class($entity);
		return $this->entityClassExists($entity, $extended, true);
	}

	/**
	 * Get array des entités contenues dans src (ou toutes les entités, si $extended = true)
	 * Sous la forme liste[nameSpace] = shortName (ou inverse si $fliped = true)
	 * @param boolean $extended = false
	 * @param boolean $force = false
	 * @param fliped $force = false
	 * @return array
	 */
	public function getListOfEnties($extended = false, $force = false, $fliped = false) {
		if($this->listOfEnties === null || $this->listOfExtendedEnties === null || $force === true) {
			$this->listOfEnties = array();
			$this->listOfExtendedEnties = array();
			$entitiesNameSpaces = $this->getEm()->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
			// recherche de tous les dossiers de src/ (donc tous les groupes de bundles)
			$groupes = $this->getSrcGroupes(false);
			foreach($entitiesNameSpaces as $ENS) {
				// $this->onlyConcrete : supprime les classes abstraites et les interfaces
				$CMD = $this->getClassMetadata($ENS);
				if(is_object($CMD)) {
					$reflectionClass = $CMD->getReflectionClass();
					if(!($reflectionClass->isAbstract() || $reflectionClass->isInterface()) || $this->onlyConcrete == false) {
						$EE = $this->getClassShortName($CMD->getName());
						if(preg_match("#^(".implode('|', $groupes).")#", $ENS)) $this->listOfEnties[$ENS] = $EE;
							else $this->listOfExtendedEnties[$ENS] = $EE;
					}
				}
			}
			// DEBUG
			$this->container->get('aetools.debug')->debugNamedFile(
				'listOfEntities',
				array(
					"call" => "Fist call : define data.",
					'groupes' => $groupes,
					'listOfExtendedEnties' => $this->listOfExtendedEnties,
					'listOfEnties' => $this->listOfEnties,
					'entitiesNameSpaces' => $entitiesNameSpaces,
					'ClassMetadatas' => $this->CMD,
					)
				);
		} else {
			$this->container->get('aetools.debug')->debugNamedFile(
				'listOfEntities',
				array(
					"call" => "data allready defined.",
					'listOfExtendedEnties' => $this->listOfExtendedEnties,
					'listOfEnties' => $this->listOfEnties,
					'ClassMetadatas' => $this->CMD,
					)
				);
		}
		if($fliped == true) {
			return $extended == false ? array_flip($this->listOfEnties) : array_flip($this->listOfExtendedEnties) ;
		} else {
			return $extended == false ? $this->listOfEnties : $this->listOfExtendedEnties ;
		}
	}

	/**
	 * Get array de toutes les entités
	 * Sous la forme liste[nameSpace] = shortName
	 * ATTENTION : impossible de faire un flip, car certaines entités ont le même shortName
	 * @param boolean $force = false
	 * @return array
	 */
	public function getListOfAllEntities($force = false) {
		return array_merge(
			$this->getListOfEnties(false, $force), // site
			$this->getListOfEnties(true, $force) // extendeds/system
			);
	}

	/**
	 * initialise avec le nom de l'entité : !!! format "groupe\bundle\dossier\entite" !!!
	 * @param string $classEntite
	 */
	public function defineEntity($classEntite) {
		// $this->getListOfEnties();
		// echo('<p>'.json_encode($classEntite).'</p>');
		// récupère le nom long s'il est en version courte
		$classEntite = $this->entityClassExists($classEntite, true);
		$shortName = $this->getEntityShortName($classEntite, true);
		if($classEntite !== false) {
			// l'entité existe
			// $this->writeConsole('***** Changement d\'entité : '.$classEntite." *****", 'error');
			$this->current = $classEntite;
			$this->serviceNom = $shortName;
			if(!$this->isDefined($this->current)) {
				// l'entité n'est pas initialisée, on la crée
				$this->entity[$this->current] = array();
				$this->entity[$this->current]['className'] = $this->current;
				$this->entity[$this->current]['name'] = $shortName;
			}
			// Objet Repository
			$this->getRepo($this->current);
		} else {
			// l'entité n'existe pas
			// throw new Exception("L'entité ".json_encode($classEntite)." n'existe pas dans la liste ".json_encode($this->getListOfEnties(false)), 1);
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
		// $service = $this->getEntityService($newEntity);
		// $service->checkStatuts($newEntity, false);
		// $service->checkTva($newEntity, false);
		$this->fillAllAssociatedFields($newEntity);
		return $newEntity;
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
	 * @return aeEntity
	 */
	public function fillAllAssociatedFields(&$object, $what = null) {
		if($what === null) $what = self::REPO_DEFAULT_VAL;
		if($this->entityClassExists($object) !== false && is_object($object)) {
			foreach($this->getAssociationNamesOfEntity($object) as $field) {
				$whatFor = $what;
				if(is_array($what)) {
					if(isset($what[$field])) $whatFor = $what[$field];
				}
				$this->fillAssociatedField($field, $object, $whatFor);
			}
		} else return false;
		return $this;
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
	 * @return boolean (true si au moins une association a pu être réalisée)
	 */
	public function fillAssociatedField($field, &$object, $what = null) {
		if($what === null) $what = self::REPO_DEFAULT_VAL;
		// $this->writeTableConsole("Recherches :", $what);
		$result = false;
		if($this->entityClassExists($object) !== false) {
			// l'objet est bien une entité existante…
			if($this->hasAssociation($field, $object)) {
				// récupère la classe associée : $targetClass
				$targetClass = $this->getTargetEntity($field, $object);

				// Repository
				$tar_repo = $this->getRepo($targetClass);

				if($tar_repo != false) {
					$associates = array();
					// valeurs par défaut
					if($what === self::REPO_DEFAULT_VAL || isset($what[self::REPO_DEFAULT_VAL])) {
						$defaultMethod = self::REPO_DEFAULT_VAL;
						if(isset($what[self::REPO_DEFAULT_VAL])) if(is_string($what[self::REPO_DEFAULT_VAL])) {
							if(method_exists($tar_repo, $what[self::REPO_DEFAULT_VAL])) $defaultMethod = $what[self::REPO_DEFAULT_VAL];
						}
						$this->writeConsole(self::TAB1.'Ajout des valeurs par défaut : (->'.$defaultMethod.'())', 'normal');
						if(method_exists($tar_repo, $defaultMethod)) {
							$associates = $tar_repo->$defaultMethod();
							// $this->writeConsole($defaultMethod.'()');
							if(is_object($associates)) $associates = array($associates);
						}
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
						foreach($what as $tar_field => $tar_Entite) if(!in_array($tar_field, array(self::REPO_DEFAULT_VAL))) {
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
						// associes les entités
						$compte = 0;
						foreach($associates as $key => $value) if($value instanceOf $targetClass) {
							// $this->writeConsole("Self entity : ".gettype($object)." / Target entity : ".gettype($value), 'error');
							if($this->attachEachSides($field, $object, $value)) {
								// echo('<p> - Attachement : '.$field.' -> '.$value->getNom().'</p>');
								// l'association a eu lieu avec succès
								$compte++;
								if($this->getTypeOfAssociation($field) == self::SINGLE_ASSOC_NAME) break;
							}
						}
					}
				}
			}
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
						// Type::STRING
						// Type::TEXT
						// Type::BLOB
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
						}
					}
					// on flush…
					// $this->getEm()->flush();
				}
			}
		}
	}

	/**
	 * Attache une entité liée (ou pas)
	 * Gère les relations bidirectionnelles si elles ne sont pas gérées par les entités elles-mêmes
	 * @param string $field
	 * @param object $entity1
	 * @param object $entity2
	 * @return boolean (true = association réussie)
	 */
	public function attachEachSides($field, &$entity1, &$entity2) {
		if($this->hasAssociation($field, $entity1)) {
			$obj_SET = $this->getMethodOfSetting($field, $entity1);
			// echo('<p>setter : '.$obj_SET.'</p>');
			$obj_GET = $this->getMethodOfGetting($field, $entity1);
			if(is_string($obj_SET)) {
				// setting pour $entity1
				$entity1->$obj_SET($entity2);
				// echo('<p style="color:orange;">- Attached '.$field.' : '.$entity1->$obj_GET().'</p>');
				// $this->writeConsole('     • Association ok : '.$this->getEntityShortName($entity1).'->'.$obj_SET.'('.$entity2->getSlug().')', 'succes');
				if($this->isBidirectional($field, $entity1)) {
					// oui, bidirectionnelle
					$otherSideField = $this->get_OtherSide_sourceField($field, $entity1);
					if(is_string($otherSideField)) {
						$tar_SET = $this->getMethodOfSetting($otherSideField, $entity2);
						// setting pour $entity2
						if(is_string($tar_SET)) {
							$entity2->$tar_SET($entity1);
							// $this->writeConsole('     • Reverse Side ok : '.$this->getEntityShortName($entity2).'->'.$tar_SET.'('.$entity1->getSlug().')', 'succes');
							return true;
						} else return false;
					} else return false;
					// } else throw new Exception(self::TAB1."Données bidirectionnelles incomplètes : champ cible inconnu (\"".gettype($entity1)."::".$field."\" => \"".gettype($entity1)."::<INCONNU>\"). (".$this->getName()."::attachEachSides() / Ligne ".__LINE__.")", 1);
				}
				return true;
			} else return false;
			// } else throw new Exception(self::TAB1."Setter absent (\"".gettype($entity1)."::".$field."\"). (".$this->getName()."::attachEachSides() / Ligne ".__LINE__.")", 1);
		} else return false;
		return false;
	}


	// INFORMATIONS SUR LES CHAMPS D'ENTITÉS

	/**
	 * Renvoie si le champ existe
	 * @return boolean
	 */
	public function hasField($field, $entite = null) {
		$CMD = $this->getClassMetadata($this->getEntityClassName($entite));
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
		$CMD = $this->getClassMetadata($this->getEntityClassName($entite));
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
		$CMD = $this->getClassMetadata($this->getEntityClassName($entite));
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
	 * @param object $entite = null
	 * @param object $entite
	 * @return string
	 */
	public function getTargetEntity($field, $entite = null, $shortname = false) {
		$CMD = $this->getClassMetadata($this->getEntityClassName($entite));
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
		$CMD = $this->getClassMetadata($this->getEntityClassName($entite));
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
		$CMD = $this->getClassMetadata($this->getEntityClassName($entite));
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
		$CMD = $this->getClassMetadata($this->getEntityClassName($entite));
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
	 * Renvoie le nom du setter / false si la méthode est manquante
	 * @param string $field
	 * @param object $entite
	 * @return string / false
	 */
	public function getMethodOfSetting($field, $entite = null, $testIfExists = true) {
		$test = $testIfExists ? $entite : null;
		$class = $this->entityClassExists($entite, true);
		if($class) {
			$TOF = $this->getTypeOfField($field, $entite);
			if($TOF !== false) {
				if($TOF === Type::TARRAY) {
					// Type arrayCollection
					return $this->getMethodNameWith($field, 'add', $test);
				} else {
					return $this->getMethodNameWith($field, 'set', $test);
				}
			} else {
				switch ($this->getTypeOfAssociation($field, $entite)) {
					case self::COLLECTION_ASSOC_NAME:
						// collection
						return $this->getMethodNameWith($field, 'add', $test);
						break;
					case self::SINGLE_ASSOC_NAME:
						// single
						return $this->getMethodNameWith($field, 'set', $test);
						break;
				}
			}
		}
		return false;
	}

	/**
	 * Renvoie le nom du remover / false si la méthode est manquante
	 * @param string $field
	 * @param object $entite
	 * @return string / false
	 */
	public function getMethodOfRemoving($field, $entite = null, $testIfExists = true) {
		$test = $testIfExists ? $entite : null;
		$class = $this->entityClassExists($entite, true);
		if($class) {
			$TOF = $this->getTypeOfField($field, $entite);
			if($TOF !== false) {
				if($TOF === Type::TARRAY) {
					// Type arrayCollection
					return $this->getMethodNameWith($field, 'remove', $test);
				} else {
					return $this->getMethodNameWith($field, 'set', $test);
				}
			} else {
				switch ($this->getTypeOfAssociation($field, $entite)) {
					case self::COLLECTION_ASSOC_NAME:
						// collection
						return $this->getMethodNameWith($field, 'remove', $test);
						break;
					case self::SINGLE_ASSOC_NAME:
						// single
						return $this->getMethodNameWith($field, 'set', $test);
						break;
				}
			}
		}
		return false;
	}

	/**
	 * Renvoie le nom du getter / false si la méthode est manquante
	 * @param string $field
	 * @param object $entite
	 * @return string / false
	 */
	public function getMethodOfGetting($field, $entite = null, $testIfExists = true) {
		$test = $testIfExists ? $entite : null;
		$class = $this->entityClassExists($entite, true);
		if($class) {
			$TOF = $this->getTypeOfField($field, $entite);
			if($TOF !== false) {
				if($TOF === Type::TARRAY) {
					// Type arrayCollection
					return $this->getMethodNameWith($field, 'get', $test); // s !
				} else {
					return $this->getMethodNameWith($field, 'get', $test);
				}
			} else if($this->hasAssociation($field, $entite)) {
				switch ($this->getTypeOfAssociation($field, $entite)) {
					case self::COLLECTION_ASSOC_NAME:
						// collection
						return $this->getMethodNameWith($field, 'get', $test); // s !
						break;
					case self::SINGLE_ASSOC_NAME:
						// single
						return $this->getMethodNameWith($field, 'get', $test);
						break;
				}
			}
		}
		return false;
	}

	/**
	 * Renvoie si le champ est de type association
	 * @param string $field
	 * @param object $entite
	 * @return boolean
	 */
	public function isAssociationWithSingleJoinColumn($field, $entite = null) {
		$CMD = $this->getClassMetadata($this->getEntityClassName($entite));
		if(is_object($CMD)) {
			return $CMD->isAssociationWithSingleJoinColumn($field, $entite);
		}
		return false;
	}

	/**
	 * Renvoie si le champ est de type association
	 * @param string $field
	 * @param object $entite
	 * @return boolean
	 */
	public function hasAssociation($field, $entite = null) {
		$CMD = $this->getClassMetadata($this->getEntityClassName($entite));
		if(is_object($CMD)) {
			return $CMD->hasAssociation($field);
		}
		return false;
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
		$CMD = $this->getClassMetadata($this->getEntityClassName($entite));
		if(is_object($CMD)) {
			return $CMD->isIdentifier($field);
		}
		return false;
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
		$CMD = $this->getClassMetadata($this->getEntityClassName($entite));
		if(is_object($CMD)) {
			if(!$this->hasAssociation($field, $entite)) return false;
			$tar_entity = $this->getTargetEntity($field, $entite);
			$tar_field = $this->get_OtherSide_sourceField($field, $entite);
			return ($this->isAssociationInverseSide($field, $entite) || $this->isAssociationInverseSide($tar_field, $tar_entity));
		}
		return false;
	}

	protected function EntityCollectionsToArray($data, $onlyArrays = true) {
		if(is_null($data)) return $data;
		$result = array();
		foreach($data as $key => $value) {
			if($onlyArrays && is_object($value)) {
				if(get_class($value) == 'Doctrine\ORM\PersistentCollection') $value = $value->getValues();
					else if(get_class($value) == 'Doctrine\Common\Collections\ArrayCollection') $value = $value->toArray();
			}
			if(is_array($value)) {
				if(!isset($result[$key])) $result[$key] = array();
				$result[$key] = array_merge($result[$key], $this->EntityCollectionsToArray($value));
			} else {
				$result[$key] = $value;
			}
		}
		return $result;
	}

	/**
	 * Vérifie et associe les champs liés bidirectionnels
	 * @param object $entity
	 * @param boolean $flush = true
	 * @param baseEntity $butEntities = []
	 * @return boolean
	 */
	public function checkInversedLinks(&$entity, $flush = true, $butEntities = []) {
		$r = true;
		$classname = get_class($entity);
		if($classname && method_exists($entity, 'getOldValues')) {
			$shortname = $this->getEntityShortName($entity);
			// $fields = $this->getAssociationNamesOfEntity($classname);
			$compValues = array();
			$compValues['info'][$shortname] = $classname;
			$compValues['info']['id'] = json_encode($entity->getId());
			$compValues['old'] = $this->EntityCollectionsToArray($entity->getOldValues());
			$compValues['current'] = array();
			foreach($compValues['old'] as $field => $values) {
				$get = $this->getMethodOfGetting($field, $classname);
				if($get) $compValues['current'][$field] = $entity->$get();
			}
			$compValues['current'] = $this->EntityCollectionsToArray($compValues['current']);
			$compValues['new'] = $this->array_unique_in_array2_recursive($compValues['old'], $compValues['current']);
			$compValues['delete'] = $this->array_unique_in_array2_recursive($compValues['current'], $compValues['old']);
			// ajouts
			foreach ($compValues['new'] as $field => $entities) {
				if(is_object($entities)) $entities = array($entities);
				if(is_array($entities)) {
					foreach ($entities as $enti) if(is_object($enti) && !in_array($enti, $butEntities, true)) {
						// $inverseClassname = $this->getEntityClassName($enti);
						$inverseClassname = get_class($enti);
						$otherSideSource = $this->get_OtherSide_sourceField($field, $classname);
						if($inverseClassname && $otherSideSource) {
							$dataSet = $this->getMethodOfSetting($otherSideSource, $inverseClassname);
							// $enti = $this->getRepo($inverseClassname)->find($enti->getId());
							if(is_string($dataSet)) {
								$enti->$dataSet($entity);
								$compValues['new']['result'][$field] = "Success : data added";
								$service = $this->container->get('aetools.aeEntity')->getEntityService($enti);
								$service->checkAfterChange($enti, [$entity]);
							} else {
								$compValues['new']['result'][$field] = "Error : not found ".json_encode($dataSet)." with source ".json_encode($otherSideSource)." on class ".json_encode($inverseClassname);
							}
						}
					}
				}
			}
			// suppressions
			foreach ($compValues['delete'] as $field => $entities) {
				if(is_object($entities)) $entities = array($entities);
				if(is_array($entities)) {
					foreach ($entities as $enti) if(is_object($enti) && !in_array($enti, $butEntities, true)) {
						// $inverseClassname = $this->getEntityClassName($enti);
						$inverseClassname = get_class($enti);
						$otherSideSource = $this->get_OtherSide_sourceField($field, $classname);
						if($inverseClassname) {
							$dataRemove = $this->getMethodOfRemoving($otherSideSource, $inverseClassname);
							if(is_string($dataRemove)) {
								if(preg_match('#^remove#', $dataRemove)) {
									// $enti = $this->getRepo($inverseClassname)->find($enti->getId());
									$enti->$dataRemove($entity);
								} else {
									$enti->$dataRemove(null);
								}
								$compValues['delete']['result'][$field] = "Success : data added";
								$service = $this->container->get('aetools.aeEntity')->getEntityService($enti);
								$service->checkAfterChange($enti, [$entity]);
							} else {
								$compValues['delete']['result'][$field] = "Error : not found ".json_encode($dataRemove)." with source ".json_encode($otherSideSource)." on class ".json_encode($inverseClassname);
							}
						}
					}
				}
			}
			// flush…
			$this->container->get('aetools.debug')->debugNamedFile('inverseLinks', $compValues);
			// die('<p>End !</p>');
			if($flush == true) $r = $this->save($entity);
		} else {
			$r = false;
		}
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
		$CMD = $this->getClassMetadata($this->getEntityClassName($entite));
		if(is_object($CMD)) {
			return $CMD->isAssociationInverseSide($field);
		}
	}

	/**
	 * Renvoie le type d'association du champ / false si aucune
	 * @param string $field
	 * @param object $entite
	 * @return string / false si aucune association
	 */
	public function getTypeOfAssociation($field, $entite = null) {
		$CMD = $this->getClassMetadata($this->getEntityClassName($entite));
		if(is_object($CMD)) {
			// Champ non associatif
			if(!$this->hasAssociation($field, $entite)) return false;
			// Champ associatif : renvoie le type : "single" / "collection"
			if($CMD->isCollectionValuedAssociation($field)) return self::COLLECTION_ASSOC_NAME;
			if($CMD->isSingleValuedAssociation($field)) return self::SINGLE_ASSOC_NAME;
		}
		return false;
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
		$CMD = $this->getClassMetadata($this->getEntityClassName($entite));
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
	}


	// DEFAULT

	public function setAsDefault(&$entite, $set = null, $flush = true) {
		if(method_exists($entite, 'setDefault') && method_exists($entite, 'getDefault')) {
			if($entite->getDefault() === $set) return;
			if($set === false || ($set == null && $entite->getDefault() === true)) {
				// set false
				$entite->setDefault(false);
			} else {
				// set true => vérification
				$entite->setDefault(true);
				$dm = $entite->isDefaultMultiple();
				if($dm !== true) {
					$items = $this->getRepo($entite->getClassName())->findByDefault(true);
					if(count($items) > 0) foreach ($items as $key => $oneItem) if($entite->getId() == $oneItem->getId()) unset($items[$key]);
					if(is_int($dm)) {
						if($dm < 1) $dm = 1;
					} else $dm = 1;
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
							'text'		=> $this->trans->trans('default.nomore'),
						));
						// annule les changements sur $entite
						$this->getEm()->refresh($entite);
					}
				}
			}
			// flush
			if($flush) $this->getEm()->flush();
		}
	}


	// DELETIONS

	/**
	 * Supprime une entité sans la retirer de la base (sauf si 'deleted')
	 * 4 États : actif => inactif => deleted => et enfin, suppression de la base
	 * @param baseEntity &$entite
	 * @param statut $statut
	 */
	public function softDeleteEntity(&$entite, statut $statut = null) {
		if(method_exists($entite, 'setStatut')) {
			// si un champ statut existe : Règle :
			// actif ---> inactif
			// inactif ou expired ---> deleted (uniquement visible du SUPER ADMIN)
			if($statut == null) {
				$niveau = $entite->getStatut()->getNiveau();
				if(in_array($niveau, array('IS_AUTHENTICATED_ANONYMOUSLY'))) {
					$statut = $this->getEm()->getRepository('site\adminBundle\Entity\statut')->findInactif();
				} else if(in_array($niveau, array('ROLE_TRANSLATOR', 'ROLE_EDITOR', 'ROLE_ADMIN'))) {
					$statut = $this->getEm()->getRepository('site\adminBundle\Entity\statut')->findDeleted();
				} else {
					$statut = 'delete';
				}
			}
			if(is_array($statut)) $statut = reset($statut);
			if(is_object($statut)) {
				$entite->setStatut($statut);
				// gestion de la suppression de default…
				$this->setAsDefault($entite, false, false);
			} else {
				$this->getEm()->remove($entite);
			}
		} else {
			// sinon on la supprime
			$this->getEm()->remove($entite);
		}
		// if(method_exists($entite, 'getImage')) {
		// 	$image = $entite->getImage();
		// 	$this->softDeleteEntity($image, $statut);
		// }
		// if(method_exists($entite, 'getLogo')) {
		// 	$logo = $entite->getLogo();
		// 	$this->softDeleteEntity($logo, $statut);
		// }
		// flush
		$this->getEm()->flush();
	}

	/**
	 * Supprime toutes les entités qui ont un statut temporaire
	 * option : array $listOfId - si null, supprime tous
	 * @param string $shortName
	 * @param array $listOfId = null
	 * @return aeEntity
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
		}
		// if(method_exists($entite, 'getImage')) {
		// 	$image = $entite->getImage();
		// 	$this->softActivateEntity($image);
		// }
		// if(method_exists($entite, 'getLogo')) {
		// 	$logo = $entite->getLogo();
		// 	$this->softActivateEntity($logo);
		// }
		$this->getEm()->flush();
	}



	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// CHECKS ON ENTITIES
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Check entity after change (edit…)
	 * $butEntities permet d'éviter la récursion infinie !! Ne pas oublier !
	 * @param baseEntity &$entity
	 * @param baseEntity $butEntities = []
	 * @return aeEntity
	 */
	public function checkAfterChange(&$entity, $butEntities = []) {
		// if(method_exists($entity, 'check')) $entity->check();
		// Check statut… etc.
		// $this->checkStatuts($entity, false);
		$this->checkInversedLinks($entity, false, $butEntities);
		return $this;
	}

	// CHAMPS LIÉS NULLABLE FALSE

	// ALL
	/**
	 * Vérifie le statut et l'attribue si null
	 * @param object $entity
	 * @param string $inverse (nom de l'entité liée)
	 * @param boolean $flush = true
	 * @return object $entity
	 */
	public function checkField($entity, $inverse, $flush = true) {
		$set = $this->getMethodOfSetting($inverse, $entity, true);
		$get = $this->getMethodOfGetting($inverse, $entity, true);
		// echo('<h3>'.get_class($entity).' ('.$inverse.') : '.$set.' / '.$get.'</h3>');
		if($set && $get) {
			$repo = $this->getRepo($inverse);
			$attr = false;
			if($entity->$get() == null) {
				$attr = self::SINGLE_ASSOC_NAME;
			} else if(method_exists($entity->$get(), 'toArray')) {
				if(count($entity->$get()->toArray()) < 1) $attr = self::COLLECTION_ASSOC_NAME;
			}
			// echo('<h4>'.get_class($entity).' ('.$inverse.') : '.$set.' / '.$get.'</h4>');
			if($attr != false && method_exists($repo, self::REPO_DEFAULT_VAL)) {
				// echo('<h5>'.get_class($entity).' ('.$inverse.') : '.$set.' / '.$get.'</h5>');
				$default = $repo->{self::REPO_DEFAULT_VAL}();
				// echo('<h5>'.get_class($entity).' ('.$inverse.') : '.$set.' / '.$get.'</h5>');
				switch ($attr) {
					case self::SINGLE_ASSOC_NAME:
						if(is_array($default)) $default = reset($default);
						if(is_object($default)) $entity->$set($default);
						break;
					case self::COLLECTION_ASSOC_NAME:
						if(is_object($default)) $default = array($default);
						if(is_array($default)) {
							foreach ($default as $value) {
								$entity->$set($value);
							}
						}
						break;
				}
			}
			if($flush == true) $this->save($entity);
		}
		return $entity;
	}

	// STATUT
	/**
	 * Vérifie le statut et l'attribue si null
	 * @param object &$entity
	 * @param boolean $flush = true
	 * @return boolean
	 */
	public function checkStatuts(&$entity, $flush = true) {
		return $this->checkField($entity, 'statut', $flush);
	}

	// TVA
	public function checkTva(&$entity, $flush = true) {
		return $this->checkField($entity, 'tauxTva', $flush);
	}



	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// FLUSH/PERSIST ENTITY
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Persist en flush a baseEntity
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	// public function NOsave(baseEntity &$entity, $flush = true) {
	// 	$aeReponse = $this->container->get('aetools.aeReponse');
	// 	$response = true;
	// 	$sadmin = false;
	// 	$user = $this->container->get('security.context')->getToken()->getUser();
	// 	if(is_object($user)) if($user->getBestRole() == 'ROLE_SUPER_ADMIN') $sadmin = true;
	// 	$message = 'Entité enregistrée.';
	// 	try {
	// 		$this->_em->persist($entity);
	// 	} catch (Exception $e) {
	// 		$response = false;
	// 		if(($this->isDev() && $sadmin) === true)
	// 			$message = $e->getMessage();
	// 			else $message = 'Erreur système.';
	// 	}
	// 	if($flush === true) {
	// 		try {
	// 			$this->_em->flush();
	// 		} catch (Exception $e) {
	// 			$response = false;
	// 			if(($this->isDev() && $sadmin) === true)
	// 				$message = $e->getMessage();
	// 				else $message = 'Erreur système.';
	// 		}
	// 	}
	// 	return $aeReponse
	// 		->setResult($response)
	// 		->setMessage($message)
	// 		->setData(array('id' => $entity->getId()))
	// 		;
	// 	// return $this;
	// }

	/**
	 * Persist en flush a baseEntity / pour tests
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	public function save(baseEntity &$entity, $flush = true) {
		$response = true;
		$message = 'Entité enregistrée.';
		if($entity->getId() == null) $this->_em->persist($entity);
		if($flush === true) $this->_em->flush();
		return $this
			->container->get('aetools.aeReponse')
			->setResult($response)
			->setMessage($message)
			->setData(array('id' => $entity->getId()))
			;
	}



	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ENTITY MANAGER ET REPOSITORY
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * set EntityManager
	 * @param EntityManager $em
	 * @return aeEntity
	 */
	public function setEm($em) {
		if($em instanceOf EntityManager) $this->_em = $em;
			else throw new Exception("aeEntity::setEm() : l'objet n'est pas une instance de EntityManager !", 1);		
		return $this;
	}

	/**
	 * get EntityManager
	 * @return EntityManager
	 */
	public function getEm() {
		if(!$this->_em instanceOf EntityManager) {
			if(is_object($this->container)) $this->_em = $this->container->get('doctrine')->getManager();
		}
		return $this->_em;
	}

	/**
	 * Renvoie le Repository de l'entité courante (ou fournie)
	 * @param mixed $entity - shortname, classeName ou objet entité
	 * @param boolean $context = true
	 * @return repository / false
	 */
	public function getRepo($entity = null, $context = true) {
		if(is_object($entity)) $entity = get_class($entity);
		// classe de l'entité ou de l'entité courante si null
		$entity = $this->getEntityClassName($entity);
		if($entity) {
			try {
				$this->repo[$entity] = $this->getEm()->getRepository($entity);
			} catch (Exception $e) {
				return false;
			}
			// initialisation du repository
			if(method_exists($this->repo[$entity], "declareContext") && $context) {
				$this->repo[$entity]->declareContext($this);
			}
			return $this->repo[$entity];
		}
		return false;
	}

	/**
	 * Renvoie la ClassMetadataInfo de l'entité / ou de l'entité courante
	 * ATTENTION : on ne peut pas mettre de shortName pour $entity !!!
	 * @param mixed $entity (classname ou objet)
	 * @param boolean $extended = false
	 * @return ClassMetadata ou false
	 */
	public function getClassMetadata($entity = null, $extended = false) {
		if(is_object($entity)) $entity = get_class($entity);
		if(is_string($entity)) {
			if($this->isLongName($entity)) {
				$shortEntity = $this->getClassShortName($entity);
				if(class_exists($entity)) {
					if(!isset($this->CMD[$shortEntity])) {
						try {
							$this->CMD[$shortEntity] = $this->getEm()->getClassMetadata($entity);
						} catch (Exception $e) {
							$message = "Echec lors de l'appel de la classe classMetaData.";
							$this->container->get('aetools.debug')->debugNamedFile('getClassMetadata', array("classname" => $entity, "Error" => $message));
							// throw new Exception($message, 1);
							return false;
						}
					}
					// $this->container->get('aetools.debug')->debugNamedFile('getClassMetadata', array("classname" => $entity, "ClassMetadata" => $this->CMD[$shortEntity]));
					return $this->CMD[$shortEntity];
				} else {
					$message = "Cette classe n'existe pas.";
					$this->container->get('aetools.debug')->debugNamedFile('getClassMetadata', array("classname" => $entity, "Error" => $message));
					// throw new Exception($message, 1);
					return false;
				}
			} else {
				$message = "aeEntity::getClassMetadata() : entity ".json_encode($entity)." doit être un nom long de classe. ShortName n'est pas accepté !";
				$this->container->get('aetools.debug')->debugNamedFile('getClassMetadata', array("classname" => $entity, "Error" => $message));
				// throw new Exception($message, 1);
				return false;
			}
		} else {
			$message = "aeEntity::getClassMetadata() : entity doit être une class (nom de classe) ou un objet Entity !";
			$this->container->get('aetools.debug')->debugNamedFile('getClassMetadata', array("classname" => $entity, "Error" => $message));
			// throw new Exception($message, 1);
			return false;
		}
		return false;
	}




}