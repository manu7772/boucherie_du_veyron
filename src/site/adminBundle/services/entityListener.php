<?php

namespace site\adminBundle\services;

use Doctrine\Common\EventSubscriber; 
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeEntity;

use \DateTime;

class entityListener implements EventSubscriber {

	protected $eventArgs;
	protected $_em;
	protected $_repo;
	protected $entityObject;
	protected $container;
	protected $entityName;
	protected $entityNameSpace;
	protected $uow;
	//
	protected $entityService;

	protected $FILESPY;

	public function __construct(ContainerInterface $container) {
		// parent::__construct();
		$this->container = $container;

		$this->FILESPY = fopen(__DIR__."/../../../../web/images/filespy.txt", "a+");
		$date = new DateTime;
		$this->SPYwrite($date->format('d-m-Y H:i:s').' ------------------------------');
	}

	public function __destruct() {
		fclose($this->FILESPY);
	}

	protected function SPYwrite($t, $noEnd = true) {
		if($noEnd === false) $EOL = ""; else $EOL = "\r\n";
		fwrite($this->FILESPY, $t.$EOL);
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
		$this->SPYwrite('postLoad : ', false);
		if($this->defineDefaultsTools($eventArgs)) $this->postLoadActions();
	}
	public function prePersist(LifecycleEventArgs $eventArgs) {
		$this->SPYwrite('prePersist : ', false);
		if($this->defineDefaultsTools($eventArgs)) $this->prePersistActions();
	}
	public function postPersist(LifecycleEventArgs $eventArgs) {
		$this->SPYwrite('postPersist : ', false);
		if($this->defineDefaultsTools($eventArgs)) $this->postPersistActions();
	}
	public function preUpdate(PreUpdateEventArgs $eventArgs) {
		$this->SPYwrite('preUpdate : ', false);
		if($this->defineDefaultsTools($eventArgs)) $this->preUpdateActions();
	}
	public function postUpdate(LifecycleEventArgs $eventArgs) {
		$this->SPYwrite('postUpdate : ', false);
		if($this->defineDefaultsTools($eventArgs)) $this->postUpdateActions();
	}
	public function preRemove(LifecycleEventArgs $eventArgs) {
		$this->SPYwrite('preRemove : ', false);
		if($this->defineDefaultsTools($eventArgs)) $this->preRemoveActions();
	}
	public function postRemove(LifecycleEventArgs $eventArgs) {
		$this->SPYwrite('postRemove : ', false);
		if($this->defineDefaultsTools($eventArgs)) $this->postRemoveActions();
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

		// service aeEntity -> avec EntityManager du Listener !!
		$this->entityService = new aeEntity($this->container, $this->_em);

		if(is_object($this->entityObject)) {
			// info MetaData sur l'entité
			// $this->info = $this->getMetaInfo($this->entityObject);
			// namespace de l'entité
			// $this->entityNameSpace = get_class($this->entityObject);
			$this->entityNameSpace = $this->entityService->getEntityClassName($this->entityObject);
			// $ex = explode("\\", $this->entityNameSpace);
			// nom de l'entité
			// $this->entityName = end($ex);
			$this->entityName = $this->entityService->getEntityShortName($this->entityObject);
			// Repository
			$this->_repo = $this->_em->getRepository($this->entityNameSpace);
			$this->uow = $this->_em->getUnitOfWork();

			$this->SPYwrite($this->entityNameSpace.' / '.$this->entityName.' / id#'.$this->entityObject->getId());
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Renvoie l'Entity Manager
	 * @return manager
	 */
	public function getEm() {
		return $this->_em;
	}

	public function getRepo($entity = null, $versionSlug = 'current') {
		return $this->_repo;
	}

	/**
	 * recomputeEntity
	 * UPDATE : recompute l'entité pour enregistrement
	 */
	protected function recomputeEntity() {
		// $this->SPYwrite('- recomputeEntity() sur '.$this->entityName);
		$this->uow->recomputeSingleEntityChangeSet(
			$this->_em->getClassMetadata($this->entityNameSpace),
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
		//
	}

	/**
	 * postPersistActions
	 * Actions sur postPersist
	 */
	public function postPersistActions() {
		//
	}

	/**
	 * preUpdateActions
	 * Actions sur preUpdate
	 * @param PreUpdateEventArgs $eventArgs
	 */
	public function preUpdateActions() {
		//
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