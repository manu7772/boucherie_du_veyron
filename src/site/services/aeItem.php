<?php
namespace site\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\services\aeEntities;

use site\adminBundle\Entity\item;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeItem');
class aeItem extends aeEntities {

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
	    // check image
	    $image = $entity->getImage();
	    // if($image->getInfoForPersist() == null) $entity->setImage(null);
		if($image !== null) {
	    	$infoForPersist = $image->getInfoForPersist();
	    	$this->container->get('aetools.aeImage')->checkAfterChange($image);
	    	if($infoForPersist['delete'] == true) {
	    		// Supression de l'image
	    		$entity->setImage(null);
	    	}
		}
    	parent::checkAfterChange($entity);
		return $this;
	}

	/**
	 * Persist and flush a item
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	public function save(baseEntity &$entity) {
        return parent::save($entity);
	}

}