<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Labo\Bundle\AdminBundle\Controller\DefaultController as extendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Labo\Bundle\AdminBundle\services\aeData;

/**
 * DefaultController
 * @Security("has_role('ROLE_TRANSLATOR')")
 */
class DefaultController extends extendController {
	
	/**
	 * Page d'accueil adminstration
	 * @return Response
	 */
	public function indexAction() {
		$data = array();
		$data['defaultPageweb'] = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServicePageweb')->getDefaultPage();
		// if site not installed (no default pageweb)
		if(count($data['defaultPageweb']) < 1) return $this->redirectToRoute('generate');
		// else…
		$this->get(aeData::PREFIX_CALL_SERVICE.'aeUrlroutes')->updateBundlesInConfig();
		$data['sitedata'] = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServiceSite')->getSiteData();
		$data['messages'] = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServiceMessage')->getRepo()->findNotRead(false);
		// $data['factures'] = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServiceFacture')->getRepo()->findByState(0);
		$data['nbmessages'] = count($data['messages']);
		$nbmessages = $this->getParameter('admin')['accueil_nb_messages'];
		$data['messages'] = array_slice($data['messages'], 0, $nbmessages); // max. : X messages
		$data['bundle'] = $this->get(aeData::PREFIX_CALL_SERVICE.'aeUrlroutes')->getBundleName();
		return $this->render('LaboAdminBundle:Default:index.html.twig', $data);
	}



}
