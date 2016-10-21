<?php
namespace site\adminsiteBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Labo\Bundle\AdminBundle\services\aeServiceMedia;
use Doctrine\ORM\EntityManager;
// use Doctrine\ORM\Event\PreUpdateEventArgs;
// use Doctrine\ORM\Event\LifecycleEventArgs;

use site\adminsiteBundle\Entity\pdf;
use Labo\Bundle\AdminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aePdf');
class aeServicePdf extends aeServiceMedia {

	const NAME                  = 'aeServicePdf';        // nom du service
	const CALL_NAME             = 'aetools.aePdf'; // comment appeler le service depuis le controller/container
	const CLASS_ENTITY          = 'site\adminsiteBundle\Entity\pdf';

	public function __construct(ContainerInterface $container, EntityManager $EntityManager = null) {
		parent::__construct($container, $EntityManager);
		$this->defineEntity(self::CLASS_ENTITY);
		return $this;
	}

	// /**
	//  * Check entity after change (edit…)
	//  * @param baseEntity $entity
	//  * @return aeServicePdf
	//  */
	// public function checkAfterChange(&$entity, $butEntities = []) {
	// 	parent::checkAfterChange($entity, $butEntities);
	// 	return $this;
	// }

	// /**
	//  * Check entity integrity in context
	//  * @param pdf $entity
	//  * @param string $context ('new', 'PostLoad', 'PrePersist', 'PostPersist', 'PreUpdate', 'PostUpdate', 'PreRemove', 'PostRemove')
	//  * @param $eventArgs = null
	//  * @return aeServicePdf
	//  */
	// public function checkIntegrity(&$entity, $context = null, $eventArgs = null) {
	//     parent::checkIntegrity($entity, $context, $eventArgs);
	//     // if($entity instanceOf pdf) {
	//         switch(strtolower($context)) {
	//             case 'new':
	//                 break;
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

	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// IMAGE MANAGER
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	// public function managePdf(&$entity) {
	// 	//
	// 	// return $this;
	// }


}