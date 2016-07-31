<?php
namespace site\adminsiteBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Labo\Bundle\AdminBundle\services\aeServiceSubentity;

use site\adminsiteBundle\Entity\site;
use Labo\Bundle\AdminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeSite');
class aeServiceSite extends aeServiceSubentity {

    const NAME                  = 'aeServiceSite';        // nom du service
    const CALL_NAME             = 'aetools.aeSite'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminsiteBundle\Entity\site';
    const CLASS_SHORT_ENTITY    = 'site';

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
     * @return aeServiceSite
     */
    public function checkAfterChange(&$entity, $butEntities = []) {
        // check images
        $fields = array('image', 'logo', 'favicon', 'adminLogo');
        foreach ($fields as $field) {
            $get = $this->getMethodOfSetting($field, $entity, true);
            $set = $this->getMethodOfGetting($field, $entity, true);
            $image = $entity->$get();
            if($image instanceOf site\adminsiteBundle\Entity\image) {
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

    public function getSiteData($siteDataId = null) {
        $repo = $this->getRepo();
        return is_object($repo) ? $repo->findSiteData($siteDataId) : array();
    }

}