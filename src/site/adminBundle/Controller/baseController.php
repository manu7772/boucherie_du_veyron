<?php

namespace site\adminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
 * baseController
 * @Security("has_role('ROLE_TRANSLATOR')")
 */
class baseController extends Controller {

	const TYPE_SELF 			= '_self';
	const DEFAULT_ACTION 		= 'list';
	const LIST_ACTION 			= 'list';
	const SHOW_ACTION 			= 'show';
	const CREATE_ACTION 		= 'create';
	const EDIT_ACTION 			= 'edit';
	const COPY_ACTION 			= 'copy';
	const DELETE_ACTION 		= 'delete';
	const ACTIVE_ACTION 		= 'active';
	const CHECK_ACTION 			= 'check';
	const TYPE_VALUE_JOINER 	= '___';
	const BUNDLE				= 'siteadmin';
	const LIST_REPO_METHOD		= 'findForList';
	const LIST_REPO_DEFAULT		= 'findAll';

	protected $bundle 			= null;

	public function getBundle() {
		if($this->bundle == null) {
			if($this->container->hasParameter('siteadminBundle')) $this->bundle = $this->getParameter('siteadminBundle');
			// echo('<p>Bundle system : '.$this->bundle.'</p>');
			if($this->bundle == null) $this->bundle = self::BUNDLE;
			// echo('<p>Bundle selected : '.$this->bundle.'</p>');
		}
		// else echo('<p>---</p>');
		return $this->bundle;
	}

	/**
	 * Renvoie le template selon le bundle décrit dans config.yml
	 * Si le bundle décrit est absent ou le template absent, utilise siteadminBundle par défaut
	 * @param string $view
	 * @param array $parameters = array()
	 * @param Response $response = null
	 * @return Response
	 */
	public function render($view, array $parameters = array(), Response $response = null) {
		$view = explode(':', $view, 2)[1];
		$bundle = $this->getBundle();
		// echo('<p>Class : '.get_called_class().'</p>');
		if($this->get('templating')->exists($bundle."Bundle:".$view)) return parent::render($bundle."Bundle:".$view, $parameters, $response);
		if($this->get('templating')->exists(self::BUNDLE."Bundle:".$view)) return parent::render(self::BUNDLE."Bundle:".$view, $parameters, $response);
		return $this->redirect($this->generateUrl('siteadmin_homepage'));
	}

	/**
	 * Get service de l'entité (ou le parent le plus proche, jusqu'à aeEntity en dernier recours)
	 * @param string $entity
	 * @return object
	 */
	public function getEntityService($entity) {
		$ES = $this->get('aetools.aeEntity');
		if($ES->entityClassExists($ES->getEntityClassName($entity)))
			return $ES->getEntityService($entity);
			else return $ES;
	}

}