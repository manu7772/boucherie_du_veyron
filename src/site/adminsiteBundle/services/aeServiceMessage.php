<?php
namespace site\adminsiteBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;

use Labo\Bundle\AdminBundle\services\aeServiceMessage as serviceMessage;

use site\adminsiteBundle\Entity\message;

class aeServiceMessage extends serviceMessage {

    const NAME                  = 'aeServiceMessage';        // nom du service
    const CALL_NAME             = 'aetools.aeServiceMessage'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminsiteBundle\Entity\message';
    const CLASS_SHORT_ENTITY    = 'message';

    public function __construct(ContainerInterface $container, EntityManager $EntityManager = null) {
        parent::__construct($container, $EntityManager);
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
     * @param $entity
     * @return aeServiceMessage
     */
    public function checkAfterChange(&$entity, $butEntities = []) {
        parent::checkAfterChange($entity, $butEntities);
        return $this;
    }


}