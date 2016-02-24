<?php

namespace site\adminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use site\adminBundle\Entity\article;

use \Exception;

class superadminController extends Controller {

	public function indexAction() {
		$this->get('aetools.aetools')->updateBundlesInConfig();
		$data = array();
		$repo = $this->get('aetools.aeEntities')->getEm()->getRepository('site\adminBundle\Entity\article');
		$data['articles'] = $repo->findAll();
		$data['panier_user'] = $this->get('aetools.aePanier')->getArticlesOfUser($this->getUser());
		$data['panier_info'] = $this->get('aetools.aePanier')->getInfosPanier($this->getUser());
		return $this->render('siteadminBundle:superadmin:index.html.twig', $data);
	}

	public function entitiesAction($entity = null, $field = null) {
		$data = array();
		$aeEntities = $this->get('aetools.aeEntities');
		$entities = array_flip($aeEntities->getListOfEnties());
		// général
		$level = '';
		foreach ($entities as $name => $value) {
			$data['entities'][$name]['classname'] = $value;
			$data['entities'][$name]['fields'] = $aeEntities->getFieldNamesOfEntity($value);
			$data['entities'][$name]['assoc'] = $aeEntities->getAssociationNamesOfEntity($value);
			$data['entities'][$name]['object'] = new $value();
		}

		if($entity != null) {
			// analyse d'une entité
			$level = '_entity';
			$data['entity']['name'] = $entity;
			$data['entity']['classname'] = $data['entities'][$entity]['classname'];
			$fields = $data['entities'][$entity]['fields'];
			$assoc = $data['entities'][$entity]['assoc'];
			// info sur champs…
			foreach ($fields as $fieldname) {
				$data['entity']['fields'][$fieldname] = array();
				$data['entity']['fields'][$fieldname]['type'] = $aeEntities->getTypeOfField($fieldname, $entity);
				$data['entity']['fields'][$fieldname]['nullable'] = $aeEntities->isNullableField($fieldname, $entity);
				$data['entity']['fields'][$fieldname]['unique'] = $aeEntities->isUniqueField($fieldname, $entity);
				$data['entity']['fields'][$fieldname]['isId'] = $aeEntities->isIdentifier($fieldname, $entity);
			}
			foreach ($assoc as $assocname) {
				$data['entity']['assoc'][$assocname] = array();
				$data['entity']['assoc'][$assocname]['target']['classname'] = $aeEntities->getTargetEntity($assocname, $entity);
				$data['entity']['assoc'][$assocname]['target']['name'] = $aeEntities->getEntityShortName($data['entity']['assoc'][$assocname]['target']['classname']);
				$data['entity']['assoc'][$assocname]['set'] = $aeEntities->getMethodOfSetting($assocname, $entity);
				$data['entity']['assoc'][$assocname]['get'] = $aeEntities->getMethodOfGetting($assocname, $entity);
				$data['entity']['assoc'][$assocname]['remove'] = $aeEntities->getMethodOfRemoving($assocname, $entity);
				$data['entity']['assoc'][$assocname]['isId'] = $aeEntities->isIdentifier($assocname, $entity);
				$data['entity']['assoc'][$assocname]['nullable'] = $aeEntities->isNullableField($assocname, $entity);
				$data['entity']['assoc'][$assocname]['unique'] = $aeEntities->isUniqueField($assocname, $entity);
				$data['entity']['assoc'][$assocname]['single'] = $aeEntities->isAssociationWithSingleJoinColumn($assocname, $entity);
				$data['entity']['assoc'][$assocname]['bidir'] = $aeEntities->isBidirectional($assocname, $entity);
				$data['entity']['assoc'][$assocname]['isInverse'] = $aeEntities->isAssociationInverseSide($assocname, $entity);
				$data['entity']['assoc'][$assocname]['otherSideSource'] = $aeEntities->get_OtherSide_sourceField($assocname, $entity);
			}
		}

		if($field != null) {
			// analyse d'un champ
			$level = '_field';
		}
		return $this->render('siteadminBundle:superadmin:entities'.$level.'.html.twig', $data);
	}





}
