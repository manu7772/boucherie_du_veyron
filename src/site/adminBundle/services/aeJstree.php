<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use site\adminBundle\Entity\categorie;

/**
 * Service Jstree
 */
class aeJstree {

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