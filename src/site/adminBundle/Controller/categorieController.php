<?php

namespace site\adminBundle\Controller;

use site\adminBundle\Controller\baseController;
// use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use site\adminBundle\services\flashMessage;

use \Exception;

/**
 * categorieController
 * @Security("has_role('ROLE_EDITOR')")
 */
class categorieController extends baseController {

	const ENTITE_NAME = 'site\adminBundle\Entity\categorie';
	const DEFAULT_LEVEL = 1;

	protected $types = array('level', 'accept');

	public function ajaxDataAction($id = null) {
		$request = $this->getRequest();
		$entityService = $this->getEntityService('categorie');
		if($id == null) {
			$id = $request->request->get('id');
		}
		if($id != null) {
			$data = $entityService->getRepo()->findArrayTree($id);
			// echo('<pre>');
			// var_dump($data);
			// die('</pre>');
			return new JsonResponse($data);
		}
		return new JsonResponse('Error');
	}

	// public function categorieAction($action = null, $type = null, $value = null, $id = null) {
	// 	if($action == null) $action = self::DEFAULT_ACTION;
	// 	$data['action'] = $action;
	// 	$data['type']['type'] = $type;
	// 	$data['type']['value'] = $value;
	// 	$data['id'] = $id;

	// 	$entityService = $this->getEntityService(self::ENTITE_NAME);

	// 	// page générique entités
	// 	switch ($data['action']) {
	// 		case 'create' :
	// 			break;
	// 		case 'show' :
	// 			$data['entite'] = $entityService->getRepo($data['classname'])->find($id);
	// 			if(!is_object($data['entite'])) {
	// 				// $this->get('aetools.aeExceptions')->launchException('not_found', null, $data['entite_name']);
	// 				$this->get('flash_messages')->send(array(
	// 					'title'		=> 'Élément introuvable',
	// 					'type'		=> flashMessage::MESSAGES_ERROR,
	// 					'text'		=> 'L\'élément est introuvable.',
	// 				));
	// 				$data['action'] = self::DEFAULT_ACTION;
	// 				$data['id'] = null;
	// 			}
	// 			break;
	// 		case 'edit' :
	// 			break;
	// 		case 'check' :
	// 			break;
	// 		case 'delete' :
	// 			break;
	// 		default:
	// 			$data['action'] = self::DEFAULT_ACTION;
	// 			break;
	// 	}

	// 	if($data['action'] == self::DEFAULT_ACTION) {
	// 		// DEFAULT_ACTION
	// 		// findForList par défaut sinon findAll
	// 		$repo = $entityService->getRepo($data['classname']);
	// 		if(method_exists($repo, self::LIST_REPO_METHOD)) $method = self::LIST_REPO_METHOD;
	// 			else $method = self::LIST_REPO_DEFAULT;
	// 		$data['entites'] = $repo->$method();
	// 	}

	// 	$template = 'siteadminBundle:categorie:'.$data['action'].'.html.twig';
	// 	if(!$this->get('templating')->exists($template)) {
	// 		$template = 'siteadminBundle:entites:'.'entite'.ucfirst($data['action']).'.html.twig';
	// 		if(!$this->get('templating')->exists($template)) {
	// 			return $this->redirect($this->generateUrl('siteadmin_homepage'));
	// 		}
	// 	}
	// 	// return
	// 	return $this->render($template, $data);
	// }



}
