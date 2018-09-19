<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
// use Labo\Bundle\AdminBundle\Controller\factureController as extendController;
// use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Labo\Bundle\AdminBundle\services\aeData;
use Labo\Bundle\AdminBundle\Entity\LaboUser;

/**
 * factureExtController
 */
class factureExtController extends Controller {

	protected function findAndConfirmUser($username, $key) {
		$userManager = $this->get('fos_user.user_manager');
		$user = $userManager->findUserByUsername($username);
		if($user instanceOf LaboUser) {
			if(urldecode($key) === $user->getPassword()) return $user;
		}
		// if wrong user…
		throw new AccessDeniedException('You do not have access to this section!');
	}

	public function changeStateFactureAction($factureId, $state, $username, $key) {
		$data = array('action' => 'changestate');
		$data['user'] = $this->findAndConfirmUser($username, $key);
		$serviceFacture = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServiceFacture');
		$em = $serviceFacture->getEm();
		$data['facture'] = $serviceFacture->getRepo()->findOneById($factureId);
		if(is_object($data['facture'])) {
			$data['facture']->setState($state);
			$em->flush();
		}
		return $this->render('siteadminsiteBundle:external:facture.html.twig', $data);
	}

	public function deleteTestFactureAction($factureId, $username, $key) {
		$data = array('action' => 'delete');
		$data['user'] = $this->findAndConfirmUser($username, $key);
		$serviceFacture = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServiceFacture');
		$em = $serviceFacture->getEm();
		$data['facture'] = $serviceFacture->getRepo()->findOneById($factureId);
		if(is_object($data['facture'])) {
			$em->remove($data['facture']);
			$em->flush();
		}
		return $this->render('siteadminsiteBundle:external:facture.html.twig', $data);
	}

}
