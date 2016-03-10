<?php

namespace site\adminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use site\adminBundle\services\flashMessage;

use \Exception;

/**
 * menusController
 * @Security("has_role('ROLE_ADMIN')")
 */
class menusController extends Controller {

	public function indexAction() {
		$data = array();
		$aeMenus = $this->get('aetools.aeMenus');
		$data['menus'] = $aeMenus->getInfoMenus();
		$data['bundles'] = $aeMenus->getBundles();
		return $this->render('siteadminBundle:menus:index.html.twig', $data);
	}

	public function actionAction($action, $bundle, $name = null, $id = null) {
		$data = array();
		$data['action'] = $action;
		$data['bundle'] = $bundle;
		$data['name'] = $name;
		$aeMenus = $this->get('aetools.aeMenus');
		$data['translates'] = $aeMenus->getLanguagesInfo(); // "languages" - "catalogue"
		$data['bundles'] = $aeMenus->getBundles();
		switch ($action) {
			case 'add':
				$aeMenus->addNewItem($bundle, $name);
				return $this->redirect($this->generateUrl('siteadmin_menus_action', array('action' => 'edit', 'bundle' => $bundle, 'name' => $name)));
				break;

			case 'create':
				# code...
				break;

			case 'edit':
				$data['menu'] = $aeMenus->getInfoMenu($bundle, $name);
				break;

			case 'delete':
				$aeMenus->deleteItem($bundle, $name, $id);
				return $this->redirect($this->generateUrl('siteadmin_menus_action', array('action' => 'edit', 'bundle' => $bundle, 'name' => $name)));
				break;

			case 'copy':
				# code...
				break;

			default:
				// view
				$data['menu'] = $aeMenus->getInfoMenu($bundle, $name);
				break;
		}
		// $data['models'] = $this->get('aetools.aePageweb')->getModels();
		$data['pagewebs'] = $this->get('aetools.aePageweb')->getRepo()->findAll();
		return $this->render('siteadminBundle:menus:menu_action.html.twig', $data);
	}

	/**
	 * Ajax modification d'un menu
	 * @param string $bundle
	 * @param string $name
	 * @return boolean
	 */
	public function modifyAction($bundle, $name) {
		$aeMenus = $this->get('aetools.aeMenus');
		$request = $this->getRequest();
		$tree = $request->request->get('tree');
		$data = $aeMenus->setMenu($bundle, $name, $tree);
		return new JsonResponse($data);
	}

	/**
	 * Ajax modification de l'attribut maxDepth d'un menu
	 * @param string $bundle
	 * @param string $name
	 * @param string $value
	 * @return boolean
	 */
	public function changeMaxDepthAction($bundle, $name, $value) {
		$value = $this->get('aetools.aeMenus')->setMaxDepth($bundle, $name, $value);
		return new JsonResponse(array('value' => $value));
	}


}
