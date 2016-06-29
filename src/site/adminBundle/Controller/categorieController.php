<?php

namespace site\adminBundle\Controller;

use site\adminBundle\Controller\baseController;
// use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use site\adminBundle\services\flashMessage;
use site\adminBundle\Entity\categorie;
use site\adminBundle\Entity\nestedposition;

use \Exception;

/**
 * categorieController
 * @Security("has_role('ROLE_EDITOR')")
 */
class categorieController extends baseController {

	const ENTITE_NAME = 'site\adminBundle\Entity\categorie';
	const ENTITE_SHORTNAME = 'categorie';
	const NESTED_NAME = 'site\adminBundle\Entity\nested';
	const NESTED_SHORTNAME = 'nested';
	const ROOT_LEVEL = 0;
	const DEFAULT_LEVEL = 1;

	// protected $entityService;

	public function ajaxDataAction($id = null, $types = 'all', $groups = null) {
		$request = $this->getRequest();
		// $this->entityService = $this->getEntityService(self::NESTED_SHORTNAME);
		$em = $this->getDoctrine()->getManager();
		if($id == null) {
			$id = $request->request->get('id');
		}
		if($id != null && $request->isXmlHttpRequest()) {
			// AJAX REQUEST
			$types = $request->request->get('types');
			$groups = $request->request->get('groups');
			$data = $em->getRepository(self::NESTED_NAME)->findArrayTree($id, $types, $groups);
			return new JsonResponse($data);
		} else if(!$request->isXmlHttpRequest()) {
			// TEST EN GET
			// if($id == null) {
			// 	$id = $em->getRepository(self::ENTITE_NAME)->findAll();
			// 	if(is_array($id)) $id = reset($id);
			// } else {
			// 	$id = $em->getRepository(self::NESTED_NAME)->find($id);
			// }
			$data = $em->getRepository(self::NESTED_NAME)->findArrayTree($id, $types, $groups);
			return $this->render('siteadminBundle:superadmin:dump.html.twig', array('title' => 'Id '.$id, 'data' => $data));
		}
		return new JsonResponse('Error');
	}


	public function categorieListAction($type_related = null, $type_field = null, $type_values = null) {
		$data = $this->getEntiteData(self::ENTITE_NAME, self::LIST_ACTION, null, $type_related, $type_field, $type_values);
		$template = 'siteadminBundle:entites:'.$data['entite_name'].ucfirst($data['action']).'.html.twig';
		if(!$this->get('templating')->exists($template)) {
			$template = 'siteadminBundle:entites:'.'entite'.ucfirst($data['action']).'.html.twig';
			if(!$this->get('templating')->exists($template)) {
				return $this->redirect($this->generateUrl('siteadmin_homepage'));
			}
		}
		return $this->render($template, $data);
	}

	public function categorieRepoAction($method, $repoParams = null) {
		$data = $this->getEntiteData(self::ENTITE_NAME, self::LIST_ACTION, null, null, null, null, $method, $repoParams);
		$template = 'siteadminBundle:entites:'.$data['entite_name'].ucfirst($data['action']).'.html.twig';
		if(!$this->get('templating')->exists($template)) {
			$template = 'siteadminBundle:entites:'.'entite'.ucfirst($data['action']).'.html.twig';
			if(!$this->get('templating')->exists($template)) {
				return $this->redirect($this->generateUrl('siteadmin_homepage'));
			}
		}
		return $this->render($template, $data);
	}

	public function categorieShowAction($id) {
		$data = $this->getEntiteData(self::ENTITE_NAME, self::SHOW_ACTION, $id);
		$template = 'siteadminBundle:entites:'.$data['entite_name'].ucfirst($data['action']).'.html.twig';
		if(!$this->get('templating')->exists($template)) {
			$template = 'siteadminBundle:entites:'.'entite'.ucfirst($data['action']).'.html.twig';
			if(!$this->get('templating')->exists($template)) {
				return $this->redirect($this->generateUrl('siteadmin_homepage'));
			}
		}
		return $this->render($template, $data);
	}

	public function categorieEditAction($id) {
		$data = $this->getEntiteData(self::ENTITE_NAME, self::EDIT_ACTION, $id);
		$template = 'siteadminBundle:entites:'.$data['entite_name'].ucfirst($data['action']).'.html.twig';
		if(!$this->get('templating')->exists($template)) {
			$template = 'siteadminBundle:entites:'.'entite'.ucfirst($data['action']).'.html.twig';
			if(!$this->get('templating')->exists($template)) {
				return $this->redirect($this->generateUrl('siteadmin_homepage'));
			}
		}
		$data[$data['action'].'_form'] = $this->getEntityFormView($data);
		if($data[$data['action'].'_form'] == false) {
			// erreur formulaire
			$data['action'] = self::DEFAULT_ACTION;
			$data['id'] = null;
		}
		return $this->render($template, $data);
	}

	public function categorieCreateAction($rootParent = null) {
		$data = $this->getEntiteData(self::ENTITE_NAME, self::CREATE_ACTION);

		$classname = $data['classname'];
		$data['entite'] = new $classname();
		switch ($rootParent) {
			case null:
				# new root type
				$data['entite']->setLvl(0);
				break;
			default:
				$id = (integer) $rootParent;
				$rootParent = $this->entityService->getRepo()->find($id);
				if(!is_object($rootParent)) throw new Exception("Parent root #$id does not exists !", 1);
				// $data['entite']->setGroup_categorie_parentParents($rootParent);
				$data['form_memory']['group_categorie_parentParents'] = array($id);
				$this->addForm_memory($data);
				break;
		}
		// ajout de valeurs si types sont définis…
		// $this->fillEntityWithData($data);
		// Get form
		$data[$data['action'].'_form'] = $this->getEntityFormView($data);
		if($data[$data['action'].'_form'] == false) {
			// erreur formulaire
			$data['action'] = self::DEFAULT_ACTION;
			$data['id'] = null;
		}
		$template = 'siteadminBundle:entites:'.$data['entite_name'].ucfirst($data['action']).'.html.twig';
		if(!$this->get('templating')->exists($template)) {
			$template = 'siteadminBundle:entites:'.'entite'.ucfirst($data['action']).'.html.twig';
			if(!$this->get('templating')->exists($template)) {
				return $this->redirect($this->generateUrl('siteadmin_homepage'));
			}
		}
		return $this->render($template, $data);
	}

	protected function getEntiteData($entite, $action = null, $id = null, $type_related = null, $type_field = null, $type_values = null, $method = null, $repoParams = null) {
		$data = array();
		if(is_object($entite)) $entite = get_class($entite);
		$data['sitedata'] = $this->get('aetools.aeSite')->getDefaultSiteData();
		$data['entite_name'] = self::ENTITE_SHORTNAME;
		$this->entityService = $this->getEntityService($data['entite_name']);
		$repo = $this->entityService->getRepo();
		$data['classname'] = self::ENTITE_NAME;
		$data['type']['type_related']	= $type_related;
		$data['type']['type_field']		= $type_field;
		$data['type']['type_values']	= $this->typeValuesToArray(urldecode($type_values));
		$data['repo']['method']	= $method;
		$data['repo']['repoParams']	= $repoParams;
		$data['action'] = $action;
		$data['entite'] = false;
		$data['entites'] = array();
		$data['roots_list'] = $this->entityService->getRepo()->findRoots();
		$data['id'] = $id;
		if($id != null) {
			// recherche par id
			$data['entite'] = $this->entityService->getRepo()->find($id);
		}
		if($data['type']['type_related'] != null && $data['type']['type_field'] != null && $data['type']['type_values'] != null) {
			// recherche par valeurs ou liens
			if(method_exists($this->entityService->getRepo(), 'findWithField')) {
				$data['entites'] = $this->entityService->getRepo()->findWithField($data['type']);
			} else throw new Exception("Method \"findWithField\" does not exist in Repository \"".$data['classname']."\"", 1);
			// echo('<p>Entités : ('.count($data['entites']).') '.implode(', ', $data['entites']).'</p>');
		} else if($data['repo']['method'] != null) {
			if(!method_exists($repo, $data['repo']['method'])) $data['repo']['method'] = self::LIST_REPO_METHOD;
			if(!method_exists($repo, $data['repo']['method'])) $data['repo']['method'] = self::LIST_REPO_DEFAULT;
			if(method_exists($repo, $data['repo']['method'])) {
				$data['entites'] = $repo->{$data['repo']['method']}($repoParams);
			}
		} else if($data['action'] == self::DEFAULT_ACTION) {
			// recherche all
			if(method_exists($repo, self::LIST_REPO_METHOD)) $method = self::LIST_REPO_METHOD;
				else $method = self::LIST_REPO_DEFAULT;
			$data['entites'] = $repo->$method();
		}
		// autres
		$data['typeSelf'] = self::TYPE_SELF;
		$data['type_value_joiner'] = self::TYPE_VALUE_JOINER;
		// form action
		if($action == self::CREATE_ACTION) {
			$data['form_action'] = $this->generateUrl('siteadmin_form_categorie');
		}
		return $data;
	}

	public function postformAction() {
		set_time_limit(300);
		$memory = $this->get('aetools.aetools')->getConfigParameters('cropper.yml', 'memory_limit');
		ini_set("memory_limit", $memory);
		$data = array();
		$classname = self::ENTITE_SHORTNAME;
		$classname = $this->get('aetools.aeEntity')->getEntityClassName($classname);
		$entiteType = str_replace('Entity', 'Form', $classname.'Type');
		$typeTmp = new $entiteType($this);
		// REQUEST
		$request = $this->getRequest();
		// récupération hiddenData
		$req = $request->request->get($typeTmp->getName());
		echo('<pre>');
		var_dump($req);
		if(isset($req["hiddenData"])) {
			$data = json_decode(urldecode($req["hiddenData"]), true);
			var_dump($data);
		} else {
			throw new Exception("entitePostFormPageAction : hiddenData absent ! (".$typeTmp->getName().")", 1);
		}
		echo('</pre>');
		if(count($data) < 1) throw new Exception('Données "hiddenData" vides !', 1);
		// Entity service
		$entityService = $this->getEntityService($data['entite_name']);

		if($data['action'] == self::CREATE_ACTION) {
			// create
			$imsg = '';
			$data['entite'] = new $classname();
		} else {
			// edit / delete
			$imsg = ' (id:'.$data['id'].')';
			$data['entite'] = $entityService->getRepo()->find($data['id']);
		}
		if(!is_object($data['entite'])) {
			// entité invalide
			$this->get('flash_messages')->send(array(
				'title'		=> 'Entité introuvable',
				'type'		=> flashMessage::MESSAGES_ERROR,
				'text'		=> 'L\'entité "'.$data['entite_name'].'"'.$imsg.' n\'a pas été trouvée.',
			));
		} else {
			switch ($data['action']) {
				// case self::DELETE_ACTION:
				// 	$entityService->softDelete($data['entite']);
				// 	if(isset($data['onSuccess'])) return $this->redirect($data['onSuccess']);
				// 	break;
				default:
					$this->addForm_memory($data);
					$form = $this->getEntityForm($data);
					$form->bind($request);
					if($form->isValid()) {
						// formulaire valide -> enregistrement -> renvoi à l'url de suc$this->generateUrl('siteadmin_entite_type', cess
						$entityService->checkAfterChange($data['entite']);
						$save = $entityService->save($data['entite']);
						if($save->getResult() == true) {
							// ENREGISTREMENT OK
							$this->getSuccessPersistFlashMessage($data);
							return $this->redirect($this->generateUrl('siteadmin_show_categorie', array('id' => $data['entite']->getId())));
						} else {
							// erreur à l'enregistrement
							$this->get('flash_messages')->send(array(
								'title'		=> 'Erreurs de saisie',
								'type'		=> flashMessage::MESSAGES_ERROR,
								'text'		=> $save->getMessage(),
							));
							// if(isset($data['onError'])) return $this->redirect($data['onError']);
						}
						// return $this->render('siteadminBundle:blocks:dump.html.twig', array('data' => $data['entite']));
					}
					// formulaire invalide -> url echec
					$this->get('flash_messages')->send(array(
						'title'		=> 'Erreurs de saisie',
						'type'		=> flashMessage::MESSAGES_ERROR,
						'text'		=> 'La saisie de vos données contient des erreurs. Veuillez les corriger, svp.',
					));
					// if(isset($data['onError'])) return $this->redirect($data['onError']);
					// retour au formulaire…
					$template = 'siteadminBundle:entites:'.$data['entite_name'].ucfirst($data['action']).'.html.twig';
					if(!$this->get('templating')->exists($template)) {
						$template = 'siteadminBundle:entites:'.'entite'.ucfirst($data['action']).'.html.twig';
					}
					$data[$data['action'].'_form'] = $form->createView();
					return $this->render($template, $data);
					// --> http://stackoverflow.com/questions/11227975/symfony-2-redirect-using-post
					// return $this->redirectToRoute('siteadmin_entite', ['request' => $request], 307);
					break;
			}
		}
		return $this->redirect($this->generateUrl('siteadmin_entite', $data['entite_name']));
	}

	protected function addForm_memory(&$data) {
		if(isset($data['form_memory']) && is_object($data['entite'])) {
			foreach($data['form_memory'] as $field => $values) {
				$classname = $data['entite']->getClassName();
				$list = array();
				if(!is_array($values)) $values = array($values);
				foreach ($values as $value) {
					$find = $this->get('aetools.aeNested')->getRepo($classname)->find($value);
					if(is_object($find)) $list[] = $find;
				}
				if(count($list) > 0) {
					$set = 'set'.ucfirst($field);
					$data['entite']->$set($list);
				}
			}
		}
		return $data;
	}

}
