<?php

namespace site\siteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use site\adminsiteBundle\Entity\message;
use Labo\Bundle\AdminBundle\Form\contactmessageType;
use \DateTime;

use site\adminsiteBundle\Entity\panier;
use site\adminsiteBundle\Entity\article;

class panierController extends Controller {

	public function panierAction($action = 'add', $id = null, $param = null) {
		$servicePanier = $this->get('aetools.aeServicePanier');
		if($id != null) $id = $this->getDoctrine()->GetManager()->getRepository('site\adminsiteBundle\Entity\article')->find($id);
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
		return $this->redirectToRoute('siteadmin_sadmin_panier');
	}

}
