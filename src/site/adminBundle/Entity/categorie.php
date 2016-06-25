<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\nested;

use site\adminBundle\services\aetools;
use site\adminBundle\services\aeDebug;
use \Exception;
use \DateTime;

/**
 * categorie
 *
 * @ORM\Entity
 * @ORM\Table(name="categorie", options={"comment":"collections hiérarchisables d'éléments. Diaporamas, catégories, etc."})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\categorieRepository")
 */
class categorie extends nested {

	const CLASS_CATEGORIE = 'categorie';

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(name="nom", type="string", length=64)
	 * @Assert\NotBlank(message = "Vous devez donner un nom à la catégorie.")
	 * @Assert\Length(
	 *      min = "2",
	 *      max = "64",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;

	/**
	 * @var integer
	 * @ORM\Column(name="lvl", type="integer", nullable=false, unique=false)
	 */
	protected $lvl;

	/**
	 * @var boolean
	 * @ORM\Column(name="open", type="boolean", nullable=false, unique=false)
	 */
	protected $open;

	/**
	 * Classes d'entités accceptées pour subEntitys
	 * @var string
	 * @ORM\Column(name="accept", type="text", nullable=true, unique=false)
	 */
	protected $accepts;

	/**
	 * type de catégorie
	 * @var string
	 * @ORM\Column(name="type", type="string", length=64, nullable=false, unique=false)
	 */
	protected $type;

	// Liste des termes valides pour accept
	protected $accept_list;
	protected $type_description;
	protected $type_list;

	// NESTED VIRTUAL GROUPS
	// les noms doivent commencer par "$group_" et finir par "Parents" (pour les parents) ou "Childs" (pour les enfants)
	// et la partie variable doit comporter au moins 3 lettres
	// reconnaissance auto par : "#^(add|remove|get)(Group_).{3,}(Parent|Child)(s)?$#" (self::VIRTUALGROUPS_PARENTS_PATTERN et self::VIRTUALGROUPS_CHILDS_PATTERN)
	protected $group_nestedsParents;
	protected $group_nestedsChilds;
	// parent categorie
	protected $group_categorie_parentParents;
	protected $group_categorie_parentChilds;
	// pages web
	protected $group_pagewebsParents;
	protected $group_pagewebsChilds;

	public function __construct() {
		parent::__construct();
		$this->lvl = 0;
		$this->open = false;
		// init
		$this->accept_list = null;
		$this->type_description = null;
		$this->type_list = null;
		$this->initTypes();
		$this->setType(array_keys($this->type_list)[0]);
	}

	public function initTypes() {
		// Description selon parameters (labo_parameters.yml)
		if(!is_array($this->accept_list) || !is_array($this->type_description) || !is_array($this->type_list)) {
			$aetools = new aetools();
			$description = $aetools->getLaboParam(self::CLASS_CATEGORIE);
			// variables…
			$this->accept_list = $description['descrition']['defaults']['accepts'];
			$this->type_description = $description['descrition']['types'];
			$this->type_list = array();
			foreach ($this->type_description as $key => $value) {
				$this->type_list[$key] = $value['nom'];
				$this->accept_list = array_unique(array_merge($this->accept_list, $value['accepts']));
			}
		}
		return $this;
	}

	/**
	 * @ORM\PostLoad
	 * @return categorie
	 */
	public function PostLoad() {
		$this->initTypes();
		return $this;
	}

	public function getNestedAttributesParameters() {
		$this->initTypes();
		$new = array(
			'categorie_parent' => array(			// groupe articles => group_articlesParents / group_imagesChilds
				'data-limit' => 1,					// nombre max. d'enfants / 0 = infini
				'class' => array('categorie'),		// classes acceptées (array) / null = toutes les classes de nested
				'required' => false,
				),
			'nesteds' => array(
				'data-limit' => 0,
				'class' => $this->getAccepts(),
				'required' => false,
				),
			'pagewebs' => array(
				'data-limit' => 12,
				'class' => array('pageweb'),
				'required' => false,
				),
			);
		return array_merge(parent::getNestedAttributesParameters(), $new);
	}


	/**
	 * @Assert\IsTrue(message="La catégorie n'est pas conforme.")
	 */
	public function isCategorieValid() {
		$result = true;
		// if($this->getType() == null) $result = false;
		return $result;
	}

	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 * 
	 * Check categorie
	 * @return array
	 */
	public function check() {
		$this->setLvl();
		if($this->getLvl() > 0) $this->setType($this->getRootParent()->getType());
		parent::check();
	}

	/**
	 * Un élément par défaut dans la table est-il obligatoire ?
	 * @return boolean
	 */
	public function isDefaultNullable() {
		return true;
	}

	public function __get($property) {
		// echo('<p>Call to __GET::'.$property.' !</p>');
		return $this->$property;
	}

	public function __set($property, $values) {
		// echo('<p>Call to __SET::'.$property.' !</p>');
		parent::__set($property, $values);
		// echo("<p>SET $property</p>");
	}

	public function __call($method, $arguments) {
		// echo('<p>Call to __CALL::'.$method.' !</p>');
		parent::__call($method, $arguments);
		// echo("<p>CALL $method</p>");
		if($method == 'setGroup_categorie_parentParents') {
			$this->setType();
			$this->setLvl();
			$this->setCouleur($this->getRootParent()->getCouleur());
		}
	}

	/**
	 * Get nestedChilds
	 * @param boolean $excludeNotAccepts = false
	 * @return ArrayCollection 
	 */
	public function getNestedChilds($excludeNotAccepts = false) {
		if(!$excludeNotAccepts) return $this->nestedChilds;
		// $accepts = $this->getAcceptsList();
		return $this->getChildsByTypes();
	}

	public function getParent() {
		$parent = $this->group_categorie_parentParents->toArray();
		$parent = reset($parent);
		return is_object($parent) ? $parent : null;
	}

	/**
	 * Get array list of parents
	 * @return array
	 */
	public function getParents() {
		if(count($this->group_categorie_parentParents) > 0) {
			$arrayOfCP = $this->group_categorie_parentParents->toArray();
			$parent = reset($arrayOfCP);
			$parents = array();
			$parentsparents = $parent->getParents();
			if(count($parentsparents) > 0) $parents = $parentsparents;
			$parents[] = $parent;
			return $parents;
		}
		return array();
	}
	/**
	 * Get inversed array list of parents
	 * @return array
	 */
	public function getParents_inverse($andHimself = false) {
		return array_reverse($this->getParents());
	}

	/**
	 * Get root parent (with lvl = 0)
	 * @return categorie
	 */
	public function getRootParent() {
		$rootParent = $this->getParents();
		return count($rootParent) > 0 ? reset($rootParent) : null;
	}

	/**
	 * has parents
	 * @return boolean
	 */
	public function hasParents() {
		return count($this->getParents()) > 0;
	}

	/**
	 * has parent $parent (or has at least on parent, if $parent is null)
	 * @param nested $parent = null
	 * @return boolean
	 */
	public function hasCategorieParent(categorie $parent = null) {
		$parents = new ArrayCollection($this->getParents());
		return $parent == null ? $this->hasParents() : $parents->contains($parent);
	}

	/**
	 * Get only nested class children. 
	 * Can define witch classes in $classes array of shortnames (or one class in a string shortname). 
	 * @param mixed $classes = []
	 * @return array
	 */
	public function getChildsByTypes($classes = []) {
		if(count((array)$classes) < 1) $classes = $this->getAccepts();
		// if(in_array(self::CLASS_CATEGORIE, $classes)) unset($classes[self::CLASS_CATEGORIE]);
		$nesteds = array();
		foreach ($this->group_nestedsChilds as $nested) {
			if(in_array($nested->getClassName(), (array)$classes)) $nesteds[] = $nested;
		}
		// return array_unique($nesteds);
		return array_unique($nesteds);
	}

	public function getAllChildsByTypes($classes = [], $limit = 100) {
		if(count((array)$classes) < 1) $classes = $this->getAccepts();
		// if(in_array(self::CLASS_CATEGORIE, $classes)) unset($classes[self::CLASS_CATEGORIE]);
		$nesteds = $this->getChildsByTypes((array)$classes);
		if($limit > 0) foreach ($this->getAllCategorieChilds() as $child) {
			$nesteds = array_merge($nesteds, $child->getAllChildsByTypes((array)$classes, $limit - 1));
		}
		return array_unique($nesteds);
	}

	public function getCategorieChilds() {
		return $this->group_categorie_parentChilds;
	}

	public function getAllCategorieChilds($limit = 100) {
		$allChilds = $this->getCategorieChilds()->toArray();
		if($limit > 0) foreach ($allChilds as $child) {
			$allChilds = array_merge($allChilds, (array)$child->getAllCategorieChilds($limit - 1));
		}
		return array_unique($allChilds);
	}

	/**
	 * Set Level
	 * @return categorie
	 */
	public function setLvl($lvl = null) {
		$this->lvl = $lvl == null ? count($this->getParents()) : (integer) $lvl;
		return $this;
	}

	/**
	 * Get Level
	 * @return integer
	 */
	public function getLvl() {
		return $this->lvl;
	}

	/**
	 * is Root categorie
	 * @return boolean
	 */
	public function isRoot() {
		return $this->lvl == 0;
	}

	public function getAcceptsList() {
		if(!is_array($this->accept_list)) $this->init();
		return $this->accept_list;
	}

	/**
	 * set accepts
	 * @param json/array $accepts = null
	 * @return categorie
	 */
	public function setAccepts() {
		if(!is_array($this->type_description)) $this->initTypes();
		if(array_key_exists($this->getType(), $this->type_description)) $this->accepts = json_encode($this->type_description[$this->getType()]['accepts']);
			else $this->accepts = json_encode(array());
		return $this;
	}

	/**
	 * has accepts
	 * @param array $accepts
	 * @param boolean $hasOne = false
	 * @return boolean
	 */
	public function hasAccepts($accepts, $hasOne = false) {
		if(is_string($accepts)) $accepts = array($accepts);
		$typeAccepts = $this->getAccepts();
		if($hasOne) foreach($accepts as $accept) {
			if(in_array($accept, $typeAccepts)) return true;
		} else foreach($accepts as $accept) {
			if(!in_array($accept, $typeAccepts)) return false;
		}
		return !$hasOne;
	}

	/**
	 * get accepts
	 * @return array
	 */
	public function getAccepts() {
		if(!is_string($this->accepts)) return array();
		return json_decode($this->accepts);
	}

	/**
	 * get not accepts
	 * @return array
	 */
	public function getNotAccepts() {
		return array_diff($this->getAcceptsList(), $this->getAccepts());
	}

	/**
	 * Renvoie la liste des types de contenu de la catégorie disponibles
	 * @return array
	 */
	public function getTypeList() {
		if(!is_array($this->type_list)) $this->init();
		return $this->type_list;
	}

	/**
	 * Renvoie le type de contenu de la catégorie
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Set type de contenu de la catégorie
	 * @param string $type
	 * @return categorie
	 */
	public function setType($type = null) {
		// ajoute le type du parent en priorité
		if($type == null && $this->getParent() instanceOf categorie) {
			// get parent type
			$this->type = $this->getParent()->getType();
		} else $this->type = $type;
		// if(array_key_exists($type, $this->getTypeList())) {
		// 	$this->type = $type;
		// } else {
		// 	throw new Exception("Ce type n'existe pas : ".json_encode($type), 1);
		// }
		if(array_key_exists($type, $this->getTypeList())) {
			// ajoute le type à ses categories enfants
			foreach($this->group_categorie_parentChilds as $child) {
				$child->setType($this->type);
			}
			// refresh accepts
			$this->setAccepts();
			// suppression des subEntitys hors type / hors accept
			foreach($this->getChildsByTypes($this->getNotAccepts()) as $child) {
				$nestedposition = $this->getNestedposition($this, $child, "nesteds");
				$this->removeNestedpositionChild($nestedposition);
			}
		}
		return $this;
	}


	/**
	 * Set open
	 * @return categorie 
	 */
	public function setOpen($open = true) {
		$this->open = (bool) $open;
		return $this;
	}

	/**
	 * Get open
	 * @return boolean 
	 */
	public function getOpen() {
		return $this->open;
	}

	/**
	 * Get open as text for JStree
	 * @return string
	 */
	public function getOpenText() {
		return $this->open ? 'open' : 'closed';
	}

	/**
	 * Toggle open
	 * @return boolean 
	 */
	public function toggleOpen() {
		$this->open = !$this->open;
		return $this->open;
	}

	/**
	 * Set open to TRUE
	 * @return categorie 
	 */
	public function open() {
		$this->open = true;
		return $this;
	}

	/**
	 * Set open to FALSE
	 * @return categorie 
	 */
	public function close() {
		$this->open = false;
		return $this;
	}


}
