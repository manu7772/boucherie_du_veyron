<?php

namespace site\adminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use site\adminBundle\Entity\pageweb;

use site\adminBundle\services\flashMessage;

use \Exception;

class entiteController extends Controller {

	const TYPE_SELF 			= '_self';
	const DEFAULT_ACTION 		= 'list';
	const TYPE_VALUE_JOINER 	= '___';


	protected function getEntiteData($entite, $type_related = nul, $type_field = nul, $type_values = nul, $action = null, $id = null) {
		if($action == null) $action = self::DEFAULT_ACTION;
		if(is_object($entite)) $entite = get_class($entite);
		$data = array();
		$exp = explode('\\', $entite);
		if(count($exp) > 1) {
			$data['classname'] = $entite;
			$data['entite_name'] = end($exp);
		} else {
			$data['classname'] = 'site\\adminBundle\\Entity\\'.$entite;			
			$data['entite_name'] = $entite;
		}
		$data['type']['type_related']	= $type_related;
		$data['type']['type_field']		= $type_field;
		$data['type']['type_values']	= $this->typeValuesToArray(urldecode($type_values));
		$data['action'] = $action;
		$data['entite'] = false;
		$data['entites'] = array();
		$data['id'] = $id;
		// EM
		$this->em = $this->getDoctrine()->getManager();
		$this->repo = $this->em->getRepository($data['classname']);
		// autres éléments…
		switch ($data['entite_name']) {
			case '...':
				# code...
				break;
			
			default:
				# code...
				break;
		}
		// variables diverses
		$data['typeSelf'] = self::TYPE_SELF;
		$data['type_value_joiner'] = self::TYPE_VALUE_JOINER;
		return $data;
	}

	protected function typeValuesToArray($type_values = null) {
		if($type_values != null) $type_values = explode(self::TYPE_VALUE_JOINER, $type_values);
		return $type_values;
	}
	protected function typeValuesToString($type_values = null) {
		if($type_values != null) $type_values = implode(self::TYPE_VALUE_JOINER, $type_values);
		return $type_values;
	}

	public function entitePageAction($entite, $type_related = null, $type_field = null, $type_values = null, $action = null, $id = null) {
		if($action == null) $action = self::DEFAULT_ACTION;
		
		$data = $this->getEntiteData($entite, $type_related, $type_field, $type_values, $action, $id);
		// EM
		$this->em = $this->getDoctrine()->getManager();
		$this->repo = $this->em->getRepository($data['classname']);
		// actions selon entité…
		switch ($data['entite_name']) {
			case 'une entité quelconque à traiter de manière particulière…':
				break;

			default:
				// page générique entités
				switch ($action) {
					case 'create' :
						$entCname = $data['classname'];
						$data['entite'] = new $entCname();
						$data[$action.'_form'] = $this->getEntityFormView($data);
						break;
					case 'show' :
						$data['entite'] = $this->repo->find($id);
						if(!is_object($data['entite'])) throw new Exception($data['entite_name'].'.not_found', 1);
						break;
					case 'edit' :
						$data['entite'] = $this->repo->find($id);
						if(!is_object($data['entite'])) throw new Exception($data['entite_name'].'.not_found', 1);
						$data[$action.'_form'] = $this->getEntityFormView($data);
						break;
					case 'check' :
						// DEFAULT_ACTION
						if($data['type']['type_values'] != null) {
							$data['entites'] = $this->repo->findByField($data['type'], self::TYPE_SELF, true);
						} else {
							$data['entites'] = $this->repo->findAll();
						}
						break;
					default:
						// DEFAULT_ACTION
						$data['entites'] = $this->repo->findAll();
						break;
				}
				break;
		}
		
		$template = 'siteadminBundle:entites:'.$entite.ucfirst($action).'.html.twig';
		if(!$this->get('templating')->exists($template)) {
			$template = 'siteadminBundle:entites:'.'entite'.ucfirst($action).'.html.twig';
		}
		return $this->render($template, $data);
	}

	/**
	 * Actions en retour de formulaire entity
	 * @param string $classname
	 * @param string $action
	 * @param string $id
	 * @return Response
	 */
	public function entitePostFormPageAction($classname) {
		$data = array();
		$classname = urldecode($classname);
		$entiteType = str_replace('Entity', 'Form', $classname.'Type');
		$typeTmp = new $entiteType($this);
		// REQUEST
		$request = $this->getRequest();
		// récupération hiddenData
		$req = $request->request->get($typeTmp->getName());
		// if(!isset($req["hiddenData"])) throw new Exception("entitePostFormPageAction : hiddenData absent ! (".$typeTmp->getName().")", 1);
		$data = json_decode(urldecode($req["hiddenData"]), true);
		// echo('<pre>');
		// var_dump($req);
		// EntityManager
		$this->em = $this->getDoctrine()->getManager();
		$this->repo = $this->em->getRepository($classname);

		if($data['action'] == "create") {
			// create
			$imsg = '';
			$data['entite'] = new $classname();
		} else {
			// edit / delete
			$imsg = ' (id:'.$data['id'].')';
			$data['entite'] = $this->repo->find($data['id']);
		}
		if(!is_object($data['entite'])) {
			// entité invalide
			$message = $this->get('flash_messages')->send(array(
				'title'		=> 'Entité introuvable',
				'type'		=> flashMessage::MESSAGES_SUCCESS,
				'text'		=> 'L\'entité "'.$data['entite_name'].'"'.$imsg.' n\'a pas été trouvée.',
			));
		} else {
			$form = $this->getEntityForm($data);
			$form->bind($request);
			if($form->isValid()) {
				// formulaire valide -> enregistrement -> renvoi à l'url de success
				$this->checkEntityBeforePersist($data);
				$this->em->persist($data['entite']);
				$this->em->flush();
				if($data['action'] == "create") {
					$data['id'] = $data['entite']->getId();
					unset($data['onSuccess']);
					$this->addContextActionsToData($data);
					$message = $this->get('flash_messages')->send(array(
						'title'		=> 'Saisie enregistrée',
						'type'		=> flashMessage::MESSAGES_SUCCESS,
						'text'		=> 'Le nouvel élément a bien été enregistré.',
					));
				} else {
					$message = $this->get('flash_messages')->send(array(
						'title'		=> 'Saisie enregistrée',
						'type'		=> flashMessage::MESSAGES_SUCCESS,
						'text'		=> 'Les modification de cet élément ont bien été enregistrées.',
					));
				}
				if(isset($data['onSuccess'])) return $this->redirect($data['onSuccess']);
			} else {
				// formulaire invalide -> url echec
				$message = $this->get('flash_messages')->send(array(
					'title'		=> 'Erreurs de saisie',
					'type'		=> flashMessage::MESSAGES_ERROR,
					'text'		=> 'La saisie de vos données contient des erreurs. Veuillez les corriger, svp.',
				));
				if(isset($data['onError'])) {
					if(is_string($data['onError'])) return $this->redirect($data['onError']);
				}
				// retour au formulaire…
				$template = 'siteadminBundle:entites:'.$data['entite_name'].ucfirst($data['action']).'.html.twig';
				if(!$this->get('templating')->exists($template)) {
					$template = 'siteadminBundle:entites:'.'entite'.ucfirst($data['action']).'.html.twig';
				}
				$data[$data['action'].'_form'] = $form->createView();
				return $this->render($template, $data);
			}
		}
		return $this->redirect($this->generateUrl('siteadmin_entite', $data['entite_name']));
	}

	protected function checkEntityBeforePersist(&$data) {
		switch ($data['entite_name']) {
			case 'reseau':
				// retrait des articles liés
				break;
			
			default:
				# code...
				break;
		}
	}

	/**
	 * Renvoie la vue du formulaire de l'entité $entite
	 * @param object $entite
	 * @param string $action
	 * @param array $data
	 * @return Symfony\Component\Form\FormView
	 */
	public function getEntityFormView(&$data) {
		return $this->getEntityForm($data, true);
	}

	/**
	 * Renvoie le formulaire de l'entité $entite
	 * @param object $entite
	 * @param string $action
	 * @param array $data
	 * @return Symfony\Component\Form\Form
	 */
	public function getEntityForm(&$data, $getViewNow = false) {
		if(!is_array($data)) throw new Exception("getEntityForm : data doit être défini !", 1);
		$types_valid = array('create', 'edit', 'copy', 'delete');
		if(!in_array($data['action'], $types_valid)) {
			// throw new Exception("Action ".$action." invalide, doit être ".json_encode($types_valid, true), 1);
			throw new Exception("getEntityForm => type d'action invalide : ".$data['action'], 1);
		}
		// récupère les directions en fonction des résultats
		$this->addContextActionsToData($data);
		$viewForm = false;
		$form = false;
		// define Type
		$entiteType = str_replace('Entity', 'Form', $data['classname'].'Type');
		switch ($data['action']) {
			case 'create':
				$form = $this->createForm(new $entiteType($this, $data), $data['entite']);
				break;
			case 'edit':
				$form = $this->createForm(new $entiteType($this, $data), $data['entite']);
				break;
			case 'delete':
				# code...
				break;
			case 'copy':
				if(method_exists($data['entite'], 'getClone')) {
					$data['entite'] = $data['entite']->getClone();
				} else {
					// copie impossible
					$message = $this->get('flash_messages')->send(array(
						'title'		=> 'Copie impossible',
						'type'		=> flashMessage::MESSAGES_ERROR,
						'text'		=> 'Cet élément <strong>"'.$data['entite']->getNom().'"</strong> ne peut être copié.',
					));
				}
				$form = $this->createForm(new $entiteType($this, $data), $data['entite']);
				break;
			
			default:
				$message = $this->get('flash_messages')->send(array(
					'title'		=> 'Erreur formulaire',
					'type'		=> flashMessage::MESSAGES_ERROR,
					'text'		=> 'Ce type de formulaire <strong>"'.$type.'"</strong> n\'est pas reconnu.',
				));
				break;
		}
		if($form != false) $viewForm = $form->createView();
		if($getViewNow) return $viewForm;
		return $form;
	}

	/**
	 * Renvoie les url selon résultats (pour formulaires)
	 * @param array $data = null
	 * @return array
	 */
	protected function addContextActionsToData(&$data) {
		if(!is_array($data)) throw new Exception("addContextActionsToData : data doit être défini !", 1);
		switch ($data['action']) {
			case 'create':
			case 'edit':
				if(!isset($data['form_action'])) {
					$data['form_action'] = $this->generateUrl('siteadmin_form_action', array(
						'classname'	=> $data['classname'],
						), true);
				}
				if(!isset($data['onSuccess'])) {
					if($data['type']['type_related'] != null) {
						$data['onSuccess'] = $this->generateUrl('siteadmin_entite_type', array(
							'entite'		=> $data['entite_name'],
							'type_related'	=> $data['type']['type_related'],
							'type_field'	=> $data['type']['type_field'],
							'type_values'	=> $this->typeValuesToString($data['type']['type_values']),
							'action'		=> 'show',
							'id'			=> $data['entite']->getId(),
							), true);
					} else {
						$data['onSuccess'] = $this->generateUrl('siteadmin_entite', array(
							'entite'	=> $data['entite_name'],
							'id'		=> $data['entite']->getId(),
							'action'	=> 'show',
							), true);
					}
				}
				if(!isset($data['onError'])) {
					$data['onError'] = null;
				}
				break;
			case 'copy':
				if(!isset($data['form_action'])) {
					$data['form_action'] = $this->generateUrl('siteadmin_form_action', array(
						'classname'	=> $data['classname'],
						), true);
				}
				if(!isset($data['onSuccess'])) {
					$data['onSuccess'] = $this->generateUrl('siteadmin_entite', array(
						'entite'	=> $data['entite_name'],
						'id'		=> null,
						'action'	=> 'show',
						), true);
				}
				if(!isset($data['onError'])) {
					$data['onError'] = null;
				}
				break;
			case 'delete':
				if(!isset($data['form_action'])) {
					$data['form_action'] = $this->generateUrl('siteadmin_form_action', array(
						'classname'	=> $data['classname'],
						), true);
				}
				if(!isset($data['onSuccess'])) {
					$data['onSuccess'] = $this->generateUrl('siteadmin_entite', array(
						'entite'	=> $data['entite_name'],
						), true);
				}
				if(!isset($data['onError'])) {
					$data['onError'] = $this->generateUrl('siteadmin_entite', array(
						'entite'	=> $data['entite_name'],
						'id'		=> $data['entite']->getId(),
						'action'	=> 'show',
						), true);
				}
				break;
			
			default:
				# code...
				break;
		}
		// return $data;
	}

	public function pageweb_as_defaultAction($id, $redir) {
		$this->em = $this->getDoctrine()->getManager();
		$this->repo = $this->em->getRepository('site\adminBundle\Entity\pageweb');
		// entité à mettre en page web par défaut
		$page = $this->repo->find($id);
		if(!is_object($page)) {
			$message = $this->get('flash_messages')->send(array(
				'title'		=> 'Page web introuvable',
				'type'		=> flashMessage::MESSAGES_ERROR,
				'text'		=> 'Cette page <strong>#"'.$id.'"</strong> n\'a pu être touvée',
			));
		} else {
			$page->setHomepage(true);
			$this->em->persist($page);
			// on passe les autres pages en false s'il en existe
			$pages = $this->repo->findByHomepage(true);
			if(count($pages) > 0) foreach ($pages as $onepage) {
				$onepage->setHomepage(false);
			}
			$this->em->flush();
		}
		return $this->redirect(urldecode($redir));
	}



}
