<?php
namespace site\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\services\aeEntity;

use site\adminBundle\Entity\item;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeSubEntity');
class aeSubEntity extends aeEntity {

	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
		$this->defineEntity('site\adminBundle\Entity\item');
	}

	/**
	 * Check entity after change (edit…)
	 * @param baseEntity $entity
	 * @return aeSubEntity
	 */
	public function checkAfterChange(baseEntity &$entity) {
		// check image
		$image = $entity->getImage();
		if(is_object($image)) {
			// if($image->isValid()) {
				$infoForPersist = $image->getInfoForPersist();
				$this->container->get('aetools.debug')->debugFile($infoForPersist);
				$service = $this->container->get('aetools.aeEntity')->getEntityService($image);
				$service->checkAfterChange($image);
				if($infoForPersist['removeImage'] === true || $infoForPersist['removeImage'] === 'true') {
					// Supression de l'image
					$entity->setImage(null);
				}
			// } else $entity->setImage(null);
		}
		parent::checkAfterChange($entity);
		return $this;
	}

	/**
	 * Persist and flush a item
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	// public function save(baseEntity &$entity) {
 //        return parent::save($entity);
	// }

}