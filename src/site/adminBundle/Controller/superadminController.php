<?php

namespace site\adminBundle\Controller;

use site\adminBundle\Controller\baseController;
// use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
// use Doctrine\Common\Annotations\AnnotationReader;

use site\adminBundle\Entity\article;

use \Exception;
use \ReflectionObject;
use \ReflectionClass;
use \ReflectionMethod;

/**
 * superadminController. 
 * Interface permettant de contrôler les données via le role SUPER ADMIN. 
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class superadminController extends baseController {

	/**
	 * Page d'accueil SUPER ADMIN
	 * @return Response
	 */
	public function indexAction() {
		$this->get('aetools.aetools')->updateBundlesInConfig();
		$data = array();
		// Site data
		$data['sitedata'] = $this->get('aetools.aeSite')->getDefaultSiteData();
		// services
		$data['services'] = array();
		$listOfServices = $this->get('aetools.aetools')->getListOfServices();
		foreach ($listOfServices as $service) {
			$data['services'][$service] = $this->get($service);
		}
		$data['aetools_service'] = $this->get('aetools.aetools');
		return $this->render('siteadminBundle:superadmin:index.html.twig', $data);
	}

	/**
	 * Page de test sur Panier
	 * @return Response
	 */
	public function panierTestAction() {
		$this->get('aetools.aetools')->updateBundlesInConfig();
		$data = array();
		$data['sitedata'] = $this->get('aetools.aeSite')->getDefaultSiteData();
		$data['articles'] = $this->getEntityService('article')->getRepo()->findAll();
		$data['panier_user'] = $this->get('aetools.aePanier')->getArticlesOfUser($this->getUser());
		$data['panier_articles'] = array();
		foreach ($data['panier_user'] as $panier) {
			$data['panier_articles'][] = $panier->getArticle();
		}
		$data['panier_info'] = $this->get('aetools.aePanier')->getInfosPanier($this->getUser());
		return $this->render('siteadminBundle:superadmin:panier_test.html.twig', $data);
	}

	/**
	 * Page d'information sur les services    		
	 * @param string $service
	 * @return Response
	 */
	public function servicesAction($service) {
		$data = array();
		$data['sitedata'] = $this->get('aetools.aeSite')->getDefaultSiteData();
		$data['name'] = $service;
		$data['service_info'] = $this->get('aetools.aetools')->getObjectProperties($this->get($service));
		return $this->render('siteadminBundle:superadmin:services.html.twig', $data);
	}


	/**
	 * Page d'information sur les routes
	 * @return Response
	 */
	public function routesAction() {
		$data = array();
		$data['sitedata'] = $this->get('aetools.aeSite')->getDefaultSiteData();
		// $data['params'] = $aetools->getRouteParameters();
		$data['aetools_service'] = $this->get('aetools.aetools');
		// via stack
		// $stack = $this->get('request_stack');
		// $masterRequest = $stack->getMasterRequest();
		// $data['routes'] = $masterRequest->get('_route');
		return $this->render('siteadminBundle:superadmin:routes.html.twig', $data);
	}

	/**
	 * Page d'information sur les bundles
	 * @return Response
	 */
	public function bundlesAction() {
		$data = array();
		$data['sitedata'] = $this->get('aetools.aeSite')->getDefaultSiteData();
		$data['bundles'] = $this->get('aetools.aetools')->getBundlesList(true);
		$data['bundle'] = $this->get('aetools.aetools')->getBundleName();
		return $this->render('siteadminBundle:superadmin:bundles.html.twig', $data);
	}

	/**
	 * Page d'information sur les entités
	 * @param string $entity = null
	 * @param string $field = null
	 * @return Response
	 */
	public function entitiesAction($entity = null, $field = null) {
		$data = array();
		$data['sitedata'] = $this->get('aetools.aeSite')->getDefaultSiteData();
		$aeEntities = $this->get('aetools.aeEntity');
		$entities = $aeEntities->getListOfEnties(false, true, true);
		// général
		$level = '';
		foreach ($aeEntities->getListOfEnties(false) as $name => $value) {
			$data['entities'][$name]['shortname'] = $value;
			$data['entities'][$name]['classname'] = $name;
			$data['entities'][$name]['single'] = $aeEntities->getFieldNamesOfEntity($name);
			$data['entities'][$name]['association'] = $aeEntities->getAssociationNamesOfEntity($name);
			$data['entities'][$name]['object'] = new $name();
		}
		foreach ($aeEntities->getListOfEnties(true) as $name => $value) {
			$data['entities'][$name]['shortname'] = $value;
			$data['entities'][$name]['classname'] = $name;
			$data['entities'][$name]['single'] = $aeEntities->getFieldNamesOfEntity($name);
			$data['entities'][$name]['association'] = $aeEntities->getAssociationNamesOfEntity($name);
		}

		if($entity != null) {
			// analyse d'une entité
			$level = '_entity';
			$entityClass = $aeEntities->getEntityClassName($entity);
			$data['entity']['shortname'] = $entity;
			$data['entity']['classname'] = $data['entities'][$entityClass]['classname'];
			$data['entity']['classinfo'] = $this->get('aetools.aetools')->getObjectProperties($data['entity']['classname']);
			$fields = $data['entities'][$entityClass]['single'];
			$assoc = $data['entities'][$entityClass]['association'];
			// info sur champs…
			foreach ($fields as $fieldname) {
				$data['entity']['single'][$fieldname] = array();
				$data['entity']['single'][$fieldname]['type'] = $aeEntities->getTypeOfField($fieldname, $entity);
				$data['entity']['single'][$fieldname]['nullable'] = $aeEntities->isNullableField($fieldname, $entity);
				$data['entity']['single'][$fieldname]['unique'] = $aeEntities->isUniqueField($fieldname, $entity);
				$data['entity']['single'][$fieldname]['isId'] = $aeEntities->isIdentifier($fieldname, $entity);
				$data['entity']['single'][$fieldname]['set'] = $aeEntities->getMethodOfSetting($fieldname, $entityClass, true);
				$data['entity']['single'][$fieldname]['get'] = $aeEntities->getMethodOfGetting($fieldname, $entityClass, true);
				$data['entity']['single'][$fieldname]['remove'] = $aeEntities->getMethodOfRemoving($fieldname, $entityClass, true);
			}
			foreach ($assoc as $assocname) {
				$data['entity']['association'][$assocname] = array();
				$targetName = $aeEntities->getTargetEntity($assocname, $entity, true);
				// echo('<p>'.$entity." target for ".$assocname." = ".$targetName.'</p>');
				$data['entity']['association'][$assocname]['target']['classname'] = $targetName;
				$data['entity']['association'][$assocname]['target']['name'] = $aeEntities->getEntityShortName($data['entity']['association'][$assocname]['target']['classname']);
				$data['entity']['association'][$assocname]['set'] = $aeEntities->getMethodOfSetting($assocname, $entityClass, true);
				$data['entity']['association'][$assocname]['get'] = $aeEntities->getMethodOfGetting($assocname, $entityClass, true);
				$data['entity']['association'][$assocname]['remove'] = $aeEntities->getMethodOfRemoving($assocname, $entityClass, true);
				$data['entity']['association'][$assocname]['isId'] = $aeEntities->isIdentifier($assocname, $entity);
				$data['entity']['association'][$assocname]['nullable'] = $aeEntities->isNullableField($assocname, $entity);
				$data['entity']['association'][$assocname]['unique'] = $aeEntities->isUniqueField($assocname, $entity);
				$data['entity']['association'][$assocname]['unidir'] = $aeEntities->isAssociationWithSingleJoinColumn($assocname, $entity);
				$data['entity']['association'][$assocname]['bidir'] = $aeEntities->isBidirectional($assocname, $entity);
				$data['entity']['association'][$assocname]['isInverse'] = $aeEntities->isAssociationInverseSide($assocname, $entity);
				$data['entity']['association'][$assocname]['otherSideSource'] = $aeEntities->get_OtherSide_sourceField($assocname, $entity);
			}
		}

		if($field != null) {
			// analyse d'un champ
			$level = '_field';
		}
		return $this->render('siteadminBundle:superadmin:entities'.$level.'.html.twig', $data);
	}





}
