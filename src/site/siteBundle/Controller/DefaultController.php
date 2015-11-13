<?php

namespace site\siteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller {

	public function indexAction() {
		$this->em = $this->getDoctrine()->getManager();
		$this->repo = $this->em->getRepository('site\adminBundle\Entity\pageweb');
		$data['pageweb'] = $this->repo->findOneByHomepage(1);
		// chargement de la pageweb
		if(is_object($data['pageweb'])) {
			$page = 'sitesiteBundle:pages_web:'.$data['pageweb']->getModele().'.html.twig';
			return $this->render($page, $data);
		} else {
			// si aucune page web… chargement de la page par défaut…
			$data['title'] = 'La Boucherie du Veyron';
			$data['description'] = 'La Boucherie du Veyron';
			$data['keywords'] = 'La Boucherie du Veyron';
			return $this->render('sitesiteBundle:Default:index.html.twig', $data);
		}
	}

	public function pagewebAction($pageweb, $params = null) {
		if($params == null) $params = array();
		$data = $params;
		// find $pageweb
		$this->em = $this->getDoctrine()->getManager();
		$this->repo = $this->em->getRepository('site\adminBundle\Entity\pageweb');
		$data['pageweb'] = $this->repo->findOneBySlug($pageweb);
		// chargement de la pageweb
		$page = 'sitesiteBundle:pages_web:'.$data['pageweb']->getModele().'.html.twig';
		return $this->render($page, $data);
	}

	public function headerMiddleAction() {
		$data['menu'] = $this->get('aeMenus')->getMenu('site-menu');
		// récupération route/params requête MASTER
		$stack = $this->get('request_stack');
		$masterRequest = $stack->getMasterRequest();
		$data['infoRoute']['_route'] = $masterRequest->get('_route');
		$data['infoRoute']['_route_params'] = $masterRequest->get('_route_params');
		return $this->render('sitesiteBundle:blocks:headerMiddle.html.twig', $data);
	}

	public function sidemenuAction() {
		$user = $this->getUser();
		if(is_object($user)) {
			$data['menu'] = $this->get('aeMenus')->getMenu('admin-sidemenu');
			$data['bundles.User.name']['params']['name'] = $user->getUsername();
			return $this->render('sitesiteBundle:blocks:sidemenu.html.twig', $data);
		} else {
			return new Response(null);
		}
	}

}
