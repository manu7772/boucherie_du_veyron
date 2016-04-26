<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeItem;

use site\adminBundle\Entity\fiche;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeFiche');
class aeFiche extends aeItem {

    const CLASS_ENTITY = 'site\adminBundle\Entity\fiche';

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->defineEntity(self::CLASS_ENTITY);
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeFiche
     */
    public function checkAfterChange(baseEntity &$entity, $butEntities = []) {
        parent::checkAfterChange($entity, $butEntities);
        return $this;
    }

    /**
     * Persist and flush a fiche
     * @param baseEntity $entity
     * @return aeReponse
     */
    // public function save(baseEntity &$entity, $flush = true) {
    //  return parent::save($entity, $flush);
    // }

}