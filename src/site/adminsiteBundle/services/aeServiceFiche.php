<?php
namespace site\adminsiteBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;

use Labo\Bundle\AdminBundle\services\aeServiceItem;

use site\adminsiteBundle\Entity\fiche;
use Labo\Bundle\AdminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeServiceFiche');
class aeServiceFiche extends aeServiceItem {

    const NAME                  = 'aeServiceFiche';        // nom du service
    const CALL_NAME             = 'aetools.aeServiceFiche'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminsiteBundle\Entity\fiche';
    const CLASS_SHORT_ENTITY    = 'fiche';

    public function __construct(ContainerInterface $container, EntityManager $EntityManager = null) {
        parent::__construct($container, $EntityManager);
        $this->defineEntity(self::CLASS_ENTITY);
        return $this;
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeServiceFiche
     */
    public function checkAfterChange(&$entity, $butEntities = []) {
        parent::checkAfterChange($entity, $butEntities);
        return $this;
    }

    public function getNom() {
        return self::NAME;
    }

    public function callName() {
        return self::CALL_NAME;
    }


}