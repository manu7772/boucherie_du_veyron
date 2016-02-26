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
		$this->em = $this->entityService->getEm();
		$this->repo = $this->entityService->getRepo($data['classname']);
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
		// $this->em = $this->getDoctrine()->getManager();
		// $this->repo = $this->em->getRepository($data['classname']);
		// actions selon entité…
		switch ($data['entite_name']) {
			// case 'une entité quelconque à traiter de manière particulière…':
			// 	break;

			default:
				// page générique entités
				switch ($data['action']) {
					case 'create' :
						$data['entite'] = $this->getNewEntity($data['classname']);
						$data[$data['action'].'_form'] = $this->getEntityFormView($data);
						break;
					case 'show' :
						$data['entite'] = $this->repo->find($id);
						if(!is_object($data['entite'])) throw new Exception($data['entite_name'].'.not_found', 1);
						break;
					case 'edit' :
						$data['entite'] = $this->repo->find($id);
						if(!is_object($data['entite'])) throw new Exception($data['entite_name'].'.not_found', 1);
						$data[$data['action'].'_form'] = $this->getEntityFormView($data);
						break;
					case 'check' :
						// DEFAULT_ACTION
						if($data['type']['type_values'] != null) {
							$data['entites'] = $this->repo->findByField($data['type'], self::TYPE_SELF, true);
						} else {
							$data['entites'] = $this->repo->findAll();
						}
						break;
					case 'delete' :
						$data['entite'] = $this->repo->find($id);
						if(!is_object($data['entite'])) {
							$message = $this->get('flash_messages')->send(array(
								'title'		=> 'Élément introuvable',
								'type'		=> flashMessage::MESSAGES_ERROR,
								'text'		=> 'L\'élément est introuvable et ne peut être supprimé.',
							));
							$data['action'] = null;
							$data['id'] = null;
						} else {
							$this->get('aetools.aeEntities')->softDeleteEntity($data['entite']);
							$message = $this->get('flash_messages')->send(array(
								'title'		=> 'Élément supprimé',
								'type'		=> flashMessage::MESSAGES_WARNING,
								'text'		=> 'L\'élément a été supprimé.',
							));
							$data['action'] = null;
							$data['id'] = null;
						}
						return $this->redirect($this->generateEntityUrl($data));
						break;
					case 'active' :
						$data['entite'] = $this->repo->find($id);
						if(!is_object($data['entite'])) {
							$message = $this->get('flash_messages')->send(array(
								'title'		=> 'Élément introuvable',
								'type'		=> flashMessage::MESSAGES_ERROR,
								'text'		=> 'L\'élément est introuvable et ne peut être modifié.',
							));
							$data['action'] = null;
							$data['id'] = null;
						} else {
							$this->get('aetools.aeEntities')->softActivateEntity($data['entite']);
							$message = $this->get('flash_messages')->send(array(
								'title'		=> 'Élément activé',
								'type'		=> flashMessage::MESSAGES_SUCCESS,
								'text'		=> 'L\'élément a été activé.',
							));
							$data['action'] = 'list';
						}
						return $this->redirect($this->generateEntityUrl($data));
						break;
					default:
						// DEFAULT_ACTION
						if(isset($data['type']['type_related']) && isset($data['type']['type_field']) && isset($data['type']['type_values'])) {
							// recherche par type
							if(method_exists($this->repo, 'findWithField')) {
								$data['entites'] = $this->repo->findWithField($data['type']);
							} else throw new Exception("Method \"findWithField\" does not exist in Repository \"".$data['classname']."\"", 1);
						} else {
							// recherche globale
							$data['entites'] = $this->repo->findAll();
						}
						break;
				}
				break;
		}
		
		$template = 'siteadminBundle:entites:'.$entite.ucfirst($data['action']).'.html.twig';
		if(!$this->get('templating')->exists($template)) {
			$template = 'siteadminBundle:entites:'.'entite'.ucfirst($data['action']).'.html.twig';
		}
		return $this->render($template, $data);
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
		} else {
			// 
		}
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
				'type'		=> flashMessage::MESSAGES_ERROR,
				'text'		=> 'L\'entité "'.$data['entite_name'].'"'.$imsg.' n\'a pas été trouvée.',
			));
		} else {
			switch ($data['action']) {
				case 'delete':
					$this->get('aetools.aeEntities')->softDelete($data['entite']);
					$this->em->flush();
					if(isset($data['onSuccess'])) return $this->redirect($data['onSuccess']);
					break;
				
				default:
					$form = $this->getEntityForm($data);
					$form->bind($request);
					if($form->isValid()) {
						// formulaire valide -> enregistrement -> renvoi à l'url de success
						// 
						$this->em->persist($data['entite']);
						$this->em->flush();
						$this->checkEntityAfterPersist($data);
						if($data['action'] == "create") {
							$data['id'] = $data['entite']->getId();
							unset($data['onSuccess']);
							$this->addContextActionsToData($data);
							$nom = $data['entite']->getId();
							if(method_exists($data['entite'], 'getName')) $nom = $data['entite']->getName();
							if(method_exists($data['entite'], 'getNom')) $nom = $data['entite']->getNom();
							$message = $this->get('flash_messages')->send(array(
								'title'		=> 'Saisie enregistrée',
								'type'		=> flashMessage::MESSAGES_SUCCESS,
								'text'		=> 'Le nouvel élément "'.$nom.'" a bien été enregistré.',
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
					break;
			}
		}
		return $this->redirect($this->generateUrl('siteadmin_entite', $data['entite_name']));
	}

	/**
	 * Check d'une entite avant de la persister
	 */
	protected function checkEntityAfterPersist(&$data) {
		$launchInverse = true;
		// lance le checkAfterChange
		$serviceName = 'aetools.ae'.ucfirst($data['entite_name']);
		if($this->has($serviceName)) {
			// et si la méthode existe…
			$checkMethod = 'checkAfterChange';
			$entityService = $this->get($serviceName);
			if(method_exists($entityService, $checkMethod)) {
				$entityService->$checkMethod($data['entite']);
				$launchInverse = false;
			}
		}
		// !!! vérifie les liens inverses !!! (si le service n'a pu être lancé)
		if($launchInverse == true) {
			$this->get('aetools.aeEntities')->checkInversedLinks($data['entite'], true);
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

	protected function getNewEntity($classname) {
		$newEntity = new $classname();
		$this->get('aetools.aeEntities')->fillAllAssociatedFields($newEntity);
		// $this->em = $this->getDoctrine()->getManager();
		// if(method_exists($newEntity, 'setStatut')) {
		// 	// si un champ statut existe
		// 	$defaultStatut = $this->em->getRepository('site\adminBundle\Entity\statut')->defaultVal();
		// 	if(is_array($defaultStatut)) $defaultStatut = reset($defaultStatut);
		// 	if(is_object($defaultStatut)) $newEntity->setStatut($defaultStatut);
		// }
		// if(method_exists($newEntity, 'setTauxTva')) {
		// 	// si un champ statut existe
		// 	$defaultTauxTva = $this->em->getRepository('site\adminBundle\Entity\tauxTva')->defaultVal();
		// 	if(is_array($defaultTauxTva)) $defaultTauxTva = reset($defaultTauxTva);
		// 	if(is_object($defaultTauxTva)) $newEntity->setTauxTva($defaultTauxTva);
		// }
		return $newEntity;
	}

	/**
	 * Désigne l'entite' comme entite par défaut
	 * @param integer $id
	 * @param string $redir
	 * @return redirectResponse
	 */
	public function entite_as_defaultAction($id, $entite, $redir) {
		$this->em = $this->getDoctrine()->getManager();
		$this->repo = $this->em->getRepository('site\adminBundle\Entity\\'.$entite);
		// entité à mettre en page web par défaut
		$item = $this->repo->find($id);
		if(!is_object($item)) {
			$message = $this->get('flash_messages')->send(array(
				'title'		=> 'Elément introuvable',
				'type'		=> flashMessage::MESSAGES_ERROR,
				'text'		=> 'Cette page <strong>#"'.$id.'"</strong> n\'a pu être touvée',
			));
		} else {
			$item->setDefault(true);
			// $this->em->persist($page);
			// on passe les autres entites en false s'il en existe
			$items = $this->repo->findByDefault(true);
			if(count($items) > 0) foreach ($items as $oneItem) {
				$oneItem->setDefault(false);
			}
			$this->em->flush();
			$message = $this->get('flash_messages')->send(array(
				'title'		=> 'Par défaut',
				'type'		=> flashMessage::MESSAGES_SUCCESS,
				'text'		=> 'L\'élément "'.$item->getNom().'" a été défini comme élément par défaut.',
			));
		}
		return $this->redirect(urldecode($redir));
	}



}
