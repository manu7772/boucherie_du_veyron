<?php
namespace site\adminsiteBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Labo\Bundle\AdminBundle\services\aeData;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Collections\ArrayCollection;

use Labo\Bundle\AdminBundle\services\aeServiceSubentity;

use site\adminsiteBundle\Entity\site;
use Labo\Bundle\AdminBundle\Entity\baseEntity;

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


    /**
     * Check entity integrity in context
     * @param article $entity
     * @param string $context ('new', 'PostLoad', 'PrePersist', 'PostPersist', 'PreUpdate', 'PostUpdate', 'PreRemove', 'PostRemove')
     * @param $eventArgs = null
     * @return aeServiceArticle
     */
    public function checkIntegrity(&$entity, $context = null, $eventArgs = null) {
        parent::checkIntegrity($entity, $context, $eventArgs);
        // if($entity instanceOf article) {
            switch(strtolower($context)) {
                case 'new':
                    break;
                case 'postload':
                    break;
                case 'prepersist':
                    break;
                case 'postpersist':
                    if($entity->getDefault()) $this->container->get('aetools.aeCache')->deleteCacheNamedFile(aeData::SITE_DATA);
                    break;
                case 'preupdate':
                    break;
                case 'postupdate':
                    if($entity->getDefault()) $this->container->get('aetools.aeCache')->deleteCacheNamedFile(aeData::SITE_DATA);
                    break;
                case 'preremove':
                    break;
                case 'postremove':
                    if($entity->getDefault()) $this->container->get('aetools.aeCache')->deleteCacheNamedFile(aeData::SITE_DATA);
                    break;
                default:
                    break;
            }
        // }
        return $this;
    }

    // /**
    //  * Check entity after change (edit…)
    //  * @param baseEntity $entity
    //  * @return aeServiceSite
    //  */
    // public function checkAfterChange(&$entity, $butEntities = []) {
    //     // check images
    //     $fields = array('logo', 'favicon', 'adminLogo');
    //     foreach ($fields as $field) {
    //         $get = $this->getMethodOfGetting($field, $entity);
    //         $set = $this->getMethodOfSetting($field, $entity);
    //         if(is_string($set) && is_string($get)) {
    //             $image = $entity->$get();
    //             if(is_object($image)) {
    //                 $infoForPersist = $image->getInfoForPersist();
    //                 // $this->container->get('aetools.aeDebug')->debugFile($infoForPersist);
    //                 if($infoForPersist['removeImage'] === true || $infoForPersist['removeImage'] === 'true') {
    //                     // Supression de l'image
    //                     $entity->$set(null);
    //                 } else {
    //                     // Gestion de l'image
    //                     $service = $this->container->get('aetools.aeServiceBaseEntity')->getEntityService($image);
    //                     $service->checkAfterChange($image);
    //                 }
    //             }
    //         }
    //     }
    //     parent::checkAfterChange($entity, $butEntities);
    //     $this->container->get('aetools.aeCache')->deleteCacheNamedFile(aeData::SITE_DATA);
    //     return $this;
    // }

    public function getSiteData() {
        return $this->siteData;
    }

    public function getAllCollaborateurs($siteId = null) {
        $siteId = null;
        $collaborateurs = new ArrayCollection();
        $sites = $this->getRepo()->findAll();
        foreach ($sites as $site) if($siteId == null || $site->getId() == $siteId) {
            $collsite = $site->getCollaborateurs();
            foreach ($collsite as $coll) if($coll->isEnabled() && !$collaborateurs->contains($coll)) $collaborateurs->add($coll);
        }
        return $collaborateurs;
    }

}