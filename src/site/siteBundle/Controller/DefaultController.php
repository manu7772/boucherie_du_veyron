<?php

namespace site\siteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Labo\Bundle\AdminBundle\services\flashMessage;

use site\adminsiteBundle\Entity\message;
use site\adminsiteBundle\Entity\pageweb;
use site\adminsiteBundle\Form\contactmessageType;
use Labo\Bundle\AdminBundle\Entity\LaboUser;

use \DateTime;
use \Exception;

class DefaultController extends Controller {

	const ACCEPT_ALIAS_ITEMS = false;
	const ADD_ALIAS_ITEMS = true;
	const FIND_EXTENDED = true;
	const SITE_DATA = 'sitedata';

	public function indexAction() {
		$data['pageweb'] = $this->get('aetools.aeServicePageweb')->getDefaultPage();
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
					return $this->redirect($domain_admin['reseau'].$domain_admin['prefix'].$domain_admin[''].'/'.$domain_admin['domain'].'.'.$domain_admin['extensions'][0].'/'.$locale.$domain_admin['path'].'/generate');
					break;
			}
		}
	}

	protected function getSitedata() {
		return $this->getRequest()->getSession()->get(self::SITE_DATA);
	}

	// public function pagewebCategorieAction($categorieSlug, $params = null) {
	// 	$data = $this->get('tools_json')->JSonExtract($params);
	// 	$data['categorie'] = $categorieSlug;
	// 	$categorie = $this->get('aetools.aeServiceCategorie')->getRepo()->findOneBySlug($categorieSlug);
	// 	return $this->pagewebPagewebAction($categorie->getGroup_pagewebsChilds()[0], $data);
	// }

	// public function pagewebPagewebAction($pagewebSlug, $params = null) {
	// 	$data = $this->get('tools_json')->JSonExtract($params);
	// 	// $data['pageweb'] = $this->get('aetools.aeServicePageweb')->getDefaultPage();
	// 	if(is_object($pagewebSlug)) $data['pageweb'] = $pagewebSlug;
	// 		else $data['pageweb'] = $this->get('aetools.aeServicePageweb')->getRepo()->findOneBySlug($pagewebSlug);
	// 	// $data['marques'] = $this->get('aetools.aeServiceBaseEntity')->getRepo('site\adminsiteBundle\Entity\marque')->findAll();
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
		// categorie
		$data['categorie'] = $this->get('aetools.aeServiceCategorie')->getRepo()->findBySlug($itemSlug)[0];
		// $data['categorie'] = $this->get('aetools.aeServiceCategorie')->getRepo()->findParentsOfCategorieBySlug($itemSlug, $parentSlug);
		// echo('<pre>');var_dump($data['categorie']);die('</pre>');
		// $data['items'] = $this->get('aetools.aeServiceNested')->getRepo()->findAllItemsByGroup($data['categorie'][0]['id'], 'nesteds', array('article'), self::ACCEPT_ALIAS_ITEMS, self::ADD_ALIAS_ITEMS);
		// $data['items'] = $data['categorie']->getAllNestedChildsByGroup('nesteds');
		// pageweb template
		if($data['categorie']->getPageweb() != null) $data['pageweb'] = $data['categorie']->getPageweb();
			else $data['pageweb'] = $this->get('aetools.aeServicePageweb')->getPageBySlug('categorie');
		$this->pagewebactions($data);
		return $this->render($data['pageweb']["template"], $data);
	}

	public function pagewebAction($itemSlug, $parentSlug = null) {
		$data['pageweb'] = $this->get('aetools.aeServicePageweb')->getPageBySlug($itemSlug);
		if($parentSlug != null) {
			$data['categorie'] = $this->get('aetools.aeServiceCategorie')->getRepo()->findOneBySlug($parentSlug);
			// $data['categorie'] = $this->get('aetools.aeServiceNested')->getRepo()->findArrayTree($parentSlug, 'all', null, false, 0, self::FIND_EXTENDED);
		}
		$this->pagewebactions($data);
		return $this->render($data['pageweb']['template'], $data);
	}

	////////////////////
	// ARTICLES
	////////////////////

	public function articleAction($itemSlug, $parentSlug = null) {
		$data['article'] = $this->get('aetools.aeServiceArticle')->getRepo()->findBySlug($itemSlug)[0];
		$data['categorie'] = null;
		if($parentSlug != null) {
			$data['categorie'] = $this->get('aetools.aeServiceCategorie')->getRepo()->findBySlug($parentSlug)[0];
		} else {
			$data['categorie'] = $this->get('aetools.aeServiceCategorie')->getRepo()->find($this->getSitedata()['menuArticle_id']);
		}
		// foreach($data['article']->getCategorieParents() as $parent) {
		// 	if((preg_match('#^(article)#', $parent->getType()) && $parentSlug == null) || $parent->getSlug() == $parentSlug) {
		// 		$data['categorie'] = $parent;
		// 		break 1;
		// 	}
		// }
		$data['pageweb'] = $this->get('aetools.aeServicePageweb')->getPageBySlug('article');
		$this->pagewebactions($data);
		return $this->render($data['pageweb']["template"], $data);
	}

	public function articlesByCategorieAction($categorieSlug) {
		$data['categorie'] = $this->get('aetools.aeServiceCategorie')->getRepo()->findOneBySlug($categorieSlug);
		// $data['entites'] = $data['categorie']->getAllNestedChildsByClass('article');
		$data['pageweb'] = $this->get('aetools.aeServicePageweb')->getPageBySlug($itemSlug);
		$this->pagewebactions($data);
		return $this->render($data['pageweb']["template"], $data);
	}

	////////////////////
	// FICHES
	////////////////////

	public function ficheAction($itemSlug, $parentSlug = null) {
		$data['fiche'] = $this->get('aetools.aeServiceFiche')->getRepo()->findOneBySlug($itemSlug);
		if($parentSlug != null)
			$data['categorie'] = $this->get('aetools.aeServiceCategorie')->getRepo()->findOneBySlug($parentSlug);
		$data['pageweb'] = $this->get('aetools.aeServicePageweb')->getPageBySlug($itemSlug);
		return $this->render('sitesiteBundle:extended_pages_web:fiche.html.twig', $data);
	}

	public function fichesByCategorieAction($categorieSlug) {
		$data['categorie'] = $this->get('aetools.aeServiceCategorie')->getRepo()->findOneBySlug($categorieSlug);
		// $data['entites'] = $data['categorie']->getAllNestedChildsByClass('fiche');
		$data['pageweb'] = $this->get('aetools.aeServicePageweb')->getPageBySlug($itemSlug);
		$this->pagewebactions($data);
		return $this->render($data['pageweb']["template"], $data);
	}






	protected function pagewebactions(&$data) {
		$trans = $this->get('translator');
		switch ($data['pageweb']["modelename"]) {
			case 'contact':
				// page contact
				if($this->getUser() instanceOf LaboUser) {
					// user connected : delete user info in session
					$olddata = $this->getRequest()->getSession()->get('user');
					unset($olddata['nom']);
					unset($olddata['prenom']);
					unset($olddata['email']);
					unset($olddata['telephone']);
					$this->getRequest()->getSession()->set('user', $olddata);
				}
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
						if($this->getUser() instanceOf LaboUser) {
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
						// envoi mail aux admin (si option User::mailSitemessages == true)
						$users = $this->getDoctrine()->getManager()->getRepository('Labo\Bundle\AdminBundle\Entity\LaboUser')->findCollaborators($this->getRequest()->getSession()->get('sitedata')['id']);
						$this->get('aetools.aeEmail')->emailMessage($users, $message);
						// nouveau formulaire
						// info in session…
						$olddata = $this->getRequest()->getSession()->get('user');
						$olddata['nom'] = $message->getNom();
						$olddata['prenom'] = $message->getPrenom();
						$olddata['email'] = $message->getEmail();
						$olddata['telephone'] = $message->getTelephone();
						$this->getRequest()->getSession()->set('user', $olddata);

						$form = $this->createForm(new contactmessageType($this, []), $this->getNewEntity('site\adminsiteBundle\Entity\message'));
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
					$data['categorie'] = $this->get('aetools.aeServiceCategorie')->getRepo()->findOneBySlug($data['categorie']);
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
		if($classname == 'site\adminsiteBundle\Entity\message') {
			if($this->getUser() instanceOf LaboUser) {
				$newEntity->setUser($this->getUser());
			} else {
				$userSessionData = $this->getRequest()->getSession()->get('user');
				// echo('<pre>');var_dump($userSessionData);echo('</pre>');
				if(isset($userSessionData['nom'])) $newEntity->setNom($userSessionData['nom']);
				if(isset($userSessionData['prenom'])) $newEntity->setPrenom($userSessionData['prenom']);
				if(isset($userSessionData['email'])) $newEntity->setEmail($userSessionData['email']);
				if(isset($userSessionData['telephone'])) $newEntity->setTelephone($userSessionData['telephone']);
			}
		}
		return $newEntity;
	}


	// SUB QUERIES

	public function menuNavAction() {
		// $this->get('aetools.aeDebug')->startChrono();
		// echo('<pre>');var_dump($this->getSitedata();die('</pre>');
		$data['menuNav'] = $this->get('aetools.aeCache')->getCacheNamedFile('menuNavigation', $this->getParameter('cache')['delay']);
		if($data['menuNav'] === null) {
			if(isset($this->getSitedata()['menuNav_id'])) {
				$data['menuNav'] = $this->get('aetools.aeServiceNested')->getRepo()->findArrayTree($this->getSitedata()['menuNav_id'], 'all', null, false, 2, self::FIND_EXTENDED);
				if(is_array($data['menuNav'])) {
					$data['menuNav'] = reset($data['menuNav']);
					$this->get('aetools.aeCache')->cacheNamedFile('menuNavigation', $data['menuNav'], false, true);
				}
			} else {
				$data['menuNav'] = array();
			}
		}
		// $this->get('aetools.aeDebug')->printChrono('Nav menu action', true);

		// récupération route/params requête MASTER
		$stack = $this->get('request_stack');
		$masterRequest = $stack->getMasterRequest();
		$data['infoRoute']['_route'] = $masterRequest->get('_route');
		$data['infoRoute']['_route_params'] = $masterRequest->get('_route_params');
		return $this->render('sitesiteBundle:blocks:menunav.html.twig', $data);
	}

	// Menu latéral gauche articles
	public function menuArticleAction() {
		// $this->get('aetools.aeDebug')->startChrono();
		$data['menuArticle'] = $this->get('aetools.aeCache')->getCacheNamedFile('menuArticle', $this->getParameter('cache')['delay']);
		if($data['menuArticle'] === null) {
			$data['menuArticle'] = $this->get('aetools.aeServiceNested')->getRepo()->findArrayTree($this->getSitedata()['menuArticle_id'], 'all', null, false, 2, self::FIND_EXTENDED);
			if(is_array($data['menuArticle'])) {
				$data['menuArticle'] = reset($data['menuArticle']);
				$this->get('aetools.aeCache')->cacheNamedFile('menuArticle', $data['menuArticle'], false, true);
			} else {
				$data['menuArticle'] = array();
			}
		}
		// echo('<pre>');var_dump($data['menu']);echo('</pre>');
		// $this->get('aetools.aeDebug')->printChrono('Main menu action', true);
		// die();
		return $this->render('sitesiteBundle:blocks:menuarticle.html.twig', $data);
	}

	public function miniListeInfoAction($categorieArticles = []) {
		// $this->get('aetools.aeDebug')->startChrono();
		if(count((array)$categorieArticles) < 1) {
			// données de siteDate
			$categorieArticles = [];
			if(isset($this->getSitedata()['categorieArticles'])) $categorieArticles = $this->getSitedata()['categorieArticles'];
		}
		$data['items'] = array();
		if(count((array)$categorieArticles) > 0) {
			foreach((array)$categorieArticles as $key => $value) {
				$it = $this->get('aetools.aeServiceNested')->getRepo()->findArrayTree($value['id'], 'all', null, false, null, self::FIND_EXTENDED);
				if(count($it) > 0) $data['items'][$value['id']] = $it[0];
			}
		}
		// $this->get('aetools.aeDebug')->printChrono('Mini list action', true);
		return $this->render('sitesiteBundle:blocks:mini-liste-info.html.twig', $data);
	}

	public function footerTopAction() {
        // $this->get('aetools.aeDebug')->startChrono();
		$data['categorieFooters'] = $this->get('aetools.aeCache')->getCacheNamedFile('categorieFooters', $this->getParameter('cache')['delay']);
		if($data['categorieFooters'] === null) {
			if(isset($this->getSitedata()['categorieFooters'])) {
				foreach($this->getSitedata()['categorieFooters'] as $dat) {
					$it = $this->get('aetools.aeServiceNested')->getRepo()->findArrayTree($dat['id'], 'all', null, false, 1, self::FIND_EXTENDED);
					if(count($it) > 0) {
						$data['categorieFooters'][] = $it[0];
					}
				}
				$this->get('aetools.aeCache')->cacheNamedFile('categorieFooters', $data['categorieFooters'], false, true);
			} else {
				$data['categorieFooters'] = array();
			}
		}
		// $this->get('aetools.aeDebug')->printChrono('Get site footer', true);
		return $this->render('sitesiteBundle:blocks:footerTop.html.twig', $data);
	}

	public function diaporamaAction($id) {
		$data['diaporama'] = $this->get('aetools.aeServiceNested')->getRepo()->findArrayTree($id, 'all', null, false, 2, self::FIND_EXTENDED);
		if(is_array($data['diaporama'])) $data['diaporama'] = reset($data['diaporama']);
		return $this->render('sitesiteBundle:blocks:diaporama.html.twig', $data);
	}

}
