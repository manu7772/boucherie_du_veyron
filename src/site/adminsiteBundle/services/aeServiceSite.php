<?php
namespace site\adminsiteBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Labo\Bundle\AdminBundle\services\aeData;

use Labo\Bundle\AdminBundle\services\aeServiceSubentity;

use site\adminsiteBundle\Entity\site;
use Labo\Bundle\AdminBundle\Entity\baseEntity;
use Labo\Bundle\AdminBundle\services\siteListener;

// call in controller with $this->get('aetools.aeServiceSite');
class aeServiceSite extends aeServiceSubentity {

    const NAME                  = 'aeServiceSite';        // nom du service
    const CALL_NAME             = 'aetools.aeServiceSite'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminsiteBundle\Entity\site';
    const CLASS_SHORT_ENTITY    = 'site';

    protected $siteData;
    // protected $cpt;

    public function __construct(ContainerInterface $container, EntityManager $EntityManager = null) {
        parent::__construct($container, $EntityManager);
        $this->defineEntity(self::CLASS_ENTITY);
        $this->siteData = null;
        // $this->cpt = 0;
        $this->siteData = $this->getRepo()->findSiteData();
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
        $fields = array('logo', 'favicon', 'adminLogo');
        foreach ($fields as $field) {
            $get = $this->getMethodOfGetting($field, $entity);
            $set = $this->getMethodOfSetting($field, $entity);
            if(is_string($set) && is_string($get)) {
                $image = $entity->$get();
                if(is_object($image)) {
                    $infoForPersist = $image->getInfoForPersist();
                    // $this->container->get('aetools.aeDebug')->debugFile($infoForPersist);
                    if($infoForPersist['removeImage'] === true || $infoForPersist['removeImage'] === 'true') {
                        // Supression de l'image
                        $entity->$set(null);
                    } else {
                        // Gestion de l'image
                        $service = $this->container->get('aetools.aeServiceBaseEntity')->getEntityService($image);
                        $service->checkAfterChange($image);
                    }
                }
            }
        }
        parent::checkAfterChange($entity, $butEntities);
        $this->container->get('aetools.aeCache')->deleteCacheNamedFile(siteListener::SITE_DATA);
        return $this;
    }

    public function getSiteData() {
        return $this->siteData;
    }


}