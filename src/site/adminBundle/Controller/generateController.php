<?php

namespace site\adminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use \Exception;

/**
 * generateController
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class generateController extends Controller {

	protected $classnames = null;

	public function indexAction($action = null, $entite = null) {
		$this->get('aetools.aetools')->updateBundlesInConfig();
		$data = array();
		$data['action'] = $action;
		$data['entite'] = $entite;
		if($entite != null) $data['classname'] = $this->getClassname($entite);
		$data['created'] = array();
		$data['emptied'] = array();
		switch ($action) {
			case 'create':
				if($entite != null) $data['created'][$entite] = $this->generateEntite($entite);
				break;
			case 'empty':
				if($entite != null) $data['emptied'][$entite] = $this->emptyEntity($entite);
				break;
			default:
				break;
		}
		$infos = $this->get('aetools.aefixtures')->getInfoFiles();
		// view
		$em = $this->getDoctrine()->getManager();
		$entities = $this->getEntities();
		foreach ($entities as $name => $classname) {
			$data['info'][$name]['classname'] = $classname;
			try {
				$data['info'][$name]['size'] = $em->createQuery("SELECT COUNT(element.id) FROM ".$classname." element")->getSingleScalarResult();
			} catch (Exception $e) {
				// Si l'entité ne possède pas d'Id => on la vire (panier…)
				unset($data['info'][$name]);
			}
			// ajout info fixtures files
			foreach ($infos as $entity => $detail) if(array_key_exists($entity, $entities)) {
				$data['info'][$entity]['fixtures'] = $detail;
			}
		}
		// echo('<pre>');
		// var_dump($data['info']);
		// die('</pre>');
		// informations entités
		return $this->render('siteadminBundle:Default:install.html.twig', $data);
	}

	protected function getEntities() {
		$this->classnames = array_flip($this->get('aetools.aeEntities')->getListOfEnties());
		return $this->classnames;
	}

	protected function getClassname($entite) {
		$this->getEntities();
		return isset($this->classnames[$entite]) ? $this->classnames[$entite] : false;
	}

	protected function generateEntite($entite = null) {
		switch ($entite) {
			// case 'fileFormat':
			// 	return $this->get('aetools.media')->initiateFormats(true);
			// 	break;
			case 'User':
				return $this->get('service.users')->createUsers(true);
				break;
			
			default:
				return $this->get('aetools.aefixtures')->fillDataWithFixtures($entite);
				break;
		}
	}



}
