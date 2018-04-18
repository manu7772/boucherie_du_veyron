<?php
namespace site\adminsiteBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Labo\Bundle\AdminBundle\services\aeServiceItem;

use site\adminsiteBundle\Entity\article;
use Labo\Bundle\AdminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeServiceArticle');
class aeServiceArticle extends aeServiceItem {

	const NAME                  = 'aeServiceArticle';		// nom du service
	const CALL_NAME             = 'aetools.aeServiceArticle';		// comment appeler le service depuis le controller/container
	const CLASS_ENTITY          = 'site\adminsiteBundle\Entity\article';
	const CLASS_SHORT_ENTITY    = 'article';

	public function __construct(ContainerInterface $container, EntityManager $EntityManager = null) {
	    parent::__construct($container, $EntityManager);
		$this->defineEntity(self::CLASS_ENTITY);
		return $this;
	}

	// /**
	//  * Check entity after change (edit…)
	//  * @param baseEntity $entity
	//  * @return aeServiceArticle
	//  */
	// public function checkAfterChange(&$entity, $butEntities = []) {
	// 	parent::checkAfterChange($entity, $butEntities);
	// 	$this->checkTva($entity);
	// 	return $this;
	// }

	public function getNom() {
		return self::NAME;
	}

	public function callName() {
		return self::CALL_NAME;
	}

	/**
	 * Check entity integrity in context
	 * @param article $entity
	 * @param string $context ('new', 'PostLoad', 'PrePersist', 'PostPersist', 'PreUpdate', 'PostUpdate', 'PreRemove', 'PostRemove')
	 * @param $eventArgs = null
	 * @return aeServiceArticle
	 */
	public function checkIntegrity(&$entity, $context = null, $eventArgs = null) {
		parent::checkIntegrity($entity, $context, $eventArgs);
		// if($entity instanceOf article) {
			switch(strtolower($context)) {
				case 'new':
					if(method_exists($entity, 'setDevises')) $entity->setDevises($this->container->getParameter('marketplace')['devises']);
					break;
				case 'postload':
					if(method_exists($entity, 'setDevises')) $entity->setDevises($this->container->getParameter('marketplace')['devises']);
					break;
				case 'prepersist':
					// $this->checkTva($entity);
					break;
				case 'postpersist':
					break;
				case 'preupdate':
					// $this->checkTva($entity);
					break;
				case 'postupdate':
					break;
				case 'preremove':
					break;
				case 'postremove':
					break;
				default:
					break;
			}
		// }
		return $this;
	}


	// TVA
	public function checkTva(&$entity, $flush = true) {
		return $this->checkAssociation($entity, 'tauxTva', $flush);
	}


	public function setAsVendable(&$entite, $set = null, $flush = true) {
		if($entite->getVendable() === $set) return null;
		$entite->setVendable(!($set === false || ($set == null && $entite->getVendable() === true)));
		// flush
		if($flush) $this->getEm()->flush();
		return $entite->getVendable();
	}

}

