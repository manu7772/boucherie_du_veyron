<?php
namespace site\adminsiteBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Labo\Bundle\AdminBundle\services\aeServiceItem;

use site\adminsiteBundle\Entity\article;
use Labo\Bundle\AdminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeArticle');
class aeServiceArticle extends aeServiceItem {

	const NAME                  = 'aeServiceArticle';		// nom du service
	const CALL_NAME             = 'aetools.aeArticle';		// comment appeler le service depuis le controller/container
	const CLASS_ENTITY          = 'site\adminsiteBundle\Entity\article';
	const CLASS_SHORT_ENTITY    = 'article';

	public function __construct(ContainerInterface $container = null, $em = null) {
		parent::__construct($container, $em);
		$this->defineEntity(self::CLASS_ENTITY);
		return $this;
	}

	/**
	 * Check entity after change (edit…)
	 * @param baseEntity $entity
	 * @return aeServiceArticle
	 */
	public function checkAfterChange(&$entity, $butEntities = []) {
		parent::checkAfterChange($entity, $butEntities);
		$this->checkTva($entity);
		return $this;
	}

	public function getNom() {
		return self::NAME;
	}

	public function callName() {
		return self::CALL_NAME;
	}

	// TVA
	public function checkTva(&$entity, $flush = true) {
		return $this->checkField($entity, 'tauxTva', $flush);
	}


	public function setAsVendable(&$entite, $set = null, $flush = true) {
		if(method_exists($entite, 'setVendable') && method_exists($entite, 'getVendable')) {
			if($entite->getVendable() === $set) return null;
			if($set === false || ($set == null && $entite->getVendable() === true)) {
				// set false
				$entite->setVendable(false);
			} else {
				$entite->setVendable(true);
			}
			// flush
			if($flush) $this->getEm()->flush();
		}
		return $entite->getVendable();
	}


}

