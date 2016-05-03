<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeSubEntity;

use site\adminBundle\Entity\tier;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeTier');
class aeTier extends aeSubEntity {

    const NAME                  = 'aeTier';        // nom du service
    const CALL_NAME             = 'aetools.aeTier'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminBundle\Entity\tier';

    public function __construct(ContainerInterface $container = null, $em = null) {
        parent::__construct($container, $em);
        $this->defineEntity(self::CLASS_ENTITY);
        return $this;
    }

    public function getNom() {
        return self::NAME;
    }

    public function callName() {
        return self::CALL_NAME;
    }

	/**
	 * Check entity after change (edit…)
	 * @param baseEntity $entity
	 * @return aeTier
	 */
	public function checkAfterChange(baseEntity &$entity, $butEntities = []) {
        // check images
        $fields = array('logo');
        foreach ($fields as $field) {
            $get = 'get'.ucfirst($field);
            $set = 'set'.ucfirst($field);
            $image = $entity->$get();
            if(is_object($image)) {
                $infoForPersist = $image->getInfoForPersist();
                $this->container->get('aetools.debug')->debugFile($infoForPersist);
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
		parent::checkAfterChange($entity, $butEntities);
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