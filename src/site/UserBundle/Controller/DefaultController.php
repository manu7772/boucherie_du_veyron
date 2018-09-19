<?php

namespace site\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
// use site\UserBundle\Form\Type\ProfileFormType;
use Labo\Bundle\AdminBundle\services\aeData;
use site\adminsiteBundle\Entity\boutique;

/**
 * @Security("has_role('ROLE_ADMIN')")
 */
class DefaultController extends Controller {

	const ENTITE_CLASSNAME = 'site\\UserBundle\\Entity\\User';
	const ENTITE_NAME = "User";
	const USER_NAME = "utilisateur";

	/**
	 * Affichage des ventes
	 * @param string $type
	 * @return Response
	 */
	public function indexAction($type = "all", $action = 'list', $params = null) {
		$data = array();
		$data['users'] = array();
		$data["entite"] = self::ENTITE_NAME;
		$userRoles = $this->get('labo_user_roles');
		$data["type"] = $userRoles->verifRole($type);
		$data['params'] = $this->get('tools_json')->JSonExtract($params);

		// vérifie les actions sur entité
		// $this->checkPostByForm($action, $params);

		// get users
		
		switch ($data["type"]) {
			case 'ROLE_USER':
			case 'ROLE_TESTER':
			case 'ROLE_EDITOR':
			case 'ROLE_ADMIN':
			case 'ROLE_SUPER_ADMIN':
				$data['users'] = $this->getDoctrine()->getRepository(self::ENTITE_CLASSNAME)->findByRole($data["type"]);
				break;
			default: // all, tous
				$data['users'] = $this->get('fos_user.user_manager')->findUsers();
				break;
		}

		// if(is_array($data['users'])) foreach($data['users'] as $user) {
		// 	$data['delete_form'][$user->getId()] = $this->getForm($user, 'delete');
		// }

		$data['htitle'] = self::USER_NAME.'s';
		return $this->render('siteUserBundle:entites:userList.html.twig', $data);
	}

	/**
	 * Information User
	 * @param string $username
	 * @return Response
	 */
	public function showAction($username) {
		$userManager = $this->get('fos_user.user_manager');
		$data['user'] = $userManager->findUserByUsername($username);

		if(is_object($data['user'])) {
			$userRoles = $this->get('labo_user_roles');
			$data['roleNames'] = $userRoles->getRoleNames();
			$data['roleColors'] = $userRoles->getRoleColors();
			$data['panier'] = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServiceFacture')->getUserPanier($data['user']);
			$data['htitle'] = "Informations ".self::USER_NAME;
			return $this->render('siteUserBundle:entites:userShow.html.twig', $data);
		} else {
			// throw new Exception(self::USER_NAME." ".$username." inconnu.", 1);
			$message = $this->get('flash_messages')->send(array(
				'title'		=> 'Échec requête',
				'type'		=> 'error',
				'text'		=> 'L\'utilisateur "'.$username.'" n\'a pu être trouvée.'
			));
			return $this->redirectToRoute('siteUser_users');
		}
	}

	/**
	 * Modification User
	 * @param string $username
	 * @return Response
	 */
	public function editAction($username) {
		// echo('<p>Enrgistrement user (edit)</p>');
		$request = $this->getRequest();
		$userManager = $this->get('fos_user.user_manager');
		$data['user'] = $userManager->findUserByUsername($username);
		if (!is_object($data['user']) || !$data['user'] instanceof UserInterface) {
			throw new AccessDeniedException('You do not have access to this user "'.$username.'".');
		} else {
			/** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
			$formFactory = $this->get('fos_user.profile.form.factory');
			$form = $formFactory->createForm();
			$form->setData($data['user']);

			$form->handleRequest($request);

			if ($form->isValid()) {
				$dispatcher = $this->get('event_dispatcher');
				$event = new FormEvent($form, $request);
				$dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

				$image = $data['user']->getAvatar();
				if(is_object($image)) {
				    $infoForPersist = $image->getInfoForPersist();
				    // $this->container->get(aeData::PREFIX_CALL_SERVICE.'aeDebug')->debugFile($infoForPersist);
				    if($infoForPersist['removeImage'] === true || $infoForPersist['removeImage'] === 'true') {
				        // Supression de l'image
				        $data['user']->setAvatar(null);
				    } else {
				        // Gestion de l'image
				        // $service = $this->container->get(aeData::PREFIX_CALL_SERVICE.'aeServiceBaseEntity')->getEntityService($image);
				        // $service->checkAfterChange($image);
				    }
				}
				$userManager->updateUser($data['user']);
				//
				return $this->redirectToRoute('siteUser_info', array('username' => $username));
			}
			$data['htitle'] = self::USER_NAME.' '.$data['user']->getUsername();
			$data['edit_form'] = $form->createView();
			return $this->render('siteUserBundle:entites:userEdit.html.twig', $data);
		}
	}

	// public function editAction(Request $request) {
	// 	$user = $this->getUser();
	// 	if (!is_object($data['user']) || !$data['user'] instanceof UserInterface) {
	// 		throw new AccessDeniedException('This user does not have access to this section.');
	// 	}
	// 	/** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
	// 	$dispatcher = $this->get('event_dispatcher');
	// 	$event = new GetResponseUserEvent($user, $request);
	// 	$dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);
	// 	if (null !== $event->getResponse()) {
	// 		return $event->getResponse();
	// 	}
	// 	/** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
	// 	$formFactory = $this->get('fos_user.profile.form.factory');
	// 	$form = $formFactory->createForm();
	// 	$form->setData($user);
	// 	$form->handleRequest($request);
	// 	if ($form->isValid()) {
	// 		/** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
	// 		$userManager = $this->get('fos_user.user_manager');
	// 		$event = new FormEvent($form, $request);
	// 		$dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);
	// 		$userManager->updateUser($user);
	// 		if (null === $response = $event->getResponse()) {
	// 			$url = $this->generateUrl('fos_user_profile_show');
	// 			$response = new RedirectResponse($url);
	// 		}
	// 		$dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));
	// 		return $response;
	// 	}
	// 	return $this->render('FOSUserBundle:Profile:edit.html.twig', array(
	// 		'form' => $form->createView()
	// 	));
	// }

	public function changeroleAction($username, $role) {
		$userManager = $this->get('fos_user.user_manager');
		$user = $userManager->findUserByUsername($username);
		$tools = $this->get(aeData::PREFIX_CALL_SERVICE.'twigToolsTextutilities');
		if($tools->compareRoles($this->getUser(), $user, false)) {
			if($role === 'ROLE_USER') {
				$this->get('flash_messages')->send(array(
					'title'		=> 'Modification non effectuée',
					'type'		=> 'warning',
					'text'		=> 'L\'utilisateur "'.$user->getUsername().'" ne peut être retiré de '.$role.'. Si vous souhaitez, vous pouvez le <a href="'.$this->generateUrl('user_enable', array('id' => $id, 'enable' => 'disable')).'">désactiver</a>.',
				));
			} else {
				$hisRoles = $user->getRoles();
				if(in_array($role, $hisRoles)) {
					$user->removeRole($role);
					$add = 'retiré de';
					// vérif collaborator
					$collab = array_intersect($hisRoles, ['ROLE_ADMIN','ROLE_SUPER_ADMIN']);
					if(count($collab) < 1) {
						$sites = $user->getSites();
						foreach ($sites as $site) {
							$site->removeCollaborateur($user);
							// $user->removeSite($site); // ???
							$this->getDoctrine()->getManager()->flush();
						}
						// $user->setCollaborator(false);
					}
				} else {
					$user->addRole($role);
					$add = 'ajouté à';
				}
				// update
				$userManager->updateUser($user);
				$this->get('flash_messages')->send(array(
					'title'		=> 'Modification de rôle',
					'type'		=> 'success',
					'text'		=> 'Le role "'.$role.'" a été '.$add.' l\'utilisateur "'.$user->getUsername().'".',
				));
			}
		} else {
			// modifications interdites : l'utilisateur n'a pas les droits sur l'user
			$this->get('flash_messages')->send(array(
				'title'		=> 'Modification interdite',
				'type'		=> 'error',
				'text'		=> 'Vous n\'avez pas les droits requis pour modifier cet utilisateur.',
			));
		}
		return $this->redirectToRoute('siteUser_info', array('username' => $username));
		// return $this->redirectToRoute('siteUser_users');
	}

	public function enableAction($username, $enable = 'enable') {
		$userManager = $this->get('fos_user.user_manager');
		$user = $userManager->findUserByUsername($username);
		$tools = $this->get(aeData::PREFIX_CALL_SERVICE.'twigToolsTextutilities');
		if($tools->compareRoles($this->getUser(), $user, false)) {
			$enable = $enable !== 'disable';
			$done = $enable ? 'activé' : 'désactivé';
			$user->setEnabled($enable);
			// update
			$userManager->updateUser($user);
			$this->get('flash_messages')->send(array(
				'title'		=> 'Activation/désactivation',
				'type'		=> 'success',
				'text'		=> 'L\'utilisateur '.$user->getUsername().' a été '.$done.'.',
			));
		} else {
			// modifications interdites : l'utilisateur n'a pas les droits sur l'user
			$this->get('flash_messages')->send(array(
				'title'		=> 'Modification interdite',
				'type'		=> 'error',
				'text'		=> 'Vous n\'avez pas les droits requis pour modifier cet utilisateur.',
			));
		}
		return $this->redirectToRoute('siteUser_info', array('username' => $username));
		// return $this->redirectToRoute('siteUser_users');
	}

	/**
	 * Suppression User
	 * @param string $username
	 * @return Response
	 */
	public function deleteAction($username) {
		$userManager = $this->get('fos_user.user_manager');
		$user = $userManager->findUserByUsername($username);
		if(is_object($user)) {
			// User trouvé
			try {
				$userManager->deleteUser($user);
			} catch (Exception $e) {
				$message = $this->get('flash_messages')->send(array(
					'title'		=> 'Échec suppression',
					'type'		=> 'error',
					'text'		=> $e
				));
			}
			$message = $this->get('flash_messages')->send(array(
				'title'		=> 'Suppression utilisateur',
				'type'		=> 'success',
				'text'		=> 'L\'utilisateur '.$username.' a été supprimé.'
			));
			return $this->redirectToRoute('siteUser_users');
		} else {
			$message = $this->get('flash_messages')->send(array(
				'title'		=> 'Échec suppression',
				'type'		=> 'error',
				'text'		=> 'L\'utilisateur'.$username.' n\'a pu être trouvée.'
			));
			return $this->redirectToRoute('siteUser_users');
		}
	}

	protected function checkPostByForm($action = null, $params = null) {
		$request = $this->getRequest();

		if($request->getMethod() == 'POST') {
			// POST method
			$actionPost = $request->query->get('action');
			switch ($actionPost) {
				case 'delete':
					# code...
					break;
				
				default:
					# code...
					break;
			}
		}

		if($action !== null) {
			// sans POST - via GET $action
			switch ($action) {
				case 'check':
					$this->checkUsersAction($params);
					break;
				
				default:
					# code...
					break;
			}
		}
	}

	protected function getForm($entity, $action) {
		// $this->em = $this->getDoctrine()->getManager();
		// if(is_string($entity)) $entity = $this->em->getRepository($entity)->find($entity);
		if(is_object($entity)) {
			// création du formulaire
			$entityClassName = get_class($entity);
			// $exp = explode('\\', $entityClassName);
			// $entityName = end($exp);
			// icone wait admin
			$twig = $this->container->get('twig');
			$globals = $twig->getGlobals();
			$defIcon = $globals['spinIcon'];
			switch ($action) {
				case 'delete':
					$form = $this->createFormBuilder(null, array('attr' => array(
							'style' => 'display:inline;',
							'class' => 'need-confirmation', // --> modale de confirmation
							'data-title' => 'Suppression',
							'data-message'	=> 'Supprimer '.self::USER_NAME.' '.$entity->getUsername().' ?',
							// 'data-href' => "#",
							)))
						// ->setAction('') // self
						->add('entity', 'hidden', array('data' => $entityClassName))
						->add('id', 'hidden', array('data' => $entity->getId()))
						->add('submit', 'submit', array(
							'label' => '<i class="fa fa-times icon-wait-on-click" data-icon-wait="'.$defIcon.'"></i>',
							'attr' => array(
								'class' => implode(' ', array('btn-danger', 'btn-outline', 'btn-xs')),
								'title' => 'Supprimer '.self::USER_NAME.' '.$entity->getUsername(),
								)
							))
						->getForm()
					;
					$form = $form->createView();
					break;
				
				default:
					$form = null;
					break;
			}
		} else {
			// erreur
			$form = null;
		}
		return $form;
	}

	public function checkUsersAction() {
		// set_time_limit(600);
		$em = $this->getDoctrine()->getManager();
		// $userManager = $this->getDoctrine()->getManager()->getRepository(self::ENTITE_CLASSNAME);
		$userManager = $this->get('fos_user.user_manager');
		$data_users = $userManager->findUsers();

		if(count($data_users) > 1) foreach($data_users as $user) {
			// ROLES
			$roles = $user->getRoles();
			if(count($roles) < 1) $user->addRole('ROLE_USER');
			foreach ($roles as $key => $value) {
				# code...
			}
			// FACTURES
			foreach ($user->getFactures() as $facture) {
				if(!($facture->getBoutique() instanceof boutique)) $em->remove($facture);
			}
			$userManager->updateUser($user, false);
		}
		$em->flush();

		$flashMsg = $this->get('flash_messages');
		$message = $flashMsg->create(array(
			'title'		=> 'Check '.self::USER_NAME.'s',
			'type'		=> 'warning',
			'grant'		=> 'ROLE_SUPER_ADMIN',
			));
		// $message->setTitle('Check utilisateurs');
		// $message->setType('danger');
		// $message->setGrant('ROLE_SUPER_ADMIN');
		if(count($data_users) > 1) {
			$message->setText(count($data_users)." ".self::USER_NAME."s checkés.");
		} else if(count($data_users) == 1) {
			$user = reset($data_users);
			$message->setText(ucfirst(self::USER_NAME)." ".$user->getUsername()." checké.");
		} else {
			$message->setText("Aucun ".self::USER_NAME." n'a été checké.");
		}
		// $message2 = $flashMsg->create(array(
		// 	'title'		=> 'Check utilisateurs 2',
		// 	'type'		=> 'error',
		// 	'grant'		=> 'ROLE_SUPER_ADMIN',
		// 	'text'		=> 'Texte pour test…'
		// 	));
		$flashMsg->sendAll();
		return $this->redirectToRoute('siteUser_users');
	}


	/**
	 * Change user prefered language
	 * @Route("/change-user-language/{language}/{user}", name="change_user_language")
	 * @return JsonResponse
	 */
	public function changeUserLanguageAction($language, $user = null) {
		$reponse['result'] = true;
		if($user == null) {
			$reponse['result'] = false;
		} else {
			$um = $this->get('fos_user.user_manager');
			$user = $um->find($user);
			$user->setLangue($language);
			$um->updateUser($user);
		}
		return new JsonResponse($reponse);
	}

	/**
	 * Change user help mode (true if state is not defined / current user if user is not defined)
	 * @param boolean $state = true
	 * @param integer $user = null
	 * @return JsonResponse
	 */
	public function changeUserHelpAction($state = true, $user = null) {
		$trans = $this->get('translator');
		$message = $trans->trans('found.not_found', array(), 'siteUserBundle');
		$userUser = $this->getUser();
		$result = false;
		$user = $this->getUserById($user);
		if(is_object($user)) {
			if($userUser->haveRight($user)) {
				$old = $user->getAdminhelp();
				if($old !== (boolean)$state) {
					$user->setAdminhelp((boolean)$state);
					$this->get('fos_user.user_manager')->updateUser($user);
					$result = true;
					return $this->get(aeData::PREFIX_CALL_SERVICE.'aeReponse')
						->setResult($result)
						->setMessage($trans->trans('found.found', array('%username%' => $user->getUsername()), 'siteUserBundle'))
						->getJSONreponse()
						;
				} else {
					$result = true;
					$message = $trans->trans('actions.modif.no_change', array('%username%' => $user->getUsername()), 'siteUserBundle');
				}
			} else {
				// $result = false;
				$message = $trans->trans('actions.modif.forbidden', array('%username%' => $user->getUsername()), 'siteUserBundle');
			}
		}
		return $this->get(aeData::PREFIX_CALL_SERVICE.'aeReponse')
			->setResult($result)
			->setMessage($message)
			->getJSONreponse()
			;
	}

	/**
	 * Get User (current User if User is not defined)
	 * @param integer $user = null
	 * @return User or false
	 */
	protected function getUserById($user = null) {
		if($user != 'null' && $user != null) {
			$objectUser = $this->get('fos_user.user_manager')->findUserBy(array('id' => $user));
		} else {
			$objectUser = $this->get('security.token_storage')->getToken()->getUser();
		}
		return is_object($objectUser) ? $objectUser : false;
	}

}
