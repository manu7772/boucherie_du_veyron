<?php

namespace site\adminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AcmeGroup\laboInspiniaBundle\services\flashMessage;

use \Exception;

class menusController extends Controller {

	public function indexAction() {
		$data = array();
		$aeMenus = $this->get('aeMenus');
		$data['menus'] = $aeMenus->getInfoMenus();
		$data['bundles'] = $aeMenus->getBundles();
		return $this->render('siteadminBundle:menus:index.html.twig', $data);
	}

	public function actionAction($action, $bundle, $name = null) {
		$data = array();
		$data['action'] = $action;
		$data['bundle'] = $bundle;
		$data['name'] = $name;
		$aeMenus = $this->get('aeMenus');
		$data['bundles'] = $aeMenus->getBundles();
		switch ($action) {
			case 'create':
				# code...
				break;
			
			case 'edit':
				$data['menu'] = $aeMenus->getInfoMenu($bundle, $name);
				# code...
				break;
			
			case 'delete':
				# code...
				break;
			
			case 'copy':
				# code...
				break;
			
			default:
				// view
				$data['menu'] = $aeMenus->getInfoMenu($bundle, $name);
				break;
		}
		return $this->render('siteadminBundle:menus:menu_action.html.twig', $data);
	}

	/**
	 * Ajax modification d'un menu
	 * @param string @name
	 * @return boolean
	 */
	public function modifyAction($bundle, $name) {
		$aeMenus = $this->get('aeMenus');
		$request = $this->getRequest();
		$tree = $request->request->get('tree');
		$data = $aeMenus->changeOrderInFile($bundle, $name, $tree);
		return new JsonResponse(true);
	}


}
