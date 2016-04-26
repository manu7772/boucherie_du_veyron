<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeItem;

use site\adminBundle\Entity\article;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeArticle');
class aeArticle extends aeItem {

    const CLASS_ENTITY = 'site\adminBundle\Entity\article';

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->defineEntity(self::CLASS_ENTITY);
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeArticle
     */
    public function checkAfterChange(baseEntity &$entity, $butEntities = []) {
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

    /**
     * Persist and flush a article
     * @param baseEntity $entity
     * @return aeReponse
     */
    // public function save(baseEntity &$entity, $flush = true) {
    //  return parent::save($entity, $flush);
    // }

}