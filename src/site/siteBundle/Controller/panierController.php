<?php

namespace site\siteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use site\adminBundle\Entity\message;
use site\adminBundle\Form\contactmessageType;
use \DateTime;

use site\adminBundle\Entity\panier;
use site\adminBundle\Entity\article;

class panierController extends Controller {

	public function panierAction($action = 'add', $id = null, $param = null) {
		$servicePanier = $this->get('aetools.aePanier');
		if($id != null) $id = $this->getDoctrine()->GetManager()->getRepository('site\adminBundle\Entity\article')->find($id);
		if($param == null) $param = 1;
		switch ($action) {
			case 'add':
				$servicePanier->ajouteArticle($id, $this->getUser(), $param);
				break;
			case 'supp':
				$servicePanier->reduitArticle($id, $this->getUser(), $param);
				break;
			case 'remove':
				$servicePanier->SupprimeArticle($id, $this->getUser());
				break;
			case 'empty':
				$servicePanier->videPanier($this->getUser());
				break;
		}
		
		//
		return $this->redirect($this->generateUrl('siteadmin_sadmin_index'));
	}

}
