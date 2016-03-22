<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeSubEntity;

use site\adminBundle\Entity\tier;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeTier');
class aeTier extends aeSubEntity {

	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
		$this->defineEntity('site\adminBundle\Entity\tier');
	}

	/**
	 * Check entity after change (edit…)
	 * @param baseEntity $entity
	 * @return aeTier
	 */
	public function checkAfterChange(baseEntity &$entity) {
		// check logo
		$logo = $entity->getLogo();
		if(is_object($logo)) {
			// if($logo->isValid()) {
				$infoForPersist = $logo->getInfoForPersist();
				$this->container->get('aetools.debug')->debugFile($infoForPersist);
				$service = $this->container->get('aetools.aeEntity')->getEntityService($logo);
				$service->checkAfterChange($logo);
				if($infoForPersist['removeImage'] === true || $infoForPersist['removeImage'] === 'true') {
					// Supression du logo
					$entity->setLogo(null);
				}
			// } else $entity->setLogo(null);
		}
		parent::checkAfterChange($entity);
		return $this;
	}

	/**
	 * Persist and flush a tier
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	// public function save(baseEntity &$entity, $flush = true) {
	// 	return parent::save($entity, $flush);
	// }

}