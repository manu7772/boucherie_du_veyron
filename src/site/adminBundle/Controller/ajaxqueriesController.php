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
 * ajaxqueriesController
 * @Security("has_role('ROLE_EDITOR')")
 */
class ajaxqueriesController extends baseController {

	public function ajaxsortAction() {
		$data = array();
		$request = $this->getRequest();
		$entity = $request->request->get('entity');
		$id = $request->request->get('id');
		// service
		$entityService = $this->getEntityService($entity);

		if($id != null && $request->isXmlHttpRequest()) {
			// AJAX REQUEST
			// $data = $entityService->getRepo()->findArrayTree($id, $types);
			// $this->get('aetools.debug')->debugNamedFile('verifTypesForJSTree', array('Types' => $types, 'Data' => $data), true, false);
			// $icons = $this->get('aetools.textutilities')->iconsAsJson(true);
			// $this->get('aetools.debug')->debugNamedFile('iconsForJSTree', $icons, true, false);
			return new JsonResponse($data);
		} else if(!$request->isXmlHttpRequest()) {
			// TEST EN GET
			return new Response(json_encode($data));
		}
		return new JsonResponse(null);
	}


}
