<?php
namespace site\UserBundle\services;

use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

use Labo\Bundle\AdminBundle\services\aeServiceLaboUser ;

class aeServiceUser extends aeServiceLaboUser {


	public function __construct(UserManager $UserManager, EncoderFactory $EncoderFactory) {
		parent::__construct($UserManager, $EncoderFactory);
		return $this;
	}

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

}


