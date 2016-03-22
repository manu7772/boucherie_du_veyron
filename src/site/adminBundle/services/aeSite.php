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
    public function checkAfterChange(baseEntity &$entity) {
        // check image
        $image = $entity->getImage();
        if(is_object($image)) {
            // if($image->isValid()) {
                $infoForPersist = $image->getInfoForPersist();
                // $infoForPersist['type_image'] = 'image';
                $this->container->get('aetools.debug')->debugFile($infoForPersist);
                $service = $this->container->get('aetools.aeEntity')->getEntityService($image);
                $service->checkAfterChange($image);
                if($infoForPersist['removeImage'] === true || $infoForPersist['removeImage'] === 'true') {
                    // Supression de l'image
                    // echo('<p>aeSite - image : suppression !!</p>');
                    $entity->setImage(null);
                }
            // } else $entity->setImage(null);
        }
        // else echo('<p>aeSite - image : aucune</p>');
        // check logo
        $logo = $entity->getLogo();
        if(is_object($logo)) {
            // if($logo->isValid()) {
                $infoForPersist = $logo->getInfoForPersist();
                // $infoForPersist['type_image'] = 'logo';
                $this->container->get('aetools.debug')->debugFile($infoForPersist);
                $service = $this->container->get('aetools.aeEntity')->getEntityService($logo);
                $service->checkAfterChange($logo);
                if($infoForPersist['removeImage'] === true || $infoForPersist['removeImage'] === 'true') {
                    // Supression de l'image
                    // echo('<p>aeSite - logo : suppression !!</p>');
                    $entity->setLogo(null);
                }
            // } else $entity->setLogo(null);
        }
        // else echo('<p>aeSite - logo : aucun</p>');
        // check favicon
        $favicon = $entity->getFavicon();
        if(is_object($favicon)) {
            // if($favicon->isValid()) {
                $infoForPersist = $favicon->getInfoForPersist();
                // $infoForPersist['type_image'] = 'favicon';
                $this->container->get('aetools.debug')->debugFile($infoForPersist);
                $service = $this->container->get('aetools.aeEntity')->getEntityService($favicon);
                $service->checkAfterChange($favicon);
                if($infoForPersist['removeImage'] === true || $infoForPersist['removeImage'] === 'true') {
                    // Supression de l'image
                    // echo('<p>aeSite - favicon : suppression !!</p>');
                    $entity->setFavicon(null);
                }
            // } else $entity->setFavicon(null);
        }
        // else echo('<p>aeSite - favicon : aucun</p>');
        parent::checkAfterChange($entity);
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

}