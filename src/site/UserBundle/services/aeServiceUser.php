<?php
namespace site\UserBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Labo\Bundle\AdminBundle\services\aeServiceBaseEntity;
use Labo\Bundle\AdminBundle\services\aeReponse;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

use Labo\Bundle\AdminBundle\services\aeData;

use Labo\Bundle\AdminBundle\services\aeServiceLaboUser;
use Labo\Bundle\AdminBundle\Entity\messageuser;
use site\UserBundle\Entity\user;
use Labo\Bundle\AdminBundle\Entity\message;
use Labo\Bundle\AdminBundle\Entity\LaboUser;

use \DateTime;

class aeServiceUser extends aeServiceLaboUser {

	const NAME                  = 'aeServiceUser';					// nom du service
	const CALL_NAME             = 'aetools.aeServiceUser';			// comment appeler le service depuis le controller/container
	const CLASS_ENTITY          = 'site\UserBundle\Entity\user';


	protected function getCreateUsers() {
		$users = parent::getCreateUsers();
		if(is_array($users))
			$users = array_merge($users, array(
				array(
					'username' => 'aymeric',
					self::PASSWORD => 'aymeric',
					'nom' => 'Marion',
					'prenom' => 'Aymeric',
					'email' => 'laboucherieduveyron@orange.fr',
					'telephone' => '06 87 86 41 78',
					'role' => 'ROLE_ADMIN',
					'enabled' => true,
					),
				)
			);
		return $users;
	}

	/**
	 * Check entity integrity in context
	 * @param baseEntity $entity
	 * @param string $context ('new', 'PostLoad', 'PrePersist', 'PostPersist', 'PreUpdate', 'PostUpdate', 'PreRemove', 'PostRemove', 'PreFlush')
	 * @param LifecycleEventArgs $eventArgs = null
	 * @return aeServiceLaboUser
	 */
	public function checkIntegrity(&$entity, $context = null, $eventArgs = null) {
		parent::checkIntegrity($entity, $context, $eventArgs);
		// if($entity instanceOf user) {
			switch(strtolower($context)) {
				case 'new':
					break;
				case 'postload':
					break;
				case 'prepersist':
					break;
				case 'postpersist':
					break;
				case 'preupdate':
					break;
				case 'postupdate':
					break;
				case 'preremove':
					break;
				case 'postremove':
					break;
				case 'preflush':
					break;
				default:
					break;
			}
		// }
		return $this;
	}


}


