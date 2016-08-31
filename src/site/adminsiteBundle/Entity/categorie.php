<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use Labo\Bundle\AdminBundle\services\aetools;
use Labo\Bundle\AdminBundle\services\aeDebug;

use Labo\Bundle\AdminBundle\Entity\nested;

use \Exception;
use \DateTime;

/**
 * categorie
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\categorieRepository")
 * @ORM\Table(name="categorie", options={"comment":"collections hiérarchisables d'éléments. Diaporamas, catégories, etc."})
 * @ORM\HasLifecycleCallbacks
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
			foreach($this->type_description as $key => $value) {
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
		$this->initNestedAttributes();
		$this->initTypes();
		return $this;
	}

	public function getNestedAttributesParameters() {
		$new = array(
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
		// control
		if($this->getLvl() == 0 && $this->hasParents()) throw new Exception("Level control error : lvl is ".json_encode($this->getLvl())." and ".json_encode($this->getNom())." has parent(s) : ".json_encode(implode(', ', $this->getParents()))." !", 1);
		if($this->getLvl() > 0 && !$this->hasParents()) throw new Exception("Level control error : lvl is ".json_encode($this->getLvl())." and ".json_encode($this->getNom())." has no parent !", 1);
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
	 * Get nestedChilds ($group_nestedsChilds)
	 * @param boolean $excludeNotAccepts = false
	 * @return ArrayCollection 
	 */
	public function getNestedChilds() {
		return $this->group_nestedsChilds;
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
		$parents = array();
		if(count($this->group_categorie_parentParents) > 0) {
			$arrayOfCP = $this->group_categorie_parentParents->toArray();
			$parent = reset($arrayOfCP);
			$parentsparents = $parent->getParents();
			if(count($parentsparents) > 0) $parents = $parentsparents;
			$parents[] = $parent;
		}
		return $parents;
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
	 * Get nested childs of type $types. 
	 * Can define witch types in $types array (or one type in a string shortname). 
	 * @param mixed $types = []
	 * @return array
	 */
	public function getChildsByTypes($types = []) {
		if(is_string($types)) $types = array($types);
		if(count($types) < 1) $types = $this->getAccepts();
		// if(in_array(self::CLASS_CATEGORIE, $types)) unset($types[self::CLASS_CATEGORIE]);
		$nesteds = array();
		foreach($this->getNestedChilds() as $nested) {
			if(in_array($nested->getType(), $types)) $nesteds[] = $nested;
		}
		// return array_unique($nesteds);
		return array_unique($nesteds);
	}

	/**
	 * Get ALL nested childs of type $types. 
	 * Can define witch types in $types array (or one type in a string shortname). 
	 * @param mixed $types = []
	 * @param integer $limit = 25
	 * @return array
	 */
	public function getAllChildsByTypes($types = [], $limit = 25) {
		if(is_string($types)) $types = array($types);
		if(count($types) < 1) $types = $this->getAccepts();
		// if(in_array(self::CLASS_CATEGORIE, $types)) unset($types[self::CLASS_CATEGORIE]);
		$nesteds = $this->getChildsByTypes($types);
		if($limit > 0) {
			foreach($this->getAllCategorieChilds() as $child) {
				$nesteds = array_merge($nesteds, $child->getAllChildsByTypes($types, $limit - 1));
			}
		}
		return array_unique($nesteds);
	}

	/**
	 * Get child categories
	 * @return array
	 */
	public function getCategorieChilds($addAlias = false) {
		if(!$addAlias) {
			$result = $this->group_categorie_parentChilds->toArray();
		} else {
			$result = array_merge($this->getAlias(), $this->group_categorie_parentChilds->toArray());
		}
		return array_unique($result);
	}

	/**
	 * Get all child categories
	 * @param integer $limit = 25;
	 * @return array
	 */
	public function getAllCategorieChilds($addAlias = false, $limit = 25) {
		$allCategorieChilds = $this->getCategorieChilds($addAlias);
		if($limit > 0) {
			foreach($allCategorieChilds as $categorieChild) {
				$allCategorieChilds = array_merge($allCategorieChilds, $categorieChild->getAllCategorieChilds($addAlias, $limit - 1));
			}
		}
		return array_unique($allCategorieChilds);
	}

	/**
	 * Get nestedParents by class
	 * @return array 
	 */
	public function getNestedChildsByClass($classes = [], $unique = false) {
		if(is_string($classes)) $classes = array($classes);
		if(count($classes) == 0) {
			return $this->getNestedChilds();
		} else {
			$result = array();
			foreach($this->getNestedChilds() as $child) {
				if(in_array($child->getClassName(), $classes)) $result[] = $child;
			}
			return $unique ? array_unique($result) : $result;
		}
	}

	/**
	 * Get ALL nestedParents by class
	 * @return array 
	 */
	public function getAllNestedChildsByClass($classes = [], $unique = false) {
		if(is_string($classes)) $classes = array($classes);
		if(count((array)$classes) == 0) {
			return $this->getAllNestedChildsByClass($classes, $unique);
		} else {
			$result = array();
			foreach($this->getNestedChildsByClass($classes, $unique) as $child)
				$result = array_merge($result, $this->getAllNestedChildsByClass($classes, $unique));
		}
		return $unique ? array_unique($result) : $result;
	}

	/**
	 * Get ALL nestedChilds by group
	 * @param string $group = null
	 * @param integer $limit = 25
	 * @return array 
	 */
	public function getAllNestedChildsByGroup($group = null, $addAlias = false, $limit = 25) {
		$nestedChilds = $this->getNestedChildsByGroup($group, $addAlias);
		if((integer)$limit > 0) {
			if($addAlias == false) {
				foreach($this->getCategorieChilds() as $categorieChild) {
					$nestedChilds = array_merge($nestedChilds, $categorieChild->getAllNestedChildsByGroup($group, $addAlias, (integer)$limit - 1));
				}
			} else {
				foreach($this->getCategorieChilds() as $categorieChild) {
					$nestedChilds = array_merge($nestedChilds, $categorieChild->getAllNestedChildsByGroup($group, $addAlias, (integer)$limit - 1));
				}
				// add alias contents
				foreach ($this->getAlias() as $child) {
					$nestedChilds = array_merge($nestedChilds, $child->getAllNestedChildsByGroup($group, $addAlias, (integer)$limit - 1));
				}
			}
		}
		return array_unique($nestedChilds);
	}

	/**
	 * get alias
	 * @return array
	 */
	public function getAlias() {
		return $this->getNestedChildsByClass('categorie');
	}

	/**
	 * get all alias
	 * @return array
	 */
	public function getAllAlias() {
		$alias = $this->getAlias();
		foreach($this->getAllCategorieChilds(true) as $categorie) if(count($categorie->getAlias()) > 0) {
			$alias = array_merge($alias, $categorie->getAlias());
		}
		return $alias;
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
		// return $this->lvl == 0;
		return !$this->hasParents();
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

		if(array_key_exists($type, $this->getTypeList())) {
			// ajoute le type à ses categories enfants
			foreach($this->getCategorieChilds() as $child) {
				$child->setType($this->type);
			}
			// refresh accepts
			$this->setAccepts();
			// suppression des nesteds hors type et/ou hors accept
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