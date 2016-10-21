<?php
namespace site\adminsiteBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
// use Doctrine\ORM\Event\PreUpdateEventArgs;
// use Doctrine\ORM\Event\LifecycleEventArgs;

use Labo\Bundle\AdminBundle\services\aeServiceMessage as serviceMessage;

use site\adminsiteBundle\Entity\message;

class aeServiceMessage extends serviceMessage {

    const NAME                  = 'aeServiceMessage';        // nom du service
    const CALL_NAME             = 'aetools.aeServiceMessage'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminsiteBundle\Entity\message';

    public function __construct(ContainerInterface $container, EntityManager $EntityManager = null) {
        parent::__construct($container, $EntityManager);
        $this->defineEntity(self::CLASS_ENTITY);
        return $this;
    }


    // /**
    //  * Check entity integrity in context
    //  * @param message $entity
    //  * @param string $context ('new', 'PostLoad', 'PrePersist', 'PostPersist', 'PreUpdate', 'PostUpdate', 'PreRemove', 'PostRemove')
    //  * @param $eventArgs = null
    //  * @return aeServiceMessage
    //  */
    // public function checkIntegrity(&$entity, $context = null, $eventArgs = null) {
    //     parent::checkIntegrity($entity, $context, $eventArgs);
    //     // if($entity instanceOf message) {
    //         switch(strtolower($context)) {
    //             case 'new':
    //             case 'postload':
    //                 break;
    //             case 'prepersist':
    //                 break;
    //             case 'postpersist':
    //                 break;
    //             case 'preupdate':
    //                 break;
    //             case 'postupdate':
    //                 break;
    //             case 'preremove':
    //                 break;
    //             case 'postremove':
    //                 break;
    //             default:
    //                 break;
    //         }
    //     // }
    //     return $this;
    // }

}