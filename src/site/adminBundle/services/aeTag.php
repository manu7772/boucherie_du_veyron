<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeEntity;

use site\adminBundle\Entity\tag;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeTag');
class aeTag extends aeEntity {

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->defineEntity('site\adminBundle\Entity\tag');
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeTag
     */
    public function checkAfterChange(baseEntity &$entity, $butEntities = []) {
        parent::checkAfterChange($entity, $butEntities);
        return $this;
    }

    /**
     * Persist and flush a tag
     * @param baseEntity $entity
     * @return aeReponse
     */
    // public function save(baseEntity &$entity, $flush = true) {
    //  return parent::save($entity, $flush);
    // }

}