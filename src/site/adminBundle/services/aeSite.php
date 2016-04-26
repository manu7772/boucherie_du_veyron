<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeEntity;

use site\adminBundle\Entity\site;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeSite');
class aeSite extends aeEntity {

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->defineEntity('site\adminBundle\Entity\site');
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeSite
     */
    public function checkAfterChange(baseEntity &$entity, $butEntities = []) {
        // check images
        $fields = array('image', 'logo', 'favicon', 'adminLogo');
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
     * Persist and flush a categorie
     * @param baseEntity $entity
     * @return aeReponse
     */
    // public function save(baseEntity &$entity, $flush = true) {
    //  return parent::save($entity, $flush);
    // }

    public function getDefaultSiteData() {
        $default = $this->getRepo()->findByDefault(true);
        if(count($default) > 0) return reset($default);
        else return array();
    }

}