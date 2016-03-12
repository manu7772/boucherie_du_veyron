<?php

namespace site\adminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use site\adminBundle\services\flashMessage;

use \Exception;

/**
 * entiteController
 * @Security("has_role('ROLE_EDITOR')")
 */
class entiteController extends Controller {

	const TYPE_SELF 			= '_self';
	const DEFAULT_ACTION 		= 'list';
	const TYPE_VALUE_JOINER 	= '___';

	protected $entityService;

	protected function getEntiteData($entite, $type_related = null, $type_field = null, $type_values = null, $action = null, $id = null) {
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
		$this->entityService = $this->get('aetools.aeEntities');
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
		$entityService = $this->getEntityService($data['entite_name']);

		// page générique entités
		switch ($data['action']) {
			case 'create' :
				$data['entite'] = $entityService->getNewEntity($data['classname']);
				$data[$data['action'].'_form'] = $this->getEntityFormView($data);
				if($data[$data['action'].'_form'] == false) {
					// erreur formulaire
					$data['action'] = self::DEFAULT_ACTION;
					$data['id'] = null;
				}
				break;
			case 'show' :
				$data['entite'] = $entityService->getRepo($data['classname'])->find($id);
				if(!is_object($data['entite'])) {
					// $this->get('aetools.aeExceptions')->launchException('not_found', null, $data['entite_name']);
					$this->get('flash_messages')->send(array(
						'title'		=> 'Élément introuvable',
						'type'		=> flashMessage::MESSAGES_ERROR,
						'text'		=> 'L\'élément est introuvable.',
					));
					$data['action'] = self::DEFAULT_ACTION;
					$data['id'] = null;
				}
				break;
			case 'edit' :
				set_time_limit(300);
				$memory = $this->get('aetools.aetools')->getConfigParameters('cropper.yml', 'memory_limit');
				ini_set("memory_limit", $memory);
				$data['entite'] = $entityService->getRepo($data['classname'])->find($id);
				if(!is_object($data['entite'])) {
					// $this->get('aetools.aeExceptions')->launchException('not_found', null, $data['entite_name']);
					$this->get('flash_messages')->send(array(
						'title'		=> 'Élément introuvable',
						'type'		=> flashMessage::MESSAGES_ERROR,
						'text'		=> 'L\'élément est introuvable.',
					));
					$data['action'] = self::DEFAULT_ACTION;
					$data['id'] = null;
				}
				$data[$data['action'].'_form'] = $this->getEntityFormView($data);
				if($data[$data['action'].'_form'] == false) {
					// erreur formulaire
					$data['action'] = self::DEFAULT_ACTION;
					$data['id'] = null;
				}
				break;
			case 'check' :
				// DEFAULT_ACTION
				if($data['type']['type_values'] != null) {
					$data['entites'] = $entityService->getRepo()->findByField($data['type'], self::TYPE_SELF, true);
				} else {
					$data['entites'] = $entityService->getRepo()->findAll();
				}
				break;
			case 'delete' :
				$data['entite'] = $entityService->getRepo()->find($id);
				if(!is_object($data['entite'])) {
					$this->get('flash_messages')->send(array(
						'title'		=> 'Élément introuvable',
						'type'		=> flashMessage::MESSAGES_ERROR,
						'text'		=> 'L\'élément est introuvable et ne peut être supprimé.',
					));
					$data['action'] = null;
					$data['id'] = null;
				} else {
					$entityService->softDeleteEntity($data['entite']);
					$this->get('flash_messages')->send(array(
						'title'		=> 'Élément supprimé',
						'type'		=> flashMessage::MESSAGES_WARNING,
						'text'		=> 'L\'élément a été supprimé.',
					));
					$data['action'] = null;
					$data['id'] = null;
				}
				return $this->redirect($this->generateEntityUrl($data));
				break;
			case 'deleteAllTemp' :
				$entityService->deleteAllTemp($data['entite_name']);
				$this->get('flash_messages')->send(array(
					'title'		=> 'Éléments supprimés',
					'type'		=> flashMessage::MESSAGES_WARNING,
					'text'		=> 'Opération effectuée.',
				));
				$data['action'] = null;
				$data['id'] = null;
				return $this->redirect($this->generateEntityUrl($data));
				break;
			case 'active' :
				$data['entite'] = $entityService->getRepo()->find($id);
				if(!is_object($data['entite'])) {
					$this->get('flash_messages')->send(array(
						'title'		=> 'Élément introuvable',
						'type'		=> flashMessage::MESSAGES_ERROR,
						'text'		=> 'L\'élément est introuvable et ne peut être modifié.',
					));
					$data['action'] = null;
					$data['id'] = null;
				} else {
					$entityService->softActivateEntity($data['entite']);
					$this->get('flash_messages')->send(array(
						'title'		=> 'Élément activé',
						'type'		=> flashMessage::MESSAGES_SUCCESS,
						'text'		=> 'L\'élément a été activé.',
					));
					$data['action'] = self::DEFAULT_ACTION;
				}
				return $this->redirect($this->generateEntityUrl($data));
				break;
			case 'delete_linked_image' :
				$data['entite'] = $entityService->getRepo()->find($id);
				$data['entite']->setImage(null);
				$entityService->save($data['entite']);
				$this->get('flash_messages')->send(array(
					'title'		=> 'Image supprimée',
					'type'		=> flashMessage::MESSAGES_WARNING,
					'text'		=> 'L\'image de '.$data['entite'].' a été supprimée.',
				));
				$data['action'] = 'show';
				return $this->redirect($this->generateEntityUrl($data));
				break;
			default:
				$data['action'] = self::DEFAULT_ACTION;
				break;
		}

		if($data['action'] == self::DEFAULT_ACTION) {
			// DEFAULT_ACTION
			if(isset($data['type']['type_related']) && isset($data['type']['type_field']) && isset($data['type']['type_values'])) {
				// recherche par type
				if(method_exists($entityService->getRepo(), 'findWithField')) {
					$data['entites'] = $entityService->getRepo($data['classname'])->findWithField($data['type']);
				} else throw new Exception("Method \"findWithField\" does not exist in Repository \"".$data['classname']."\"", 1);
			} else {
				// recherche globale
				$data['entites'] = $entityService->getRepo($data['classname'])->findAll();
			}
		}
		
		$template = 'siteadminBundle:entites:'.$entite.ucfirst($data['action']).'.html.twig';
		if(!$this->get('templating')->exists($template)) {
			$template = 'siteadminBundle:entites:'.'entite'.ucfirst($data['action']).'.html.twig';
			if(!$this->get('templating')->exists($template)) {
				$this->redirect($this->generateUrl('siteadmin_homepage'));
			}
		}
		return $this->render($template, $data);
	}

	/**
	 * Renvoie le service de l'entité
	 * Renvoie les services/entités parents dans l'ordre, puis aeEntities par défaut si non trouvé
	 * @param string $entityShortName
	 * @return object
	 */
	protected function getEntityService($entityShortName) {
		$ae = "aetools.ae".ucfirst($entityShortName);
		if($this->has($ae)) {
			$service = $this->get($ae);
		} else {
			$service = $this->get('aetools.aeEntities');
			$service->defineEntity($entityShortName);
		}
		return $service;
	}

	/**
	 * Renvoie une URL selon les paramètres de $data
	 * @param array $data
	 * @return string
	 */
	protected function generateEntityUrl($data) {
		if($data['type']['type_related'] != null) {
			// avec type
			return $this->generateUrl('siteadmin_entite_type', array('entite' => $data['entite_name'], 'type_related' => $data['type_related'], 'type_field' => $data['type_field'], 'type_values' => $this->typeValuesToString($data['type_values']), 'action' => $data['action'], 'id' => $data['id']));
		} else {
			// sans type
			return $this->generateUrl('siteadmin_entite', array('entite' => $data['entite_name'], 'action' => $data['action'], 'id' => $data['id']));
		}
	}

	/**
	 * Actions en retour de formulaire entity
	 * @param string $classname
	 * @param string $action
	 * @param string $id
	 * @return Response
	 */
	public function entitePostFormPageAction($classname) {
		set_time_limit(300);
		$memory = $this->get('aetools.aetools')->getConfigParameters('cropper.yml', 'memory_limit');
		ini_set("memory_limit", $memory);
		$data = array();
		$classname = urldecode($classname);
		$entiteType = str_replace('Entity', 'Form', $classname.'Type');
		$typeTmp = new $entiteType($this);
		// REQUEST
		$request = $this->getRequest();
		// récupération hiddenData
		$req = $request->request->get($typeTmp->getName());
		// if(!isset($req["hiddenData"])) throw new Exception("entitePostFormPageAction : hiddenData absent ! (".$typeTmp->getName().")", 1);
		if(isset($req["hiddenData"])) {
			$data = json_decode(urldecode($req["hiddenData"]), true);
		} 
		// Entity service
		$entityService = $this->getEntityService($data['entite_name']);

		if($data['action'] == "create") {
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
				case 'delete':
					$entityService->softDelete($data['entite']);
					if(isset($data['onSuccess'])) return $this->redirect($data['onSuccess']);
					break;
				default:
					$form = $this->getEntityForm($data);
					$form->bind($request);
					if($form->isValid()) {
						// formulaire valide -> enregistrement -> renvoi à l'url de success
						$entityService->checkAfterChange($data['entite']);
						$save = $entityService->save($data['entite']);
						if($save->getResult() == true) {
							$this->getSuccessPersistFlashMessage($data);
							if(isset($data['onSuccess'])) return $this->redirect($data['onSuccess']);
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
		if(!is_string($typeType)) $typeType = $data['classname'].'Type';
		$entiteType = str_replace('Entity', 'Form', $typeType);
		switch ($data['action']) {
			case 'create':
			case 'edit':
				$form = $this->createForm(new $entiteType($this, $data), $data['entite']);
				break;
			case 'copy':
				throw new Exception("Ce formulaire ".$data['action']." n'est pas encore supporté.", 1);
				break;
			case 'delete':
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
		if($form != false) $viewForm = $form->createView();
			else {
				$this->get('flash_messages')->send(array(
					'title'		=> 'Erreur de génération du formalaire',
					'type'		=> flashMessage::MESSAGES_ERROR,
					'text'		=> 'Le formulaire n\'a pas pu être généré. Veuillez contacter le webmaster.',
				));
			}
		if($getViewNow != false) return $viewForm;
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
			case 'delete_linked_image':
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

	/**
	 * Désigne l'entite comme entite par défaut
	 * @param integer $id
	 * @param string $redir
	 * @return redirectResponse
	 */
	public function entite_as_defaultAction($id, $entite, $redir) {
		$item = $this->get('aetools.aeEntities')->getRepo('site\adminBundle\Entity\\'.$entite)->find($id);
		// entité à mettre par défaut
		if(!is_object($item)) {
			$this->get('flash_messages')->send(array(
				'title'		=> 'Elément introuvable',
				'type'		=> flashMessage::MESSAGES_ERROR,
				'text'		=> 'Cette page <strong>#"'.$id.'"</strong> n\'a pu être touvée',
			));
		} else {
			$this->get('aetools.aeEntities')->setAsDefault($item);
		}
		return $this->redirect(urldecode($redir));
	}


	/**
	 * Envoie un flash message après persist/update d'une entité
	 * @param array $data
	 */
	protected function getSuccessPersistFlashMessage($data) {
		$data['id'] = $data['entite']->getId();
		unset($data['onSuccess']);
		$this->addContextActionsToData($data);
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


}
