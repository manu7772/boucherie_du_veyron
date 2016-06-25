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
		$request = $this->getRequest();
		$data = array();
		if($request->isXmlHttpRequest()) {
			// AJAX REQUEST
			$data = $request->request->all();
			if(isset($data['entity']) && isset($data['children']) && isset($data['group'])) {
				// DATA OK
				$entityService = $this->getEntityService($data['entity'][0]);
				if(method_exists($entityService, 'sortChildren')) {
					$data = $entityService->sortChildren($data);
					return new JsonResponse($data);
				} else {
					return $this->requErrors(500, 'This entity is not nestable !');
				}
			} else {
				// ERROR
				$data['request']['method'] = $request->getMethod();
				$this->get('aetools.debug')->debugNamedFile('ajaxqueries_ajaxsort', $data);
				return $this->requErrors(500, 'Request data not found');
			}
		} else if(!$request->isXmlHttpRequest()) {
			// TEST EN GET
			$data = array(
				'entity' => array('article', '7'),
				'children' => array(
					array('article', '8'),
					array('article', '9'),
					array('article', '7'),
					),
				'group' => 'articles',
				);
			$entityService = $this->getEntityService($data['entity'][0]);
			if(method_exists($entityService, 'sortChildren')) {
				$data = $entityService->sortChildren($data);
				echo('<pre>');
				var_dump($data);
				echo('</pre>');
				return new Response('ok !');
			}
		}
		return $this->requErrors(500, 'Uncognized system error !');
	}


	/***************************/
	/*** ERRORS              ***/
	/***************************/

	protected function requErrors($status, $message) {
		$response = new Response();
		$response->headers->set('Content-type', 'application/json');
		$response->setContent((string) $message);
		$response->setStatusCode((integer) $status);
		return $response;
	}

}
