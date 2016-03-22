<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeSubEntity;

use site\adminBundle\Entity\item;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeItem');
class aeItem extends aeSubEntity {

	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
		$this->defineEntity('site\adminBundle\Entity\item');
	}

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
	 * @return aeItem
     */
    public function checkAfterChange(baseEntity &$entity) {
    	parent::checkAfterChange($entity);
		return $this;
	}

	/**
	 * Persist and flush a item
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	// public function save(baseEntity &$entity, $flush = true) {
	// 	return parent::save($entity, $flush);
	// }

}