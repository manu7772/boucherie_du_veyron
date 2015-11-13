<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use \Exception;
use \NotFoundHttpException;

class aeMessages {

	protected $container;			// container
	protected $flashBag;			// session
	protected $translator;			// translator
	protected $exceptionsList;		// liste des exception valides

	public function __construct(ContainerInterface $container = null) {
		$this->container = $container;
		$this->translator = $this->container->get('translator');
		$this->exceptionsList = array(
			"Exception",
			"NotFoundHttpException",
			);
	}

	public function launchException($message, $vars = null, $domaine = null, $langue = null, $typeException = null) {
		if(!is_array($vars)) $vars = array();
		if($typeException == null) $typeException = reset($this->exceptionsList);
		if(is_string($message) && in_array($typeException, $this->exceptionsList)) {
			// Envoie de l'exception
			$vars['texte'] = $message;
			throw new $typeException(ucfirst($this->getTransText($vars, $domaine, $langue)), 1);
		}
		// Erreur
		throw new Exception("Erreur data : site\\adminBundle\\services\\aeMessages::launchException.", 1);
	}

	public function addFlashMessage($titre, $message, $type = 'success', $domaine = null, $langue = null) {
		$this->container->get('session')->getFlashBag()->add(
			$type,
			array(
				'debug'	=> false,
				'positionClass' => 'toast-top-right',
				'title' => ucfirst($this->getTransText($titre, $domaine, $langue)),
				'texte' => ucfirst($this->getTransText($message, $domaine, $langue)),
				)
			);
	}

	public function addAdminFlashMessage($titre, $message, $type = 'success', $domaine = null, $langue = null) {
		$this->container->get('session')->getFlashBag()->add(
			$type,
			array(
				'debug'	=> true,
				'positionClass' => 'toast-top-center',
				'title' => ucfirst($this->getTransText($titre, $domaine, $langue)),
				'texte' => ucfirst($this->getTransText($message, $domaine, $langue)),
				)
			);
	}

	protected function getArray($data) {
		$return = array();
		$return['data'] = array();
		if(is_array($data)) {
			// array détecté, séparation des données
			if(array_key_exists('texte', $data)) {
				$return['texte'] = $data['texte'];
				unset($data['texte']);
			} else {
				$return['texte'] = array_shift($data);
			}
			if(is_array($data)) $return['data'] = $data;
		} else {
			// string -> transformation en array
			$return['texte'] = $data;
		}
		return $return;
	}

	protected function getTransText($array, $domaine = null, $langue = null) {
		$array = $this->getArray($array);
		// echo("<pre>--- ".$array['texte']." ---");
		// var_dump($array);
		// echo("</pre>");
		if(array_key_exists('%count%', $array['data'])) {
			// transChoice
			if(is_array($array['data']['%count%'])) $count = count($array['data']['%count%']);
				else $count = intval($array['data']['%count%']);
			$array['data']['%count%'] = $count;
			// unset($array['data']['%count%']);
			$transText = $this->translator->transChoice($array['texte'], $count, $array['data'], $domaine, $langue);
		} else {
			$transText = $this->translator->trans($array['texte'], $array['data'], $domaine, $langue);
		}
		return $transText;
	}

}