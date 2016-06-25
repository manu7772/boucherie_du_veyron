<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeNested;

use site\adminBundle\Entity\media;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.media');
class aeMedia extends aeNested {

	const NAME                  = 'aeMedia';        // nom du service
	const CALL_NAME             = 'aetools.aeMedia'; // comment appeler le service depuis le controller/container
	const CLASS_ENTITY          = 'site\adminBundle\Entity\media';

	public function __construct(ContainerInterface $container = null, $em = null) {
	    parent::__construct($container, $em);
	    $this->defineEntity(self::CLASS_ENTITY);
	    return $this;
	}

	/**
	 * Check entity after change (edit…)
	 * @param baseEntity $entity
	 * @return aeMedia
	 */
	public function checkAfterChange(&$entity, $butEntities = []) {
		parent::checkAfterChange($entity, $butEntities);
		return $this;
	}

	public function getNom() {
		return self::NAME;
	}

	public function callName() {
		return self::CALL_NAME;
	}

	/**
	 * Persist en flush a media
     * @dev désactivée
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	// public function save(baseEntity &$entity, $flush = true) {
	// 	return parent::save($entity, $flush);
	// }

}