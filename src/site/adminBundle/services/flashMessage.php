<?php
namespace site\adminBundle\services;

class flashMessage {

	const MESSAGES_SUCCESS			= "success";
	const MESSAGES_INFO				= "info";
	const MESSAGES_WARNING			= "warning";
	const MESSAGES_ERROR			= "error";
	const DEFAULT_ROLE				= "ROLE_USER";

	protected $message;

	public function __construct($params = null) {
		$this->message = array();
		$this->valid_types = array(
			self::MESSAGES_SUCCESS,
			self::MESSAGES_INFO,
			self::MESSAGES_WARNING,
			self::MESSAGES_ERROR,
			);
		// initialisation
		$this->setByParams($params);
	}

	public function setByParams($params = null) {
		// défaults
		$this->setNom();
		$this->setType();
		$this->setTitle('Titre');
		$this->setText('');
		$this->setGrant(self::DEFAULT_ROLE);
		// params
		$defaultParams = array('nom', 'type', 'title', 'text', 'grant');
		foreach ($defaultParams as $nomParam) {
			if(isset($params[$nomParam])) {
				$set = 'set'.ucfirst($nomParam);
				$this->$set($params[$nomParam]);
			}
		}
	}

	public function resetNom($nom = null) {
		$this->message['nom'] = time().'_'.rand(1000000, 9999999);
	}

	public function setNom($nom = null) {
		if($nom == null && !isset($this->message['nom']))
			$this->resetNom();
		else $this->message['nom'] = $nom;
	}

	public function getNom() {
		return $this->message['nom'];
	}

	public function setType($type = null) {
		if(in_array($type, $this->valid_types))
			$this->message['type'] = $type;
			else $this->message['type'] = reset($this->valid_types);
		return $this;
	}

	public function getType() {
		return $this->message['type'];
	}

	public function getValidTypes() {
		return $this->valid_types;
	}

	public function setTitle($title) {
		$this->message['title'] = $title;
		return $this;
	}

	public function getTitle() {
		return $this->message['title'];
	}

	public function setText($text) {
		$this->message['text'] = $text;
		return $this;
	}

	public function addText($text) {
		$this->message['text'] .= $text;
		return $this;
	}

	public function getText() {
		return $this->message['text'];
	}

	public function setGrant($role) {
		$this->message['role'] = $role;
		return $this;
	}

	public function getGrant() {
		return $this->message['role'];
	}


}