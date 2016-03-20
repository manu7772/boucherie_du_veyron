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
    public function checkAfterChange(baseEntity &$entity) {
        parent::checkAfterChange($entity);
        return $this;
    }

    /**
     * Persist and flush a article
     * @param baseEntity $entity
     * @return aeReponse
     */
    // public function save(baseEntity &$entity) {
    //     return parent::save($entity);
    // }

}