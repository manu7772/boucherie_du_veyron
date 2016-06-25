<?php

namespace site\adminBundle\Controller;

use site\adminBundle\Controller\baseController;
// use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use site\adminBundle\services\flashMessage;
use site\adminBundle\Entity\categorie;

use \Exception;

/**
 * entiteController
 * @Security("has_role('ROLE_EDITOR')")
 */
class entiteController extends baseController {


	protected function getEntiteData($entite, $type_related = null, $type_field = null, $type_values = null, $method = null, $params = null, $repository = null, $action = null, $id = null) {
		if($action == null) $action = self::DEFAULT_ACTION;
		if(is_object($entite)) $entite = get_class($entite);
		$data = array();
		$data['sitedata'] = $this->get('aetools.aeSite')->getDefaultSiteData();
		// find names of entity
		$data['entite_name'] = $this->get('aetools.aeEntity')->getEntityShortName($entite);
		$data['classname'] = $this->get('aetools.aeEntity')->getEntityClassName($entite);

		$data['params'] = $params;
		$data['repo']['method']	= $method;
		if($method != null && $repository == null) $repository = $data['classname'];
		$data['repo']['repository']	= $repository;
		$data['type']['type_related']	= $type_related;
		$data['type']['type_field']		= $type_field;
		$data['type']['type_values']	= $this->typeValuesToArray(urldecode($type_values));
		$data['action'] = $action;
		$data['entite'] = false;
		$data['entites'] = array();
		$data['id'] = $id;
		// EM
		// autres éléments…
		// switch ($data['entite_name']) {
		// 	case 'categorie':
		// 		if($this->container->get('kernel')->getEnvironment() == "dev") {
		// 			$data['categories_tree'] = $this->getEntityService('categorie')->getRepo()->findArrayTree(null, 'all');
		// 		}
		// 		$data['roots_list'] = $this->getEntityService('categorie')->getRepo()->findRoots();
		// 		break;
			
		// 	default:
		// 		# code...
		// 		break;
		// }
		// variables diverses
		$data['typeSelf'] = self::TYPE_SELF;
		$data['type_value_joiner'] = self::TYPE_VALUE_JOINER;
		return $data;
	}

	public function entitePageAction($entite, $type_related = null, $type_field = null, $type_values = null, $method = null, $params = null, $repository = null, $action = null, $id = null) {
		if($action == null) $action = self::DEFAULT_ACTION;
		
		$data = $this->getEntiteData($entite, $type_related, $type_field, $type_values, $method, $params, $repository, $action, $id);
		$entityService = $this->getEntityService($data['entite_name']);

		// page générique entités
		switch ($data['action']) {
			case self::CREATE_ACTION :
				// $data['entite'] = $entityService->getNewEntity($data['classname']);
				$classname = $data['classname'];
				$data['entite'] = new $classname();
				// ajout de valeurs si types sont définis…
				$this->fillEntityWithData($data);
				// Get form
				$data[$data['action'].'_form'] = $this->getEntityFormView($data);
				if($data[$data['action'].'_form'] == false) {
					// erreur formulaire
					$data['action'] = self::DEFAULT_ACTION;
					$data['id'] = null;
				}
				break;
			case self::SHOW_ACTION :
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
			case self::EDIT_ACTION :
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
				} else {
					$data[$data['action'].'_form'] = $this->getEntityFormView($data);
					if($data[$data['action'].'_form'] == false) {
						// erreur formulaire
						$this->get('flash_messages')->send(array(
							'title'		=> 'Erreur formulaire',
							'type'		=> flashMessage::MESSAGES_ERROR,
							'text'		=> 'Le formulaire n\'a pas été généré correctement.',
						));
						$data['action'] = self::DEFAULT_ACTION;
						$data['id'] = null;
					}
				}
				break;
			case self::CHECK_ACTION :
				// DEFAULT_ACTION
				if($data['type']['type_values'] != null) {
					$data['entites'] = $entityService->getRepo()->findByField($data['type'], self::TYPE_SELF, true);
				} else {
					$repo = $entityService->getRepo($data['classname']);
					if(method_exists($repo, self::LIST_REPO_METHOD)) $method = self::LIST_REPO_METHOD;
						else $method = self::LIST_REPO_DEFAULT;
					$data['entites'] = $repo->$method();
					// $data['entites'] = $entityService->getRepo()->findAll();
				}
				break;
			case self::DELETE_ACTION :
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
					$entityService->checkAfterChange($data['entite']);
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
			case self::ACTIVE_ACTION :
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
					$entityService->checkAfterChange($data['entite']);
					$this->get('flash_messages')->send(array(
						'title'		=> 'Élément activé',
						'type'		=> flashMessage::MESSAGES_SUCCESS,
						'text'		=> 'L\'élément a été activé.',
					));
					$data['action'] = self::DEFAULT_ACTION;
					$data['id'] = null;
				}
				return $this->redirect($this->generateEntityUrl($data));
				break;
			case 'delete_linked_image' :
				// supprime l'image de l'entité
				$data['entite'] = $entityService->getRepo()->find($id);
				$data['entite']->setImage(null);
				$entityService->checkAfterChange($data['entite']);
				$entityService->save($data['entite']);
				$this->get('flash_messages')->send(array(
					'title'		=> 'Image supprimée',
					'type'		=> flashMessage::MESSAGES_WARNING,
					'text'		=> 'L\'image de '.$data['entite'].' a été supprimée.',
				));
				$data['action'] = self::SHOW_ACTION;
				return $this->redirect($this->generateEntityUrl($data));
				break;
			case 'delete_linked_logo' :
				// supprime le logo de l'entité
				$data['entite'] = $entityService->getRepo()->find($id);
				$data['entite']->setLogo(null);
				$entityService->checkAfterChange($data['entite']);
				$entityService->save($data['entite']);
				$this->get('flash_messages')->send(array(
					'title'		=> 'Logo supprimé',
					'type'		=> flashMessage::MESSAGES_WARNING,
					'text'		=> 'Le logo de '.$data['entite'].' a été supprimé.',
				));
				$data['action'] = self::SHOW_ACTION;
				return $this->redirect($this->generateEntityUrl($data));
				break;
			default:
				$data['action'] = self::DEFAULT_ACTION;
				break;
		}

		// if(isset($data['entite'])) if(is_object($data['entite'])) $entityService->checkAfterChange($data['entite']);

		if($data['action'] == self::DEFAULT_ACTION) {
			// DEFAULT_ACTION
			if(isset($data['type']['type_related']) && isset($data['type']['type_field']) && isset($data['type']['type_values'])) {
				// recherche par type
				if(method_exists($entityService->getRepo(), 'findWithField')) {
					$data['entites'] = $entityService->getRepo($data['classname'])->findWithField($data['type']);
				} else throw new Exception("Method \"findWithField\" does not exist in Repository \"".$data['classname']."\"", 1);
			} else if(isset($data['repo']['method'])) {
				// recherche par repo
				// echo('<p>Repo méthode : '.$method.'</p>');
				$method = $data['repo']['method'];
				$repo = $entityService->getRepo($data['repo']['repository']);
				if(method_exists($repo, $method)) {
					// if($data['params'] != null && !is_array($data['params'])) $data['params'] = array($data['params']);
					if(is_array($data['params'])) {
						switch (count($data['params'])) {
							case 1: $data['entites'] = $repo->$method(reset($data['params'])); break;
							case 2: $data['entites'] = $repo->$method(reset($data['params']), next($data['params'])); break;
							case 3: $data['entites'] = $repo->$method(reset($data['params']), next($data['params']), next($data['params'])); break;
							case 4: $data['entites'] = $repo->$method(reset($data['params']), next($data['params']), next($data['params']), next($data['params'])); break;
							default:
								$data['entites'] = $repo->$method();
								break;
						}
					} else {
						if($data['params'] == null) $data['entites'] = $repo->$method();
							else $data['entites'] = $repo->$method($data['params']);
					}
				} else throw new Exception("Method \"".$method."\" does not exist in Repository \"".$data['repo']['repository']."\"", 1);
			} else {
				// findForList par défaut sinon findAll
				$repo = $entityService->getRepo($data['classname']);
				if(method_exists($repo, self::LIST_REPO_METHOD)) $method = self::LIST_REPO_METHOD;
					else $method = self::LIST_REPO_DEFAULT;
				$data['entites'] = $repo->$method();
			}
		}

		// ajout de données contextuelles (selon entity et action)
		$this->addContextData($data);
		
		$template = 'siteadminBundle:entites:'.$entite.ucfirst($data['action']).'.html.twig';
		if(!$this->get('templating')->exists($template)) {
			$template = 'siteadminBundle:entites:'.'entite'.ucfirst($data['action']).'.html.twig';
			if(!$this->get('templating')->exists($template)) {
				return $this->redirect($this->generateUrl('siteadmin_homepage'));
			}
		}
		return $this->render($template, $data);
	}

	/**
	 * Renvoie une URL selon les paramètres de $data
	 * @param array $data
	 * @return string
	 */
	protected function generateEntityUrl($data) {
		if(isset($data['type']['type_related'])) {
			if($data['type']['type_related'] != null) {
				// avec type
				return $this->generateUrl('siteadmin_entite_type', array('entite' => $data['entite_name'], 'type_related' => $data['type']['type_related'], 'type_field' => $data['type']['type_field'], 'type_values' => $this->typeValuesToString($data['type']['type_values']), 'action' => $data['action'], 'id' => $data['id']));
			}
		}
		// sans type
		return $this->generateUrl('siteadmin_entite', array('entite' => $data['entite_name'], 'action' => $data['action'], 'id' => $data['id']));
	}

	protected function addContextData(&$data) {
		// $entityService = $this->getEntityService($data['entite_name']);
		// switch ($data['entite_name']) {
		// 	case 'categorie':
		// 		$jstreeObjects = $this->get('aetools.aeJstree');
		// 		if($data['action'] == self::LIST_ACTION) {
		// 			$rootParents = array();
		// 			foreach ($data['entites'] as $entite) {
		// 				// treeview
		// 				$jstreeObjects->createNew($entite);
		// 				// rootparents
		// 				if(is_object($entite->getRootParent(1))) 
		// 					$rootParents[$entite->getId()] = $entite->getRootParent(1);
		// 			}
		// 			if(count($rootParents) < 1) $rootParents = null;
		// 				else $rootParents = reset($rootParents);
		// 			// formulaire création new
		// 			$data['new_entite'] = $entityService->getNewEntity($data['classname']);
		// 			if($rootParents != null) {
		// 				$data['new_entite']->addParent($rootParents);
		// 			}
		// 			// $data['type']['type_field']
		// 			// $data['type']['type_values']

		// 			// $data['form_pre_create'] =
		// 				// $this->getEntityFormView($data, 'preCategorieType');
		// 				// $this->createFormBuilder('preCategorieType', array())
		// 					// ->setAction($this->generateUrl('siteadmin_entite', array('entite' => $data['entite_name'], 'action' => self::CREATE_ACTION)))
		// 					// ->add('input', 'text', array(
		// 					// 	'label' => 'form.nom',
		// 					// 	'translation_domain' => 'messages',
		// 					// 	))
		// 					// ->add('submit', 'submit', array(
		// 					// 	'attr' => array('class' => 'btn-danger btn-outline', 'label' => self::CREATE_ACTION),
		// 					// 	))
		// 					// ->getForm()
		// 					// ->createView()
		// 					;
		// 		} else if($data['action'] == self::SHOW_ACTION) {
		// 			$jstreeObjects->createNew($data['entite']);
		// 		}
		// 		$data['jstreeObjects'] = $jstreeObjects;
		// 		break;
		// 	default:
		// 		# code...
		// 		break;
		// }
		return $data;
	}

		// return $this->createFormBuilder(null, array('attr' => array('style' => 'display: inline;')))
		// 	->setAction($this->generateUrl('siteadmin_entite', array('id' => $id)))
		// 	->setMethod('DELETE')
		// 	->add('submit', 'submit', array(
		// 		'label' => '<i class="fa fa-times"></i> '.$this->get('translator')->trans($texte),
		// 		'attr' => array(
		// 			'class' => $class,
		// 			'data-name' => $name,
		// 			)
		// 		))
		// 	->getForm()
		// ;

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
		$classname = $this->get('aetools.aeEntity')->getEntityClassName($classname);
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
			throw new Exception('Données "hiddenData" manquantes !', 1);
		}
		if(count($data) < 1) throw new Exception('Données "hiddenData" vides !', 1);
		// echo('<pre>');
		// var_dump($data);
		// echo('</pre>');
		// $data['sitedata'] = $this->get('aetools.aeSite')->getDefaultSiteData();
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
				case self::DELETE_ACTION:
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
							// ENREGISTREMENT OK
							$data['id'] = $data['entite']->getId();
							unset($data['onSuccess']);
							$this->addContextActionsToData($data);
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
	 * Désigne l'entite comme entite par défaut
	 * @param integer $id
	 * @param string $redir
	 * @return redirectResponse
	 */
	public function entite_as_defaultAction($id, $entite, $redir) {
		$item = $this->get('aetools.aeEntity')->getRepo('site\adminBundle\Entity\\'.$entite)->find($id);
		// entité à mettre par défaut
		if(!is_object($item)) {
			$this->get('flash_messages')->send(array(
				'title'		=> 'Elément introuvable',
				'type'		=> flashMessage::MESSAGES_ERROR,
				'text'		=> 'Cette page <strong>#"'.$id.'"</strong> n\'a pu être touvée',
			));
		} else {
			$this->get('aetools.aeEntity')->setAsDefault($item);
		}
		return $this->redirect(urldecode($redir));
	}



}
