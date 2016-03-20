<?php
namespace site\adminBundle\services;

use Symfony\Component\HttpFoundation\JsonResponse;

class aeReponse {

	private $data = array();

	public function __construct($result = true, $data = null, $message = "") {
		$this->initAeReponse($result, $data, $message);
	}

	public function initAeReponse($result = true, $data = null, $message = "") {
		$this->setResult($result);
		$this->setData($data);
		$this->setMessage($message);
		return $this;
	}

	// GETTERS

	public function getResult() {
		return $this->data["result"];
	}

	public function getMessage() {
		return $this->data["message"];
	}

	public function getData() {
		return $this->data["data"];
	}

	public function getDataType() {
		return gettype($this->data["data"]);
	}

	public function getJSONreponse($reset = true) {
		$r = new JsonResponse(json_encode($this->data));
		if($reset) $this->initAeReponse();
		return $r;
	}

	// SETTERS

	public function setResult($result) {
		if(!is_bool($result)) $result = false;
		$this->data["result"] = $result;
		return $this;
	}

	public function setMessage($message = null) {
		$this->data["message"] = $message;
		return $this;
	}

	public function setData($data = null) {
		$this->data["data"] = $data;
		return $this;
	}


}