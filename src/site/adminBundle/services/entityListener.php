<?php

namespace site\adminBundle\services;

use Doctrine\Common\EventSubscriber; 
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

use site\adminBundle\services\aeEntity;
use site\adminBundle\services\aetools;

use \DateTime;
use \ReflectionClass;

class entityListener implements EventSubscriber {

    const NAME                  = 'entityListener';        // nom du service
    const CALL_NAME             = 'aetools.entityUtils'; // comment appeler le service depuis le controller/container

	const SLASH					= '/';				// slash
	const ASLASH 				= '\\';				// anti-slashes

	protected $eventArgs;
	protected $_em;
	protected $_repo;
	protected $entityObject;
	protected $container;
	protected $entityName;
	protected $entityNameSpace;
	protected $uow;
	protected $debug;
	//
	protected $entityService;

	protected $FILESPY;

	public function __construct(ContainerInterface $container) {
		// parent::__construct();
		$this->container = $container;
		$this->debug = array();
		$this->debug['action'] = 'constructeur';
	}

	public function __destruct() {
	}

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

	public function getSubscribedEvents() {
		return array(
			'postLoad',
			'prePersist',
			'postPersist',
			'preUpdate',
			'postUpdate',
			'preRemove',
			'postRemove'
		);
	}
	public function postLoad(LifecycleEventArgs $eventArgs) {
		// $this->debug['action'] = 'postLoad';
		if($this->defineDefaultsTools($eventArgs)) $this->postLoadActions();
		// $this->container->get('aetools.debug')->debugNamedFile('EntityListener', $this->debug);
	}
	public function prePersist(LifecycleEventArgs $eventArgs) {
		// $this->debug['action'] = 'prePersist';
		if($this->defineDefaultsTools($eventArgs)) $this->prePersistActions();
		// $this->container->get('aetools.debug')->debugNamedFile('EntityListener', $this->debug);
	}
	public function postPersist(LifecycleEventArgs $eventArgs) {
		// $this->debug['action'] = 'postPersist';
		if($this->defineDefaultsTools($eventArgs)) $this->postPersistActions();
		// $this->container->get('aetools.debug')->debugNamedFile('EntityListener', $this->debug);
	}
	public function preUpdate(PreUpdateEventArgs $eventArgs) {
		// $this->debug['action'] = 'preUpdate';
		if($this->defineDefaultsTools($eventArgs)) $this->preUpdateActions();
		// $this->container->get('aetools.debug')->debugNamedFile('EntityListener', $this->debug);
	}
	public function postUpdate(LifecycleEventArgs $eventArgs) {
		// $this->debug['action'] = 'postUpdate';
		if($this->defineDefaultsTools($eventArgs)) $this->postUpdateActions();
		// $this->container->get('aetools.debug')->debugNamedFile('EntityListener', $this->debug);
	}
	public function preRemove(LifecycleEventArgs $eventArgs) {
		// $this->debug['action'] = 'preRemove';
		if($this->defineDefaultsTools($eventArgs)) $this->preRemoveActions();
		// $this->container->get('aetools.debug')->debugNamedFile('EntityListener', $this->debug);
	}
	public function postRemove(LifecycleEventArgs $eventArgs) {
		// $this->debug['action'] = 'postRemove';
		if($this->defineDefaultsTools($eventArgs)) $this->postRemoveActions();
		// $this->container->get('aetools.debug')->debugNamedFile('EntityListener', $this->debug);
	}

	/**
	 * defineDefaultsTools
	 * Initialise EntityManager et Repository
	 * @param LifecycleEventArgs $eventArgs
	 */
	public function defineDefaultsTools(LifecycleEventArgs $eventArgs) {
		$this->eventArgs = $eventArgs;
		$this->_em = $this->eventArgs->getEntityManager();
		$this->entityObject = $this->eventArgs->getEntity();
		// $this->debug['Entity']['get_class'] = get_class($this->entityObject);
		// service aeEntity -> avec EntityManager du Listener !!
		$this->entityService = new aeEntity($this->container, $this->_em);

		if(is_object($this->entityObject)) {
			// namespace de l'entité
			$this->entityNameSpace = $this->entityService->getEntityClassName($this->entityObject);
			if($this->entityNameSpace == false) return false;
			// nom de l'entité
			$this->entityName = $this->entityService->getEntityShortName($this->entityObject);
			// Repository
			$this->_repo = $this->_em->getRepository($this->entityNameSpace);
			$this->uow = $this->_em->getUnitOfWork();
			// $this->debug['Entity']['className'] = $this->entityNameSpace;
			// $this->debug['Entity']['shortName'] = $this->entityName;
			// $this->debug['Entity']['id'] = $this->entityObject->getId();
			return true;
		}
		return false;
	}


	/**
	 * Renvoie l'Entity Manager
	 * @return manager
	 */
	public function getEm() {
		return $this->_em;
	}

	public function getRepo($entity) {
		return $this->_repo;
	}

	/**
	 * recomputeEntity
	 * UPDATE : recompute l'entité pour enregistrement
	 */
	protected function recomputeEntity() {
		$this->uow->recomputeSingleEntityChangeSet(
			$this->_em->getClassMetadata(get_class($this->entityObject)),
			$this->entityObject
		);
	}

	/**
	 * postLoadActions
	 * Actions sur postLoad
	 */
	public function postLoadActions() {
	}

	/**
	 * prePersistActions
	 * Actions sur prePersist
	 */
	public function prePersistActions() {
		if(method_exists($this->entityObject, 'getStatut')) {
			if($this->entityObject->getId() == null && $this->entityObject->getStatut() == null) {
				$this->entityService->checkStatuts($this->entityObject, false);
				$this->_em->persist($this->entityObject);
			}
		}
		// $this->entityService->checkTva($this->entityObject, false);
	}

	/**
	 * postPersistActions
	 * Actions sur postPersist
	 */
	public function postPersistActions() {
		// $this->debug['Entity']['id'] = $this->entityObject->getId();
	}

	/**
	 * preUpdateActions
	 * Actions sur preUpdate
	 * @param PreUpdateEventArgs $eventArgs
	 */
	public function preUpdateActions() {
		//
		// $this->entityService->checkInversedLinks($this->entityObject, false);
		// $this->entityService->checkStatuts($this->entityObject, false);
		// $this->entityService->checkTva($this->entityObject, false);
		//////////////////////////// IMPORTANT ///////////////////////////////
		// Recompute suite modifs entity (nécessaire dans le cas d'Update) !!!
		$this->recomputeEntity();
	}

	/**
	 * postUpdateActions
	 * Actions sur postUpdate
	 * @param LifecycleEventArgs $eventArgs
	 */
	public function postUpdateActions() {
		//
	}

	/**
	 * preRemoveActions
	 * Actions sur preRemove
	 * @param LifecycleEventArgs $eventArgs
	 */
	public function preRemoveActions() {
		//
	}

	/**
	 * postRemoveActions
	 * Actions sur postRemove
	 * @param LifecycleEventArgs $eventArgs
	 */
	public function postRemoveActions() {
		//
	}









}