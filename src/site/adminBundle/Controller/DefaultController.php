<?php

namespace site\adminBundle\Controller;

use site\adminBundle\Controller\baseController;
// use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
 * DefaultController
 * @Security("has_role('ROLE_TRANSLATOR')")
 */
class DefaultController extends baseController {

	/**
	 * Page d'accueil adminstration
	 * @return Response
	 */
	public function indexAction() {
		$this->get('aetools.aetools')->updateBundlesInConfig();
		$data = array();
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		$data['messages'] = $this->get('aetools.aeMessage')->getRepo()->findNotRead();
		$data['bundle'] = $this->getBundle();
		return $this->render('siteadminBundle:Default:index.html.twig', $data);
	}

	/**
	 * Page de support (help)
	 * @return Response
	 */
	public function supportAction() {
		$data = array();
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		$data['bundle'] = $this->getBundle();
		return $this->render('siteadminBundle:Default:support.html.twig', $data);
	}

	//**************//
	// BLOCKS       //
	//**************//

	public function headerAction($option = null) {
		$data = array();
		$stack = $this->get('request_stack');
		$masterRequest = $stack->getMasterRequest();
		$data['infoRoute']['_route'] = $masterRequest->get('_route');
		$data['infoRoute']['_route_params'] = $masterRequest->get('_route_params');
		$data['bundle'] = $this->getBundle();
		return $this->render('siteadminBundle:blocks:header.html.twig', $data);
	}

	public function sidebarAction($option = null) {
		$data = array();
		$data['sitedata'] = $this->get('aetools.aeSite')->getSiteData();
		$data['roles'] = $this->get('labo_user_roles')->getListOfRoles();
		// variables diverses
		$data['typeSelf'] = self::TYPE_SELF;
		$data['type_value_joiner'] = self::TYPE_VALUE_JOINER;
		$data['bundle'] = $this->getBundle();
		return $this->render('siteadminBundle:blocks:sidebar.html.twig', $data);
	}


}
