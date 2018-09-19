<?php

namespace site\siteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Labo\Bundle\AdminBundle\services\aeData;
use Labo\Bundle\AdminBundle\services\aeReponse;
use Labo\Bundle\AdminBundle\services\flashMessage;
use Labo\Bundle\AdminBundle\services\aeServicePanier;

use site\adminsiteBundle\Entity\message;
use Labo\Bundle\AdminBundle\Form\contactmessageType;
use \DateTime;

use site\adminsiteBundle\Entity\panier;
use site\adminsiteBundle\Entity\article;
use site\UserBundle\Entity\User;
use Labo\Bundle\AdminBundle\Entity\LaboUser;

class ajaxliveController extends Controller {

	/**
	 * info on entities
	 * @param array $requirements = null
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function globalinfosAction($requirements = null, Request $request) {
		// requirements:
		//	- 'entities'
		//		- shortname
		//			- shortname
		//			- …
		//	- 'user' : id of user
		//
		// GET
		if($requirements != null) $requirements = json_decode(urldecode($requirements), true);
		// POST
		if($request->request->get('requirements') != null) $requirements = $request->request->get('requirements');
		// requirements defaults
		if($requirements == null) {
			$ajaxliveParams = $this->getParameter('ajaxlive');
			$requirements = $ajaxliveParams;
		}
		// User
		// if(!isset($requirements['user'])) $requirements['user'] = $this->getUser();
		// Get infos…
		$entities = array();
		foreach ($requirements['entities'] as $entity) {
			$entities[$entity['shortname']] = $this->get(aeData::PREFIX_CALL_SERVICE.'aeService'.ucfirst($entity['shortname']))->getSerialized($entity);
			if(count(json_decode($entities[$entity['shortname']], true)) < 1) unset($entities[$entity['shortname']]);
		}
		$aeReponse = new aeReponse(true, $entities, null);

		if(!$this->get(aeData::PREFIX_CALL_SERVICE.'aeServiceSessiondata')->checkChangedSessionAjaxlivedata(json_encode($entities))) {
			$aeReponse = new aeReponse(true, array(0 => 'no-changes'), 'Aucune modification dans la base de données serveur.');
		}
		// return new JsonResponse($aeReponse->getData());
		return $request->isXmlHttpRequest() ? $aeReponse->getJSONreponse() : $aeReponse;
	}

	/**
	 * info on ONE entity
	 * @param array $requirements = null
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function entiteinfosAction($requirements = null, Request $request) {
		// GET
		if($requirements != null) $requirements = json_decode($requirements, true);
		// POST
		if($request->request->get('requirements') != null) $requirements = $request->request->get('requirements');
		// requirements defaults
		if($requirements == null) {
			$ajaxliveParams = $this->getParameter('ajaxlive');
			$requirements = $ajaxliveParams;
		}
		// User
		if(!isset($requirements['user'])) $requirements['user'] = $this->getUser();
		// data
		$data = $this->get(aeData::PREFIX_CALL_SERVICE.'aeService'.ucfirst($requirements['shortname']))->getSerialized($requirements);
		$aeReponse = new aeReponse(true, $data, null);
		return $request->isXmlHttpRequest() ? $aeReponse->getJSONreponse() : $aeReponse;
	}


}
