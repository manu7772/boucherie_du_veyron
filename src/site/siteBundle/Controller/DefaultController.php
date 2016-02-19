<?php

namespace site\siteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use site\adminBundle\Entity\message;
use site\adminBundle\Form\contactmessageType;
use \DateTime;

use site\adminBundle\Entity\pageweb;

class DefaultController extends Controller {

	public function indexAction() {
		$data['pageweb'] = $this->get('aetools.aePageweb')->getDefaultPage();
		if(is_object($data['pageweb'])) {
			$this->pagewebactions($data);
			// chargement de la pageweb
			if(isset($data['redirect'])) {
				return $this->redirect($data['redirect']);
			} else if(is_object($data['pageweb'])) {
				return $this->render($data['pageweb']->getTemplate(), $data);
			}
		} else {
			// si aucune page web… chargement de la page par défaut…
			$userService = $this->get('service.users');
			$userService->usersExist(true);
			return $this->redirect($this->generateUrl('generate'));
		}
	}

	public function pagewebAction($pageweb, $params = null) {
		$data = $this->get('tools_json')->JSonExtract($params);
		$data['pageweb'] = $this->get('aetools.aePageweb')->getRepository()->findOneBySlug($pageweb);
		if(is_object($data['pageweb'])) {
			$this->pagewebactions($data);
			// chargement de la pageweb
			if(isset($data['redirect'])) {
				return $this->redirect($data['redirect']);
			} else if(is_object($data['pageweb'])) {
				return $this->render($data['pageweb']->getTemplate(), $data);
			}
		} else {
			// si aucune page web… chargement de la page par défaut…
			$userService = $this->get('service.users');
			$userService->usersExist(true);
			return $this->redirect($this->generateUrl('generate'));
		}
	}

	protected function pagewebactions(&$data) {
		switch ($data['pageweb']->getModelename()) {
			case 'contact':
				// page contact
				$message = $this->getNewEntity('site\adminBundle\Entity\message');
				$form = $this->createForm(new contactmessageType($this, []), $message);
				// $this->repo = $this->em->getRepository('site\adminBundle\Entity\message');
				$request = $this->getRequest();
				if($request->getMethod() == 'POST') {
					// formulaire reçu
					$form->bind($request);
					if($form->isValid()) {
						// get IP & DateTime
						$message->setIp($request->getClientIp());
						$message->setCreation(new DateTime());
						if(is_object($this->getUser())) {
							$message->setNom($this->getUser()->getNom());
							$message->setPrenom($this->getUser()->getPrenom());
							$message->setTelephone($this->getUser()->getTelephone());
							$message->setEmail($this->getUser()->getEmail());
							$message->setUser($this->getUser());
						}
						// enregistrement
						$this->em->persist($message);
						$this->em->flush();
						$data['message_success'] = "message.success";
						// nouveau formulaire
						$new_message = $this->getNewEntity('site\adminBundle\Entity\message');
						$new_message->setNom($message->getNom());
						$new_message->setPrenom($message->getPrenom());
						$new_message->setTelephone($message->getTelephone());
						$new_message->setEmail($message->getEmail());
						// $new_message->setObjet($message->getObjet());
						$form = $this->createForm(new contactmessageType($this, []), $new_message);
						$data['redirect'] = $this->generateUrl('site_pageweb', array('pageweb' => $data['pageweb']));
					} else {
						$data['message_error'] = "message.error";
					}
				}
				$data['message_form'] = $form->createView();
				break;
			
			default:
				# code...
				break;
		}
		return $data;
	}

	protected function getNewEntity($classname) {
		$newEntity = new $classname();
		$this->em = $this->getDoctrine()->getManager();
		if(method_exists($newEntity, 'setStatut')) {
			// si un champ statut existe
			$statut = $this->em->getRepository('site\adminBundle\Entity\statut')->defaultVal();
			if(is_array($statut)) $statut = reset($statut);
			$newEntity->setStatut($statut);
		}
		return $newEntity;
	}

	public function headerMiddleAction() {
		$data['menu'] = $this->get('aeMenus')->getMenu('site-menu');
		// récupération route/params requête MASTER
		$stack = $this->get('request_stack');
		$masterRequest = $stack->getMasterRequest();
		$data['infoRoute']['_route'] = $masterRequest->get('_route');
		$data['infoRoute']['_route_params'] = $masterRequest->get('_route_params');
		return $this->render('sitesiteBundle:blocks:headerMiddle.html.twig', $data);
	}

	public function sidemenuAction() {
		$user = $this->getUser();
		if(is_object($user)) {
			$data['menu'] = $this->get('aeMenus')->getMenu('admin-sidemenu');
			$data['bundles.User.name']['params']['name'] = $user->getUsername();
			return $this->render('sitesiteBundle:blocks:sidemenu.html.twig', $data);
		} else {
			return new Response(null);
		}
	}

	public function mainmenuAction() {
		$data = array();
		return $this->render('sitesiteBundle:blocks:mainmenu.html.twig', $data);
	}

	public function diaporamaAction($slug = 'intro') {
		$data = array();
		return $this->render('sitesiteBundle:blocks:diaporama.html.twig', $data);
	}

}
