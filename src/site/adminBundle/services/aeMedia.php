<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeEntity;

use site\adminBundle\Entity\media;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.media');
class aeMedia extends aeEntity {

	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
		$this->defineEntity('site\adminBundle\Entity\media');
	}

	/**
	 * Check entity after change (edit…)
	 * @param baseEntity $entity
	 * @return aeMedia
	 */
	public function checkAfterChange(baseEntity &$entity, $butEntities = []) {
		parent::checkAfterChange($entity, $butEntities);
		return $this;
	}

	/**
	 * Persist en flush a media
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	// public function save(baseEntity &$entity, $flush = true) {
	// 	return parent::save($entity, $flush);
	// }

}