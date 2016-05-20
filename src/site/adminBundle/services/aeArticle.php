<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeItem;

use site\adminBundle\Entity\article;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeArticle');
class aeArticle extends aeItem {

    const NAME                  = 'aeArticle';        // nom du service
    const CALL_NAME             = 'aetools.aeArticle'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminBundle\Entity\article';

    public function __construct(ContainerInterface $container = null, $em = null) {
        parent::__construct($container, $em);
        $this->defineEntity(self::CLASS_ENTITY);
        return $this;
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeArticle
     */
    public function checkAfterChange(&$entity, $butEntities = []) {
        // fiche inverse
        // foreach($entity->getFiches() as $fiche) {
        //     $fiche->addArticle($entity);
        //     $service = $this->container->get('aetools.aeEntity')->getEntityService($fiche);
        //     $service->checkAfterChange($fiche);
        //     $service->save($fiche, false);
        // }
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
     * Persist and flush a article
     * @param baseEntity $entity
     * @return aeReponse
     */
    // public function save(baseEntity &$entity, $flush = true) {
    //  return parent::save($entity, $flush);
    // }

}