<?php

namespace site\siteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use site\adminBundle\services\flashMessage;

use site\adminBundle\Entity\message;
use site\adminBundle\Entity\pageweb;
use site\adminBundle\Form\contactmessageType;

use \DateTime;
use \Exception;

class DefaultController extends Controller {

	public function indexAction() {
		$data['sitedata'] = $this->get('aetools.aeSite')->getDefaultSiteData();
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
			$httpHost = $this->get('request')->getSchemeAndHttpHost();
			$locale = $this->get('request')->getLocale();
			echo('<p>No data in base : user creation…</p>');
			echo('<p><strong>'.$httpHost.' / '.$locale.'</strong></p>');
			$userService = $this->get('service.users');
			$userService->usersExist(true);
			switch ($httpHost) {
				case 'http://localhost':
					// LOCALHOST
					return $this->redirect($this->generateUrl('generate'));
					break;
				default:
					// WEB SITE
					return $this->redirect('http://admin.boucherie-du-veyron.fr/'.$locale.'/admin/generate');
					break;
			}
		}
	}

	public function pagewebCategorieAction($categorieSlug, $params = null) {
		$data = $this->get('tools_json')->JSonExtract($params);
		$data['categorie'] = $categorieSlug;
		$categorie = $this->get('aetools.aeCategorie')->getRepo()->findOneBySlug($categorieSlug);
		$data['sitedata'] = $this->get('aetools.aeSite')->getDefaultSiteData();
		return $this->pagewebAction($categorie->getPageweb(), $data);
	}

	public function pagewebAction($pageweb, $params = null) {
		$data = $this->get('tools_json')->JSonExtract($params);
		$data['pageweb'] = $this->get('aetools.aePageweb')->getDefaultPage();
		$data['sitedata'] = $this->get('aetools.aeSite')->getDefaultSiteData();
		if(is_object($pageweb)) $data['pageweb'] = $pageweb;
			else $data['pageweb'] = $this->get('aetools.aePageweb')->getRepo()->findOneBySlug($pageweb);
		// $data['marques'] = $this->get('aetools.aeEntity')->getRepo('site\adminBundle\Entity\marque')->findAll();
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
			return $this->redirect($this->generateUrl('sitesite_homepage'));
			// $userService = $this->get('service.users');
			// $userService->usersExist(true);
			// return $this->redirect($this->generateUrl('generate'));
		}
	}

	protected function pagewebactions(&$data) {
		$trans = $this->get('translator');
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
						// $data['message_success'] = "message.success";
						$this->get('flash_messages')->send(array(
							'title'		=> ucfirst($trans->trans('message.title.sent')),
							'type'		=> flashMessage::MESSAGES_SUCCESS,
							'text'		=> ucfirst($trans->trans('message.success')),
						));
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
						// $data['message_error'] = "message.error";
						$this->get('flash_messages')->send(array(
							'title'		=> ucfirst($trans->trans('message.title.error')),
							'type'		=> flashMessage::MESSAGES_ERROR,
							'text'		=> ucfirst($trans->trans('message.error')),
						));
					}
				}
				$data['message_form'] = $form->createView();
				break;
			case 'articles':
				if(isset($data['categorie'])) {
					$data['categorie'] = $this->get('aetools.aeCategorie')->getRepo()->findOneBySlug($data['categorie']);
				}
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
		$data['menu'] = $this->get('aetools.aeMenus')->getMenu('site-menu');
		$data['sitedata'] = $this->get('aetools.aeSite')->getDefaultSiteData();
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
			$data['menu'] = $this->get('aetools.aeMenus')->getMenu('admin-sidemenu');
			$data['bundles.User.name']['params']['name'] = $user->getUsername();
			return $this->render('sitesiteBundle:blocks:sidemenu.html.twig', $data);
		} else {
			return new Response(null);
		}
	}

	// public function mainmenuAction() {
	// 	$data = array();
	// 	$data['mainmenu'] = $this->get('aetools.aeCategorie')->getRepo()->findByNom('Menu latéral');
	// 	return $this->render('sitesiteBundle:blocks:mainmenu.html.twig', $data);
	// }

	// public function miniListeInfoAction() {
	// 	$data = array();
	// 	return $this->render('sitesiteBundle:blocks:mini-liste-info.html.twig', $data);
	// }

	// public function footerTopAction() {
	// 	$data = array();
	// 	$data['mainmenu'] = $this->get('aetools.aeCategorie')->getRepo()->findByNom('Menu latéral');
	// 	return $this->render('sitesiteBundle:blocks:footerTop.html.twig', $data);
	// }

	public function diaporamaAction($slug = 'intro') {
		$data = array();
		return $this->render('sitesiteBundle:blocks:diaporama.html.twig', $data);
	}

}
