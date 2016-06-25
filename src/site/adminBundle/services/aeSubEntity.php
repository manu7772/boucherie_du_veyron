<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeEntity;

use site\adminBundle\Entity\baseEntity;
use site\adminBundle\Entity\subentityposition;

// call in controller with $this->get('aetools.aeSubentity');
class aeSubentity extends aeEntity {

	const NAME                  = 'aeSubentity';        // nom du service
	const CALL_NAME             = 'aetools.aeSubentity'; // comment appeler le service depuis le controller/container
	const CLASS_ENTITY          = 'site\adminBundle\Entity\subentity';
	const CLASS_SHORT_ENTITY    = 'subentity';

	public function __construct(ContainerInterface $container = null, $em = null) {
	    parent::__construct($container, $em);
	    $this->defineEntity(self::CLASS_ENTITY);
	    return $this;
	}

	/**
	 * Check entity after change (edit…)
	 * @param baseEntity $entity
	 * @return aeSubentity
	 */
	public function checkAfterChange(&$entity, $butEntities = []) {
        // check images
        $fields = array('image');
        foreach ($fields as $field) {
            $get = $this->getMethodOfGetting($field, $entity);
            $set = $this->getMethodOfSetting($field, $entity);
            if(is_string($set) && is_string($get)) {
	            $image = $entity->$get();
	            if(is_object($image)) {
	                $infoForPersist = $image->getInfoForPersist();
	                // $this->container->get('aetools.debug')->debugFile($infoForPersist);
	                if($infoForPersist['removeImage'] === true || $infoForPersist['removeImage'] === 'true') {
	                    // Supression de l'image
	                    $entity->$set(null);
	                } else {
	                    // Gestion de l'image
	                    $service = $this->container->get('aetools.aeEntity')->getEntityService($image);
	                    $service->checkAfterChange($image);
	                }
	            }
            }
        }
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
	 * Persist and flush a item
     * @dev désactivée
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	// public function save(baseEntity &$entity, $flush = true) {
	// 	return parent::save($entity, $flush);
	// }

}