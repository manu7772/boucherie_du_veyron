<?php

namespace site\siteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Labo\Bundle\AdminBundle\services\flashMessage;

use site\adminsiteBundle\Entity\message;
use site\adminsiteBundle\Entity\pageweb;
use site\adminsiteBundle\Form\contactmessageType;

use \DateTime;
use \Exception;

class DefaultController extends Controller {

	const ACCEPT_ALIAS_ITEMS = false;
	const ADD_ALIAS_ITEMS = true;


	protected function addSiteData(&$data = null, $siteDataId = null) {
		if($data === null) $data = array();
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData($siteDataId);
		return $data;
	}

	public function indexAction() {
		$data = $this->addSiteData();
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
			$userService = $this->get('aetools.aeUser');
			$userService->usersExist(true);
			switch ($httpHost) {
				case 'http://localhost':
					// LOCALHOST
					return $this->redirectToRoute('generate');
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
		$this->addSiteData($data);
		$data['categorie'] = $categorieSlug;
		$categorie = $this->get('aetools.aeCategorie')->getRepo()->findOneBySlug($categorieSlug);
		return $this->pagewebPagewebAction($categorie->getGroup_pagewebsChilds()[0], $data);
	}

	public function pagewebPagewebAction($pagewebSlug, $params = null) {
		$data = $this->get('tools_json')->JSonExtract($params);
		$this->addSiteData($data);
		// $data['pageweb'] = $this->get('aetools.aePageweb')->getDefaultPage();
		if(is_object($pagewebSlug)) $data['pageweb'] = $pagewebSlug;
			else $data['pageweb'] = $this->get('aetools.aePageweb')->getRepo()->findOneBySlug($pagewebSlug);
		// $data['marques'] = $this->get('aetools.aeEntity')->getRepo('site\adminsiteBundle\Entity\marque')->findAll();
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
			return $this->redirectToRoute('sitesite_homepage');
			// $userService = $this->get('aetools.aeUser');
			// $userService->usersExist(true);
			// return $this->redirectToRoute('generate');
		}
	}


	public function categorieAction($itemSlug, $parentSlug = null) {
		$data = $this->addSiteData();
		// categorie
		$data['categorie'] = $this->get('aetools.aeCategorie')->getRepo()->findOneBySlug($itemSlug);
		$data['parent'] = $this->get('aetools.aeCategorie')->getRepo()->findOneBySlug($parentSlug);
		// items -> findChildrenByGroup : 'nesteds'
		$nestedRepo = $this->get('aetools.aeNested')->getRepo(); // ->declareContext($this->get('aetools.aeEntity'));
		// $data['items'] = $nestedRepo->findItemsByGroup($data['categorie']->getId(), 'nesteds', self::ACCEPT_ALIAS_ITEMS);
		$data['items'] = $nestedRepo->findAllItemsByGroup($data['categorie']->getId(), 'nesteds', array('article'), self::ACCEPT_ALIAS_ITEMS, self::ADD_ALIAS_ITEMS);
		// pageweb template
		if(count($data['categorie']->getGroup_pagewebsChilds()) > 0) {
			$data['pageweb'] = $data['categorie']->getGroup_pagewebsChilds()[0];
		} else {
			$data['pageweb'] = $this->get('aetools.aePageweb')->getRepo()->findOneByNom('categorie');
		}
		$this->pagewebactions($data);
		return $this->render($data['pageweb']->getTemplate(), $data);
	}

	public function pagewebAction($itemSlug, $parentSlug = null) {
		$data['pageweb'] = $this->get('aetools.aePageweb')->getRepo()->findOneBySlug($itemSlug);
		if($parentSlug != null)
			$data['categorie'] = $this->get('aetools.aeCategorie')->getRepo()->findOneBySlug($parentSlug);
		$this->addSiteData($data);
		$this->pagewebactions($data);
		return $this->render($data['pageweb']->getTemplate(), $data);
	}

	public function articleAction($itemSlug, $parentSlug = null) {
		$data['article'] = $this->get('aetools.aeArticle')->getRepo()->findOneBySlug($itemSlug);
		if($parentSlug != null)
			$data['categorie'] = $this->get('aetools.aeCategorie')->getRepo()->findOneBySlug($parentSlug);
		$this->addSiteData($data);
		return $this->render('sitesiteBundle:extended_pages_web:article.html.twig', $data);
	}

	public function articlesAction($categorieSlug) {
		$data['categorie'] = $this->get('aetools.aeCategorie')->getRepo()->findOneBySlug($categorieSlug);
		// $data['entites'] = $data['categorie']->getAllNestedChildsByClass('article');
		if(count($data['categorie']->getGroup_pagewebsChilds()) > 0) {
			$data['pageweb'] = $data['categorie']->getGroup_pagewebsChilds()[0];
		} else {
			$data['pageweb'] = $this->get('aetools.aePageweb')->getRepo()->findOneByNom('articles');
		}
		$this->addSiteData($data);
		$this->pagewebactions($data);
		return $this->render($data['pageweb']->getTemplate(), $data);
	}



	protected function pagewebactions(&$data) {
		$trans = $this->get('translator');
		switch ($data['pageweb']->getModelename()) {
			case 'contact':
				// page contact
				$message = $this->getNewEntity('site\adminsiteBundle\Entity\message');
				$form = $this->createForm(new contactmessageType($this, []), $message);
				// $this->repo = $this->em->getRepository('site\adminsiteBundle\Entity\message');
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
						$new_message = $this->getNewEntity('site\adminsiteBundle\Entity\message');
						$new_message->setNom($message->getNom());
						$new_message->setPrenom($message->getPrenom());
						$new_message->setTelephone($message->getTelephone());
						$new_message->setEmail($message->getEmail());
						// $new_message->setObjet($message->getObjet());
						$form = $this->createForm(new contactmessageType($this, []), $new_message);
						$data['redirect'] = $this->generateUrl('site_pageweb', array('pagewebSlug' => $data['pageweb']));
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
				if(is_string($data['categorie'])) {
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
			$statut = $this->em->getRepository('Labo\Bundle\AdminBundle\Entity\statut')->defaultVal();
			if(is_array($statut)) $statut = reset($statut);
			$newEntity->setStatut($statut);
		}
		return $newEntity;
	}


	// SUB QUERIES

	// public function headerMiddleAction($siteDataId = null) {
	// 	// $data['menu'] = $this->get('aetools.aeMenus')->getMenu('site-menu');
	// 	$data = array();
	// 	$this->addSiteData($data, $siteDataId);
	// 	// récupération route/params requête MASTER
	// 	$stack = $this->get('request_stack');
	// 	$masterRequest = $stack->getMasterRequest();
	// 	$data['infoRoute']['_route'] = $masterRequest->get('_route');
	// 	$data['infoRoute']['_route_params'] = $masterRequest->get('_route_params');
	// 	return $this->render('sitesiteBundle:blocks:headerMiddle.html.twig', $data);
	// }

	// public function sidemenuAction() {
	// 	$user = $this->getUser();
	// 	if(is_object($user)) {
	// 		$data['menu'] = $this->get('aetools.aeMenus')->getMenu('admin-sidemenu');
	// 		$data['bundles.User.name']['params']['name'] = $user->getUsername();
	// 		return $this->render('sitesiteBundle:blocks:sidemenu.html.twig', $data);
	// 	} else {
	// 		return new Response(null);
	// 	}
	// }

	// public function mainmenuAction($siteDataId = null) {
	// 	$data = array();
	// 	$this->addSiteData($data, $siteDataId);
	// 	return $this->render('sitesiteBundle:blocks:mainmenu.html.twig', $data);
	// }

	public function miniListeInfoAction($categorieArticles = []) {
		$data = array();
		if(count($categorieArticles) > 0)
			$data['items'] = $this->get('aetools.aeCategorie')->getRepo()->findWithArrayOfIds(array_keys($categorieArticles));
		else $data['items'] = array();
		return $this->render('sitesiteBundle:blocks:mini-liste-info.html.twig', $data);
	}

	// public function footerTopAction($siteDataId = null) {
	// 	$data = array();
	// 	$this->addSiteData($data, $siteDataId);
	// 	return $this->render('sitesiteBundle:blocks:footerTop.html.twig', $data);
	// }

	public function diaporamaAction($siteDataId = null) {
		$data = array();
		$this->addSiteData($data, $siteDataId);
		return $this->render('sitesiteBundle:blocks:diaporama.html.twig', $data);
	}

}
