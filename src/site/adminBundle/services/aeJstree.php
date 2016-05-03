<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use site\adminBundle\Entity\categorie;
use \ReflectionClass;

/**
 * Service Jstree
 */
class aeJstree {

    const NAME                  = 'aeJstree';        // nom du service
    const CALL_NAME             = 'aetools.aeJstree'; // comment appeler le service depuis le controller/container

	const SLASH					= '/';				// slash
	const ASLASH 				= '\\';				// anti-slashes

	protected $container;
	protected $data;
	protected $trans;

	const ANIMATION = 500;
	// CORE
	const CORE_CHECK_CALLBACK = true;
	// CORE / THEMES
	const CORE_THEME_NAME = true;
	const CORE_THEME_URL = true;
	const CORE_THEME_DIR = true;
	const CORE_THEME_DOTS = true;
	const CORE_THEME_ICONS = true;
	const CORE_THEME_STRIPES = true;
	const CORE_THEME_VARIANT = true;
	const CORE_THEME_RESPONSIVE = true;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
		$this->trans = $this->container->get('translator');
		$this->data = array();
		return $this;
	}

	public function __toString() {
		try {
			$string = $this->getNom();
		} catch (Exception $e) {
			$string = '…';
		}
		return $string;
	}

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
	 * @Assert\IsTrue(message="validator.jstree.nonConforme")
	 */
	public function isValid() {
		$result = true;
		if(count($this->data) < 1) return false;
		foreach ($this->data as $name => $data) {
			if(!is_object($data['categorie'])) $result = false;
		}
		return $result;
	}

	public function createNew(categorie $categorie, $name = null) {
		if($name == null) $name = 'jstree_'.$categorie->getId();
		$this->data[$name] = array();
		$this->data[$name]['treeData'] = $this->getNewData();
		$this->data[$name]['categorie'] = $categorie;
		return $name;
	}

	/**
	 * Liste des id
	 * @return array
	 */
	public function getIds() {
		return array_keys($this->data);
	}

	public function getTrees() {
		return $this->data;
	}

	public function getTree($name) {
		return $this->data[$name];
	}

	public function getTreeAsJson($name) {
		return json_encode($this->data[$name]);
	}

	public function getData($name) {
		return $this->data[$name]['treeData'];
	}

	public function getDataAsJson($name) {
		return json_encode($this->data[$name]['treeData']);
	}


	public function check($name) {
		//
	}

	// CREATION DATA

	protected function getNewData() {
		return array(
			'core' => array(
				'animation' => self::ANIMATION,
				'check_callback' => self::CORE_CHECK_CALLBACK,
				'themes' => $this->getThemes(),
				),
			'plugins' => $this->getPlugins(),
			'types' => $this->getTypes(),
			);
	}

	protected function getThemes() {
		return array(
			// 'name' => self::CORE_THEME_NAME,
			// 'url' => self::CORE_THEME_URL,
			// 'dir' => self::CORE_THEME_DIR,
			// 'dots' => self::CORE_THEME_DOTS,
			// 'icons' => self::CORE_THEME_ICONS,
			'stripes' => self::CORE_THEME_STRIPES,
			// 'variant' => self::CORE_THEME_VARIANT,
			// 'responsive' => self::CORE_THEME_RESPONSIVE,
			);
	}

	protected function typesList() {
		return array(
			'categorie',
			'article',
			'fiche',
			);
	}

	protected function getTypes() {
		$array = array(
			'default' => array(
				'icon' => 'fa fa-question',
				),
			'forbidden' => array(
				'icon' => 'fa fa-ban text-danger',
				),
			);
		foreach ($this->typesList() as $type) {
			$array[$type] = array('icon' => 'fa '.$this->trans->trans('entite.'.$type, [], 'icon'));
		}
		return $array;
	}

	protected function getPlugins() {
		return array("types", "contextmenu", "dnd");
	}




}