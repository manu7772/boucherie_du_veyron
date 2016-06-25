<?php

namespace site\adminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use site\adminBundle\Entity\magasin;
use site\adminBundle\Form\magasinType;
use site\adminBundle\Entity\reseau;
use site\adminBundle\Form\reseauType;
use site\adminBundle\Entity\marque;
use site\adminBundle\Form\marqueType;

use site\adminBundle\services\flashMessage;

use \Exception;

/**
 * baseController
 * @Security("has_role('ROLE_TRANSLATOR')")
 */
class baseController extends Controller {

	const TYPE_SELF 			= '_self';
	const DEFAULT_ACTION 		= 'list';
	const LIST_ACTION 			= 'list';
	const SHOW_ACTION 			= 'show';
	const CREATE_ACTION 		= 'create';
	const EDIT_ACTION 			= 'edit';
	const COPY_ACTION 			= 'copy';
	const DELETE_ACTION 		= 'delete';
	const ACTIVE_ACTION 		= 'active';
	const CHECK_ACTION 			= 'check';
	const TYPE_VALUE_JOINER 	= '___';
	const BUNDLE				= 'siteadmin';
	const LIST_REPO_METHOD		= 'findForList';
	const LIST_REPO_DEFAULT		= 'findAll';

	protected $bundle 			= null;

	public function getBundle() {
		if($this->bundle == null) {
			if($this->container->hasParameter('siteadminBundle')) $this->bundle = $this->getParameter('siteadminBundle');
			// echo('<p>Bundle system : '.$this->bundle.'</p>');
			if($this->bundle == null) $this->bundle = self::BUNDLE;
			// echo('<p>Bundle selected : '.$this->bundle.'</p>');
		}
		// else echo('<p>---</p>');
		return $this->bundle;
	}

	/**
	 * Renvoie le template selon le bundle décrit dans config.yml
	 * Si le bundle décrit est absent ou le template absent, utilise siteadminBundle par défaut
	 * @param string $view
	 * @param array $parameters = array()
	 * @param Response $response = null
	 * @return Response
	 */
	public function render($view, array $parameters = array(), Response $response = null) {
		$view = explode(':', $view, 2)[1];
		$bundle = $this->getBundle();
		// echo('<p>Class : '.get_called_class().'</p>');
		if($this->get('templating')->exists($bundle."Bundle:".$view)) return parent::render($bundle."Bundle:".$view, $parameters, $response);
		if($this->get('templating')->exists(self::BUNDLE."Bundle:".$view)) return parent::render(self::BUNDLE."Bundle:".$view, $parameters, $response);
		return $this->redirect($this->generateUrl('siteadmin_homepage'));
	}

	/**
	 * Get service de l'entité (ou le parent le plus proche, jusqu'à aeEntity en dernier recours)
	 * @param string $entity
	 * @return object
	 */
	public function getEntityService($entity) {
		$ES = $this->get('aetools.aeEntity');
		if($ES->entityClassExists($ES->getEntityClassName($entity)))
			return $ES->getEntityService($entity);
			else return $ES;
	}


	protected function typeValuesToArray($type_values = null) {
		if($type_values != null) $type_values = explode(self::TYPE_VALUE_JOINER, $type_values);
		return $type_values;
	}
	protected function typeValuesToString($type_values = null) {
		if($type_values != null) $type_values = implode(self::TYPE_VALUE_JOINER, $type_values);
		return $type_values;
	}

	protected function fillEntityWithData(&$data) {
		$service = $this->getEntityService($data['entite']);
		if(is_array($data['type']['type_values']) && count($data['type']['type_values']) > 0) {
			switch ($data['type']['type_related']) {
				case self::TYPE_SELF:
					// field
					if(!$service->hasAssociation($data['type']['type_field'], $data['entite'])) {
						$set = $service->getMethodOfSetting($data['type']['type_field'], $data['entite'], false);
						if(is_string($set)) {
							// ok setter
							if(preg_match('#^set#', $set)) $data['entite']->$set(reset($data['type']['type_values']));
							if(preg_match('#^add#', $set)) foreach($data['type']['type_values'] as $value) {
								$data['entite']->$set($value);
							}
						}
					} else {
						throw new Exception("Ce champ est de type association : field ".json_encode($data['type']['type_field'])." fourni !", 1);
					}
					break;
				default:
					// association
					$set = $service->getMethodOfSetting($data['type']['type_field'], $data['entite'], false);
					$related = explode(self::TYPE_VALUE_JOINER, $data['type']['type_related'], 2);
					if(is_string($set) && count($related) == 2) {
						// ok setter
						$related_service = $this->getEntityService($related[0]);
						foreach ($data['type']['type_values'] as $value) {
							// find relateds
							$findMethod = $related_service->getMethodNameWith($related[1], 'findBy');
							if($findMethod != false) {
								$related_objects = $related_service->getRepo()->$findMethod($value);
								if(count($related_objects) > 0) $data['entite']->$set($related_objects);
							}
						}
					} else {
						throw new Exception("Ce champ n'a pas d'association : field ".json_encode($data['type']['type_field'])." fourni ! ".json_encode($set).". ".json_encode($related).".", 1);
					}
					break;
			}
		}
		return $data;
	}

	/**
	 * Renvoie les url selon résultats (pour formulaires)
	 * @param array $data = null
	 * @return array
	 */
	protected function addContextActionsToData(&$data) {
		if(!is_array($data)) throw new Exception("addContextActionsToData : data doit être défini !", 1);
		switch ($data['action']) {
			case 'delete_linked_image':
				if(!isset($data['form_action'])) {
					$data['form_action'] = $this->generateUrl('siteadmin_form_action', array(
						'classname'	=> $data['entite_name'],
						), true);
				}
				if(!isset($data['onSuccess'])) {
					if($data['type']['type_related'] != null) {
						$data['onSuccess'] = $this->generateUrl('siteadmin_entite_type', array(
							'entite'		=> $data['entite_name'],
							'type_related'	=> $data['type']['type_related'],
							'type_field'	=> $data['type']['type_field'],
							'type_values'	=> $this->typeValuesToString($data['type']['type_values']),
							'action'		=> self::SHOW_ACTION,
							'id'			=> $data['entite']->getId(),
							), true);
					} else {
						$data['onSuccess'] = $this->generateUrl('siteadmin_entite', array(
							'entite'	=> $data['entite_name'],
							'id'		=> $data['entite']->getId(),
							'action'	=> self::SHOW_ACTION,
							), true);
					}
				}
				if(!isset($data['onError'])) {
					$data['onError'] = null;
				}
				break;
			case self::CREATE_ACTION:
				if(!isset($data['form_action'])) {
					$data['form_action'] = $this->generateUrl('siteadmin_form_action', array(
						'classname'	=> $data['entite_name'],
						), true);
				}
				if(!isset($data['onSuccess'])) {
					if($data['type']['type_related'] != null) {
						$data['onSuccess'] = $this->generateUrl('siteadmin_entite_type', array(
							'entite'		=> $data['entite_name'],
							'type_related'	=> $data['type']['type_related'],
							'type_field'	=> $data['type']['type_field'],
							'type_values'	=> $this->typeValuesToString($data['type']['type_values']),
							'action'		=> self::SHOW_ACTION,
							'id'			=> $data['entite']->getId(),
							), true);
					} else {
						$data['onSuccess'] = $this->generateUrl('siteadmin_entite', array(
							'entite'	=> $data['entite_name'],
							'id'		=> $data['entite']->getId(),
							'action'	=> self::SHOW_ACTION,
							), true);
					}
				}
				if(!isset($data['onError'])) {
					$data['onError'] = null;
				}
			case self::EDIT_ACTION:
				if(!isset($data['form_action'])) {
					$data['form_action'] = $this->generateUrl('siteadmin_form_action', array(
						'classname'	=> $data['entite_name'],
						), true);
				}
				if(!isset($data['onSuccess'])) {
					if($data['type']['type_related'] != null) {
						$data['onSuccess'] = $this->generateUrl('siteadmin_entite_type', array(
							'entite'		=> $data['entite_name'],
							'type_related'	=> $data['type']['type_related'],
							'type_field'	=> $data['type']['type_field'],
							'type_values'	=> $this->typeValuesToString($data['type']['type_values']),
							'action'		=> self::LIST_ACTION,
							// 'id'			=> $data['entite']->getId(),
							), true);
					} else {
						$data['onSuccess'] = $this->generateUrl('siteadmin_entite', array(
							'entite'	=> $data['entite_name'],
							// 'id'		=> $data['entite']->getId(),
							'action'	=> self::LIST_ACTION,
							), true);
					}
				}
				if(!isset($data['onError'])) {
					$data['onError'] = null;
				}
				break;
			case self::COPY_ACTION:
				if(!isset($data['form_action'])) {
					$data['form_action'] = $this->generateUrl('siteadmin_form_action', array(
						'classname'	=> $data['entite_name'],
						), true);
				}
				if(!isset($data['onSuccess'])) {
					$data['onSuccess'] = $this->generateUrl('siteadmin_entite', array(
						'entite'	=> $data['entite_name'],
						'id'		=> null,
						'action'	=> self::SHOW_ACTION,
						), true);
				}
				if(!isset($data['onError'])) {
					$data['onError'] = null;
				}
				break;
			case self::DELETE_ACTION:
				if(!isset($data['form_action'])) {
					$data['form_action'] = $this->generateUrl('siteadmin_form_action', array(
						'classname'	=> $data['entite_name'],
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
						'action'	=> self::SHOW_ACTION,
						), true);
				}
				break;
			
			default:
				if(!isset($data['form_action'])) {
					$data['form_action'] = $this->generateUrl('siteadmin_form_action', array(
						'classname'	=> $data['entite_name'],
						), true);
				}
				break;
		}
		// return $data;
	}

	/**
	 * Envoie un flash message après persist/update d'une entité
	 * @param array $data
	 */
	protected function getSuccessPersistFlashMessage($data) {
		$nom = $data['entite']->getId();
		if(method_exists($data['entite'], 'getName')) $nom = $data['entite']->getName();
		if(method_exists($data['entite'], 'getNom')) $nom = $data['entite']->getNom();
		if($data['action'] == "create") {
			$this->get('flash_messages')->send(array(
				'title'		=> 'Saisie enregistrée',
				'type'		=> flashMessage::MESSAGES_SUCCESS,
				'text'		=> 'Le nouvel élément "'.$nom.'" a bien été enregistré.',
			));
		} else {
			$this->get('flash_messages')->send(array(
				'title'		=> 'Saisie enregistrée',
				'type'		=> flashMessage::MESSAGES_SUCCESS,
				'text'		=> 'Les modification "'.$nom.'" ont bien été enregistrées.',
			));
		}
	}

	/**
	 * Renvoie la vue du formulaire de l'entité $entite
	 * @param object $entite
	 * @param string $action
	 * @param array $data
	 * @return Symfony\Component\Form\FormView
	 */
	public function getEntityFormView(&$data, $typeType = null) {
		return $this->getEntityForm($data, $typeType, true);
	}

	/**
	 * Renvoie le formulaire de l'entité $entite
	 * @param object $entite
	 * @param string $action
	 * @param array $data
	 * @return Symfony\Component\Form\Form
	 */
	public function getEntityForm(&$data, $typeType = null, $getViewNow = false) {
		if(!is_array($data)) throw new Exception("getEntityForm : data doit être défini !", 1);
		$types_valid = array(self::LIST_ACTION, self::SHOW_ACTION, self::CREATE_ACTION, self::EDIT_ACTION, self::COPY_ACTION, self::DELETE_ACTION);
		if(!in_array($data['action'], $types_valid)) {
			// throw new Exception("Action ".$action." invalide, doit être ".json_encode($types_valid, true), 1);
			throw new Exception("getEntityForm => type d'action invalide : ".$data['action'], 1);
		}
		// récupère les directions en fonction des résultats
		$this->addContextActionsToData($data);
		$form = false;
		// define Type
		$baseClassType = str_replace('Entity', 'Form', preg_replace('#'.$data['entite_name'].'$#', '', $data['classname']));
		$entiteType = $typeType;
		if(!is_string($typeType)) {
			// Type non fourni => Type selon nom de l'entité
			$entiteType = $baseClassType.$data['entite_name'].'Type';
		} else if(!preg_match('#^(.+\\.+)+$#', $typeType)) {
			// Type fourni => vérification si classname complet fourni
			$entiteType = $baseClassType.$typeType;
		}
		switch ($data['action']) {
			case self::SHOW_ACTION:
			case self::LIST_ACTION:
			case self::CREATE_ACTION:
				// echo('<p>Nom form : '.$data['entite'].' ('.strlen($data['entite']).' lettres)</p>');
				$form = $this->createForm(new $entiteType($this, $data), $data['entite']);
				break;
			case self::EDIT_ACTION:
				$form = $this->createForm(new $entiteType($this, $data), $data['entite']);
				break;
			case self::COPY_ACTION:
				throw new Exception("Ce formulaire ".$data['action']." n'est pas encore supporté.", 1);
				break;
			case self::DELETE_ACTION:
				throw new Exception("Ce formulaire ".$data['action']." n'est pas encore supporté.", 1);
				break;
			default:
				$this->get('flash_messages')->send(array(
					'title'		=> 'Erreur formulaire',
					'type'		=> flashMessage::MESSAGES_ERROR,
					'text'		=> 'Ce type de formulaire <strong>"'.$type.'"</strong> n\'est pas reconnu.',
				));
				break;
		}
		if($form == false) {
			$this->get('flash_messages')->send(array(
				'title'		=> 'Erreur de génération du formalaire',
				'type'		=> flashMessage::MESSAGES_ERROR,
				'text'		=> 'Le formulaire n\'a pas pu être généré. Veuillez contacter le webmaster.',
			));
		} else {
			if($getViewNow != false) return $form->createView();
		}
		return $form;
	}

}