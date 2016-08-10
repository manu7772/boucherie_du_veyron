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
	const FIND_EXTENDED = true;

	public function indexAction() {
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		$data['pageweb'] = $this->get('aetools.aePageweb')->getDefaultPage();
		if(isset($data['pageweb']['id'])) {
			// $this->get('aetools.aeDebug')->startChrono();
			$this->pagewebactions($data);
			// $this->get('aetools.aeDebug')->printChrono('Pagweb action', true);
			// chargement de la pageweb
			if(isset($data['redirect'])) {
				return $this->redirect($data['redirect']);
			} else if(isset($data['pageweb']['id'])) {
				return $this->render($data['pageweb']["template"], $data);
			}
		} else {
			// si aucune page web… chargement de la page par défaut…
			$httpHost = $this->get('request')->getSchemeAndHttpHost();
			$locale = $this->get('request')->getLocale();
			// echo('<p>No data in base : user creation…</p>');
			// echo('<p><strong>'.$httpHost.' / '.$locale.'</strong></p>');
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

	// public function pagewebCategorieAction($categorieSlug, $params = null) {
	// 	$data = $this->get('tools_json')->JSonExtract($params);
	// 	$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
	// 	$data['categorie'] = $categorieSlug;
	// 	$categorie = $this->get('aetools.aeCategorie')->getRepo()->findOneBySlug($categorieSlug);
	// 	return $this->pagewebPagewebAction($categorie->getGroup_pagewebsChilds()[0], $data);
	// }

	// public function pagewebPagewebAction($pagewebSlug, $params = null) {
	// 	$data = $this->get('tools_json')->JSonExtract($params);
	// 	$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
	// 	// $data['pageweb'] = $this->get('aetools.aePageweb')->getDefaultPage();
	// 	if(is_object($pagewebSlug)) $data['pageweb'] = $pagewebSlug;
	// 		else $data['pageweb'] = $this->get('aetools.aePageweb')->getRepo()->findOneBySlug($pagewebSlug);
	// 	// $data['marques'] = $this->get('aetools.aeEntity')->getRepo('site\adminsiteBundle\Entity\marque')->findAll();
	// 	if(is_object($data['pageweb'])) {
	// 		$this->pagewebactions($data);
	// 		// chargement de la pageweb
	// 		if(isset($data['redirect'])) {
	// 			return $this->redirect($data['redirect']);
	// 		} else if(is_object($data['pageweb'])) {
	// 			return $this->render($data['pageweb']["template"], $data);
	// 		}
	// 	} else {
	// 		// si aucune page web… chargement de la page par défaut…
	// 		return $this->redirectToRoute('sitesite_homepage');
	// 		// $userService = $this->get('aetools.aeUser');
	// 		// $userService->usersExist(true);
	// 		// return $this->redirectToRoute('generate');
	// 	}
	// }


	public function categorieAction($itemSlug, $parentSlug = null) {
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		// categorie
		$data['categorie'] = $this->get('aetools.aeCategorie')->getRepo()->findParentsOfCategorieBySlug($itemSlug);
		$data['items'] = $this->get('aetools.aeNested')->getRepo()->findAllItemsByGroup($data['categorie'][0]['id'], 'nesteds', array('article'), self::ACCEPT_ALIAS_ITEMS, self::ADD_ALIAS_ITEMS);
		// pageweb template
		$data['pageweb'] = $this->get('aetools.aePageweb')->getPageBySlug('categorie');
		$this->pagewebactions($data);
		return $this->render($data['pageweb']["template"], $data);
	}

	public function pagewebAction($itemSlug, $parentSlug = null) {
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		$data['pageweb'] = $this->get('aetools.aePageweb')->getPageBySlug($itemSlug);
		if($parentSlug != null) {
			$data['categorie'] = $this->get('aetools.aeCategorie')->getRepo()->findOneBySlug($parentSlug);
			// $data['categorie'] = $this->get('aetools.aeNested')->getRepo()->findArrayTree($parentSlug, 'all', null, false, 0, self::FIND_EXTENDED);
		}
		$this->pagewebactions($data);
		return $this->render($data['pageweb']['template'], $data);
	}

	////////////////////
	// ARTICLES
	////////////////////

	public function articleAction($itemSlug, $parentSlug = null) {
		$data['article'] = $this->get('aetools.aeArticle')->getRepo()->findArticleBySlug($itemSlug, $parentSlug);
		// echo('<pre>');var_dump($data['article']);die('</pre>');
		if($data['article'] === false) {
			// parentSlug n'est pas un parent direct… on le retrouve…
			$data['article'] = $this->get('aetools.aeArticle')->getRepo()->findArticleBySlug($itemSlug);
			foreach ($data['article']['nestedpositionParents'] as $index => $parents) {
				$parents = $this->get('aetools.aeCategorie')->getRepo()->findParentsOfCategorie($data['article']['nestedpositionParents'][$index]['parent']['id']);
				foreach ($parents as $key => $parent) {
					if($parent['slug'] === $parentSlug) $data['categories'] = $parents;
				}
			}
		} else {
			if($data['article'] !== false) {
				if(count($data['article']['nestedpositionParents']) > 0)
					$data['categories'] = $this->get('aetools.aeCategorie')->getRepo()->findParentsOfCategorie($data['article']['nestedpositionParents'][0]['parent']['id']);
					// echo('<pre>');var_dump($data['categories']);die('</pre>');
			}
		}
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		$data['pageweb'] = $this->get('aetools.aePageweb')->getPageBySlug('article');
		$this->pagewebactions($data);
		return $this->render($data['pageweb']["template"], $data);
	}

	public function articlesByCategorieAction($categorieSlug) {
		$data['categorie'] = $this->get('aetools.aeCategorie')->getRepo()->findOneBySlug($categorieSlug);
		// $data['entites'] = $data['categorie']->getAllNestedChildsByClass('article');
		$data['pageweb'] = $this->get('aetools.aePageweb')->getPageBySlug($itemSlug);
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		$this->pagewebactions($data);
		return $this->render($data['pageweb']["template"], $data);
	}

	////////////////////
	// FICHES
	////////////////////

	public function ficheAction($itemSlug, $parentSlug = null) {
		$data['fiche'] = $this->get('aetools.aefiche')->getRepo()->findOneBySlug($itemSlug);
		if($parentSlug != null)
			$data['categorie'] = $this->get('aetools.aeCategorie')->getRepo()->findOneBySlug($parentSlug);
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		$data['pageweb'] = $this->get('aetools.aePageweb')->getPageBySlug($itemSlug);
		return $this->render('sitesiteBundle:extended_pages_web:fiche.html.twig', $data);
	}

	public function fichesByCategorieAction($categorieSlug) {
		$data['categorie'] = $this->get('aetools.aeCategorie')->getRepo()->findOneBySlug($categorieSlug);
		// $data['entites'] = $data['categorie']->getAllNestedChildsByClass('fiche');
		$data['pageweb'] = $this->get('aetools.aePageweb')->getPageBySlug($itemSlug);
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		$this->pagewebactions($data);
		return $this->render($data['pageweb']["template"], $data);
	}






	protected function pagewebactions(&$data) {
		$trans = $this->get('translator');
		switch ($data['pageweb']["modelename"]) {
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
						$data['redirect'] = $this->generateUrl('site_pageweb', array('pagewebSlug' => $data['pageweb']['slug']));
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

	public function menuNavAction() {
		$this->get('aetools.aeDebug')->startChrono();
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		// echo('<pre>');var_dump($data['sitedata']);die('</pre>');
		if(count($data['sitedata']) > 0) {
			$data['menuNav'] = $this->get('aetools.aeNested')->getRepo()->findArrayTree($data['sitedata']['menuNav_id'], 'all', null, false, 1, self::FIND_EXTENDED);
			if(is_array($data['menuNav'])) $data['menuNav'] = reset($data['menuNav']);
		} else {
			$data['menuNav'] = array();
		}
		$this->get('aetools.aeDebug')->printChrono('Nav menu action', true);

		// récupération route/params requête MASTER
		$stack = $this->get('request_stack');
		$masterRequest = $stack->getMasterRequest();
		$data['infoRoute']['_route'] = $masterRequest->get('_route');
		$data['infoRoute']['_route_params'] = $masterRequest->get('_route_params');
		return $this->render('sitesiteBundle:blocks:menunav.html.twig', $data);
	}

	// Menu latéral gauche articles
	public function menuArticleAction($menuNav = []) {
		$this->get('aetools.aeDebug')->startChrono();
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		$data['menuArticle'] = $this->get('aetools.aeNested')->getRepo()->findArrayTree($data['sitedata']['menuArticle_id'], 'all', null, false, 2, self::FIND_EXTENDED);
		if(is_array($data['menuArticle'])) $data['menuArticle'] = reset($data['menuArticle']);
		// echo('<pre>');var_dump($data['menu']);echo('</pre>');
		$this->get('aetools.aeDebug')->printChrono('Main menu action', true);
		// die();
		return $this->render('sitesiteBundle:blocks:menuarticle.html.twig', $data);
	}

	public function miniListeInfoAction($categorieArticles = []) {
		$this->get('aetools.aeDebug')->startChrono();
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		if(count((array)$categorieArticles) < 1) {
			// données de siteDate
			$categorieArticles = $data['sitedata']['categorieArticles'];
		}
		$data['items'] = array();
		if(count((array)$categorieArticles) > 0) {
			foreach((array)$categorieArticles as $key => $value) {
				$it = $this->get('aetools.aeNested')->getRepo()->findArrayTree($value['id'], 'all', null, false, null, self::FIND_EXTENDED);
				if(count($it) > 0) $data['items'][$value['id']] = $it[0];
			}
		}
		$this->get('aetools.aeDebug')->printChrono('Mini list action', true);
		return $this->render('sitesiteBundle:blocks:mini-liste-info.html.twig', $data);
	}

	public function footerTopAction() {
        $this->get('aetools.aeDebug')->startChrono();
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		$data['categorieFooters'] = array();
		if(count($data['sitedata']) > 0) {
			foreach($data['sitedata']['categorieFooters'] as $dat) {
				$it = $this->get('aetools.aeNested')->getRepo()->findArrayTree($dat['id'], 'all', null, false, 1, self::FIND_EXTENDED);
				if(count($it) > 0) $data['categorieFooters'][] = $it[0];
			}
		}
		$this->get('aetools.aeDebug')->printChrono('Get site footer', true);
		return $this->render('sitesiteBundle:blocks:footerTop.html.twig', $data);
	}

	public function diaporamaAction($id) {
		// $data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		$data['diaporama'] = $this->get('aetools.aeNested')->getRepo()->findArrayTree($id, 'all', null, false, 2, self::FIND_EXTENDED);
		if(is_array($data['diaporama'])) $data['diaporama'] = reset($data['diaporama']);
		return $this->render('sitesiteBundle:blocks:diaporama.html.twig', $data);
	}

}
