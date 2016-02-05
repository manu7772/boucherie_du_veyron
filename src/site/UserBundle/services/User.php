<?php
namespace site\UserBundle\services;

use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class User {

	const PASSWORD = 'password';
	protected $UserManager;           // UserManager
	protected $EncoderFactory;        // EncoderFactory

	public function __construct(UserManager $UserManager, EncoderFactory $EncoderFactory) {
		$this->UserManager = $UserManager;
		$this->EncoderFactory = $EncoderFactory;
	}

	public function usersExist($createIfNobody = false) {
		$users = $this->UserManager->findUsers();
		$u = true;
		if($createIfNobody === true && count($users) < 1) {
			// crée si aucun utilisateur
			$u = $this->createUsers(true);
		}
		return count($u) > 0 ? $u : false ;
	}

	/**
	 * Efface tous les utilisateurs
	 * @return integer
	 */
	public function deleteAllUsers() {
		$users = $this->UserManager->findUsers();
		$n = count($users);
		if($n > 0) {
			foreach($users as $key => $user) {
				$this->UserManager->deleteUser($user);
			}
		}
		return $n;
	}

	/**
	 * Hydrate avec les utilisateurs de base
	 * @param boolean $deleteAll
	 * @return array
	 */
	public function createUsers($deleteAll = false) {
		if($deleteAll === true) {
			// efface tous les utilisateur existants
			$this->deleteAllUsers();
		}
		$users = array(
			0 => array(
				'username' => 'sadmin',
				self::PASSWORD => 'sadmin',
				'nom' => 'Dujardin',
				'prenom' => 'Emmanuel',
				'email' => 'manu7772@gmail.com',
				'telephone' => '06 13 14 35 15',
				'role' => 'ROLE_SUPER_ADMIN',
				'enabled' => true,
				),
			1 => array(
				'username' => 'manu7772',
				self::PASSWORD => 'azetyu123',
				'nom' => 'Dujardin',
				'prenom' => 'Emmanuel',
				'email' => 'emmanuel@aequation-webdesign.fr',
				'telephone' => '06 13 14 35 15',
				'role' => 'ROLE_ADMIN',
				'enabled' => true,
				),
			2 => array(
				'username' => 'aymeric',
				self::PASSWORD => 'aymeric',
				'nom' => 'Marion',
				'prenom' => 'Aymeric',
				'email' => 'laboucherieduveyron@orange.fr',
				'telephone' => '06 87 86 41 78',
				'role' => 'ROLE_ADMIN',
				'enabled' => true,
				),
			);
		// crée les users par défaut
		$newUsers = array();
		$attrMethods = array('set', 'add');
		foreach ($users as $key => $user) {
			$newUsers[$key] = $this->UserManager->createUser();
			foreach ($user as $attribute => $value) {
				foreach ($attrMethods as $method) {
					$m = $method.ucfirst($attribute);
					if(method_exists($newUsers[$key], $m)) {
						if($attribute == self::PASSWORD) {
							$encoder = $this->EncoderFactory->getEncoder($newUsers[$key]);
							$value = $encoder->encodePassword($value, $newUsers[$key]->getSalt());
						}
						$newUsers[$key]->$m($value);
					}
				}
			}
			$this->UserManager->updateUser($newUsers[$key]);
		}
		return $newUsers;
	}

}


