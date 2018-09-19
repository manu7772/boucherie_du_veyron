<?php

namespace site\siteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Labo\Bundle\AdminBundle\services\aeData;
use Labo\Bundle\AdminBundle\services\aeReponse;
use Labo\Bundle\AdminBundle\services\flashMessage;
use Labo\Bundle\AdminBundle\services\aeServicePanier;

use site\adminsiteBundle\Entity\message;
use Labo\Bundle\AdminBundle\Form\contactmessageType;
use \DateTime;

use site\adminsiteBundle\Entity\pageweb;
use site\adminsiteBundle\Entity\panier;
use site\adminsiteBundle\Entity\article;
use site\UserBundle\Entity\User;
use Labo\Bundle\AdminBundle\Entity\LaboUser;

class panierController extends Controller {


	/**
	 * info on Panier
	 * @param integer $user = null
	 * @param boolean $complete = false
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function infoPanierAction($user = null, $complete = false, Request $request) {
		// if($user == null) $user = $this->getUser();
		$infopanier = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServicePanier')->getInfosPanier($user, $complete);
		$aeReponse = new aeReponse(is_array($infopanier), $infopanier, null);
		return $request->isXmlHttpRequest() ? $aeReponse->getJSONreponse() : $aeReponse;
	}

	/**
	 * Actions on Panier
	 * @param Json $getdata = null
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function panierAction($getdata = null, Request $request) {
		$aeReponse = null;
		$servicePanier = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServicePanier');
		// POST Params
		$postParams = $request->request->get('params');
		if($postParams == null && $getdata != null) {
			$postParams = json_decode($getdata, true);
		}
		$servicePanier->computePanier($postParams);
		// echo('<pre>');var_dump($postParams);die('</pre>');
		switch($postParams['action']) {
			// actions…
			case 'add':
				$aeReponse = $servicePanier->ajouteArticle($postParams);
				break;
			case 'supp':
				$aeReponse = $servicePanier->reduitArticle($postParams);
				break;
			case 'remove':
				$aeReponse = $servicePanier->SupprimeArticle($postParams);
				break;
			case 'empty':
				$aeReponse = $servicePanier->videPanier($postParams['user']);
				break;
		}
		if($aeReponse == null) $aeReponse = new aeReponse(false, null, ucfirst($this->get('translator')->trans('errors.errorpanier', [], aeServicePanier::CLASS_SHORT_ENTITY)));
		// Response if AJAX
		return $request->isXmlHttpRequest() ? $aeReponse->getJSONreponse() : $aeReponse;
	}

	/**
	 * @Security("has_role('ROLE_USER')")
	 * Validation du panier
	 * @param Request $request
	 * @return Response
	 */
	public function paniervalidAction(Request $request) {
		$data = array();
		$form = $request->request->get('form');
		$commandeready = new DateTime($form['commandeready']);
		// boutique
		$boutiques = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServiceBoutique')->getRepo()->findAll();
		// echo('<pre>');var_dump($boutiques);die('</pre>');
		$boutique = reset($boutiques);

		$serviceFacture = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServiceFacture');
		$aeReponse = $serviceFacture->saveUserFacture($this->getUser(), $boutique, !$this->getUser()->hasRole('ROLE_TESTER'));

		$message = $aeReponse->getMessage();
		$typeMessage = $aeReponse->getResult() ? flashMessage::MESSAGES_SUCCESS : flashMessage::MESSAGES_ERROR;
		if(trim($message) != '') $this->get('flash_messages')->send(array(
			'title'		=> ucfirst('Action Panier'),
			'type'		=> $typeMessage,
			'text'		=> $message,
		));

		if($aeReponse->getResult()) {
			$facture = $aeReponse->getData();
			$facture->setDelailivraison($commandeready);
			$serviceFacture->saveFacture($facture);
			$factureId = $facture->getId();
			// envois mails
			$serviceEmail = $this->get(aeData::PREFIX_CALL_SERVICE.'aeEmail');
			$serviceEmail->emailToUserAfterCommand($this->getUser(), $facture);
			// $collaborateurs = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServiceSite')->getAllCollaborateurs($this->getRequest()->getSession()->get('sitedata')['id']);
			$serviceEmail->emailConfirmCdeCollaborateurs($this->getUser(), $facture);
		} else {
			$factureId = null;
		}
		// redirect…
		// return $this->render('sitesiteBundle:extended_pages_web:paniervalid.html.twig', $data);
		return $this->redirect($this->generateUrl('panier_pageweb_valid_confirm', array('factureId' => $factureId)));
	}

	/**
	 * @Security("has_role('ROLE_USER')")
	 * Pageweb validation du panier
	 * @param string $factureId
	 * @return Response
	 */
	public function paniervalidconfirmAction($factureId = null) {
		$data = array();
		if($factureId !== null) {
			$data['facture'] = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServiceFacture')->getRepo()->findOneById($factureId);
			// echo('<pre>');var_dump($data['facture']);die('</pre>');
		} else {
			$data['facture'] = null;
		}
		$servicePageweb = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServicePageweb');
		$data['pageweb'] = $servicePageweb->getRepo()->findPaniervalid();
		if($data['pageweb'] instanceOf pageweb)
			$template = $servicePageweb->getPageBySlug($data['pageweb']->getSlug())['template'];
			else $template = 'sitesiteBundle:extended_pages_web:paniervalid.html.twig';
		return $this->render($template, $data);
	}

	/**
	 * @Security("has_role('ROLE_USER')")
	 * Verification date d'enlèvement pour confirmation de commande
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function commandeVerifDateAction(Request $request) {
		$date = $request->request->get('date');
		$date = preg_replace('([^0-9])', '-', $date);
		$aeReponse = $this->get(aeData::PREFIX_CALL_SERVICE.'aeServiceCalendar')->verifDate(new DateTime($date), 'boutique', 'boutique-de-poncin', 'OUVERT', true);
		return $aeReponse->getArrayJSONreponse();
	}

	/**
	 * @Security("has_role('ROLE_USER')")
	 * Actions de tests sur panier
	 * @param Json $getdata = null
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function panierTestAction($getdata = null, Request $request) {
		$aeReponse = $this->panierAction($getdata, $request);
		if($aeReponse instanceOf aeReponse) {
			$message = $aeReponse->getMessage();
			$typeMessage = $aeReponse->getResult() ? flashMessage::MESSAGES_SUCCESS : flashMessage::MESSAGES_ERROR;
			if(trim($message) != '') $this->get('flash_messages')->send(array(
				'title'		=> ucfirst('Action Panier'),
				'type'		=> $typeMessage,
				'text'		=> $message,
			));
		} else {
			throw new Exception("Action is not AJAX, so aeReponse must be an object, in panierController::panierTestAction().", 1);
		}
		return $this->redirect($this->generateUrl('siteadmin_sadmin_panier'));
	}




}
