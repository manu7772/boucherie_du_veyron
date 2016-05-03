<?php
namespace site\adminBundle\services;

use Symfony\Component\HttpFoundation\JsonResponse;
use \ReflectionClass;

class aeReponse {

    const NAME                  = 'aeReponse';        // nom du service
    const CALL_NAME             = 'aetools.aeReponse'; // comment appeler le service depuis le controller/container

	const SLASH					= '/';				// slash
	const ASLASH 				= '\\';				// anti-slashes

	/**
	 * Données de l'objet aeReponse
	 */
	private $data = array();

	/**
	 * Constructeur
	 * @param boolean $result = true
	 * @param mixed $data = null
	 * @param string $message = null
	 * @return aeReponse
	 */
	public function __construct($result = true, $data = null, $message = null) {
		$this->initAeReponse($result, $data, $message);
		return $this;
	}

	public function __toString() {
		return (string) $this->data["data"];
	}

	// public function __toString() {
	// 	try {
	// 		$string = $this->getNom();
	// 	} catch (Exception $e) {
	// 		$string = '…';
	// 	}
	// 	return $string;
	// }

	public function getNom() {
		return self::NAME;
	}

	public function callName() {
		return self::CALL_NAME;
	}

	/**
	 * Renvoie le nom de la classe
	 * @return string
	 */
	public function getName() {
		return get_called_class();
	}

	/**
	 * Renvoie le nom de la classe
	 * @return string
	 */
	public function getShortName() {
		return $this->getClassShortName($this->getName());
	}

    // abstract public function getClassName();
    public function getClassName() {
        return $this->getClass(true);
    }

	/**
	 * Renvoie la liste (array) des classes des parents de l'entité
	 * @param boolean $short = false
	 * @return array
	 */
	public function getParentClassName($short = false) {
		$class = new ReflectionClass($this->getClass());
		$class = $class->getParentClass();
		if($class)
			return (boolean) $short ?
				$class->getShortName():
				$class->getName();
			else return null;
	}

	/**
	 * Renvoie la liste (array) des classes des parents de l'entité
	 * @param boolean $short = false
	 * @return array
	 */
	public function getParentsClassNames($short = false) {
		$class = new ReflectionClass($this->getClass());
		$parents = array();
		while($class = $class->getParentClass()) {
			(boolean) $short ?
				$parents[] = $class->getShortName():
				$parents[] = $class->getName();
		}
		return $parents;
	}

	/**
	 * Renvoie la liste (array) des classes des parents de l'entité
	 * @param boolean $short = false
	 * @return array
	 */
	public function getParentsShortNames() {
		return $this->getParentsClassNames(true);
	}

	/**
	 * Renvoie le nom de la classe (short name par défaut)
	 * @param boolean $short = false
	 * @return string
	 */
	public function getClass($short = false) {
		$class = new ReflectionClass(get_called_class());
		return (boolean) $short ?
			$class->getShortName():
			$class->getName();
	}

	/**
	 * Renvoie le nom court de la classe
	 * @return string
	 */
	public function getClassShortName($class) {
		if(is_object($class)) $class = get_class($class);
		if(is_string($class)) {
			$shortName = explode(self::ASLASH, $class);
			return end($shortName);
		}
		return false;
	}




	/**
	 * Initialise le service
	 * @param boolean $result = true
	 * @param mixed $data = null
	 * @param string $message = null
	 * @return aeReponse
	 */
	public function initAeReponse($result = true, $data = null, $message = null) {
		$this->setResult((boolean) $result);
		$this->setData($data);
		$this->setMessage((string) $message);
		return $this;
	}

	// GETTERS

	/**
	 * Get result
	 * @return boolean
	 */
	public function getResult() {
		return $this->data["result"];
	}

	/**
	 * Get message
	 * @return string
	 */
	public function getMessage() {
		return $this->data["message"];
	}

	/**
	 * Get data
	 * @return mised
	 */
	public function getData() {
		return $this->data["data"];
	}

	/**
	 * Get data type
	 * @return string
	 */
	public function getDataType() {
		return gettype($this->data["data"]);
	}

	/**
	 * Get aeReponse as JSON data
	 * @param boolean $reset = false
	 * @return string
	 */
	public function getJSONreponse($reset = false) {
		$r = new JsonResponse(json_encode($this->data));
		if((boolean) $reset) $this->initAeReponse();
		return $r;
	}

	// SETTERS

	/**
	 * Set result
	 * @param boolean $result
	 * @return aeReponse
	 */
	public function setResult($result) {
		$this->data["result"] = (boolean) $result;
		return $this;
	}

	/**
	 * Set message
	 * @param string $message
	 * @return aeReponse
	 */
	public function setMessage($message = null) {
		if($message == null) $message = "";
		$this->data["message"] = (string) $message;
		return $this;
	}

	/**
	 * Set data
	 * @param mixed $data
	 * @return aeReponse
	 */
	public function setData($data = null) {
		$this->data["data"] = $data;
		return $this;
	}


}