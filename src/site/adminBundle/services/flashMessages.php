<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\flashMessage;

class flashMessages {

	protected $message;
	protected $container;
	protected $serviceSess;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
		$this->serviceSess = $this->container->get('request')->getSession();
		$this->message = array();
	}

	public function create($params = null) {
		$new_message = new flashMessage($params);
		while (isset($this->message[$new_message->getNom()])) {
			$new_message->resetNom();
		}
		$this->message[$new_message->getNom()] = $new_message;
		return $this->message[$new_message->getNom()];
	}

	public function send($params) {
		$this->sendOne($this->create($params));
	}

	/**
	 * Place les $messages en flashbag. 
	 * Renvoie true si au moins un message a été ajouté / sinon false
	 * @param array/flashMessage $messages
	 * @return boolean
	 */
	public function sendAll() {
		// if(!is_array($messages)) $messages = array($messages);
		$send = 0;
		foreach ($this->message as $message) if($message instanceOf flashMessage) {
			$this->sendOne($message);
			$send++;
		}
		return count($send) > 0 ? true : false;
	}

	protected function sendOne(flashMessage $msg) {
		$send[$msg->getNom()] = array(
			"type"		=> $msg->getType(),
			"title" 	=> $msg->getTitle(),
			"text" 		=> $msg->getText(),
			"grant"		=> $msg->getGrant(),
		);
		$this->serviceSess->getFlashBag()->add("flashMessages", $send);
	}

}