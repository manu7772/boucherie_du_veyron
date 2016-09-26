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

	/**
	 * @ORM\ManyToOne(targetEntity="site\adminsiteBundle\Entity\categorie")
	 * @ORM\JoinColumn(nullable=true, unique=false)
	 */
	protected $categorieParent;

	// Liste des termes valides pour accept
	protected $accept_list;
	protected $type_description;
	protected $type_list;

	// NESTED VIRTUAL GROUPS
	// les noms doivent commencer par "$group_" et finir par "Parents" (pour les parents) ou "Childs" (pour les enfants)
	// et la partie variable doit comporter au moins 3 lettres
	// reconnaissance auto par : "#^(add|remove|get)(Group_).{3,}(Parent|Child)(s)?$#" (self::VIRTUALGROUPS_PARENTS_PATTERN et self::VIRTUALGROUPS_CHILDS_PATTERN)
	protected $passBySetParent;
	// nesteds
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
		$this->categorieParent = null;
		$this->passBySetParent = false;
		$this->initTypes();
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
	public function initNestedAttributes() {
		parent::initNestedAttributes();
		$this->initTypes();
		// $parents = $this->getParentsByGroup('categorie_parent');
		// $this->categorieParent = count($parents > 0) ? reset($parents) : null;
		$this->passBySetParent = false;
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
		$result = $result && is_string($this->getType());
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
		parent::check();
	}

	/**
	 * Un élément par défaut dans la table est-il obligatoire ?
	 * @return boolean
	 */
	public function isDefaultNullable() {
		return true;
	}

	/**
	 * Set arguments for a property
	 * @param string $method
	 * @param array $arguments
	 * @return categorie
	 */
	public function __call($method, $arguments) {
		if($method !== 'setGroup_categorie_parentParents') return parent::__call($method, $arguments);
		$categorie = null;
		$noArgs = true;
		// echo('<h3>method '.json_encode($method).'</h3>');
		if(isset($arguments[0])) {
			if($arguments[0]->count() > 0) {
				$categorie = $arguments[0]->first();
				if($categorie instanceOf categorie) {
					parent::__call($method, $arguments);
					if(!$this->passBySetParent) $this->setCategorieParent($categorie);
					// if($this->getLvl() > 0) $this->setCouleur($this->getRootParent()->getCouleur());
					$noArgs = false;
				}
			}
		}
		if(!$this->passBySetParent && $noArgs) $this->setCategorieParent(null);

		$this->setType();
		$this->setLvl();
		// echo('<h3>Type '.json_encode($this->getType()).'</h3>');
		// echo('<h3>Level '.json_encode($this->getLvl()).'</h3>');
		return $this;
	}

	/////////////////////////
	// CATEGORIE PARENT(S)
	/////////////////////////

	/**
	 * Get parent
	 * @return categorie
	 */
	public function getCategorieParent() {
		return $this->categorieParent;
	}

	/**
	 * Set parent
	 * @param categorie $categorie
	 * @return nested
	 */
	public function setCategorieParent(categorie $categorie = null) {
		// categorie_parent
		$this->passBySetParent = true;
		$this->categorieParent = $categorie;
		$this->__call('setGroup_categorie_parentParents', new ArrayCollection((array)$categorie));
		$this->passBySetParent = false;
		if($categorie instanceOf categorie) {
			// as parent, so in first position !
			$categorie->setNestedPosition_first($this, 'categorie_parent');
		}
		return $this;
	}

	/**
	 * Get array list of parents
	 * @return array
	 */
	public function getCategorieParents() {
		$parent = $this->getCategorieParent();
		return is_object($parent) ? array_merge(array($parent), $parent->getCategorieParents()) : array();
	}

	/**
	 * Get inversed array list of parents
	 * @return array
	 */
	public function getCategorieParents_inverse() {
		return array_reverse($this->getCategorieParents());
	}

	/**
	 * has parents
	 * @return boolean
	 */
	public function hasCategorieParents() {
		return count($this->getCategorieParents()) > 0;
	}

	/**
	 * has parent $parent (or if has at least on parent, if $parent is null)
	 * @param nested $parent = null
	 * @return boolean
	 */
	public function hasCategorieParent(categorie $parent = null) {
		if($parent === null) {
			return $this->hasCategorieParents();
		}
		$parents = new ArrayCollection($this->getCategorieParents());
		return $parents->contains($parent);
	}

	/**
	 * Get root parent (with lvl = 0)
	 * @return categorie
	 */
	public function getRootParent() {
		$rootParent = $this->getCategorieParents();
		return count($rootParent) > 0 ? reset($rootParent) : null;
	}



	/////////////////////////
	// NESTEDS
	/////////////////////////

	/**
	 * Get nestedChilds ($group_nestedsChilds)
	 * @return ArrayCollection 
	 */
	public function getNestedChilds() {
		return $this->getChildsByGroup('nesteds');
	}

	/**
	 * Get ALL nestedChilds ($group_nestedsChilds)
	 * @param boolean $excludeNotAccepts = false
	 * @return ArrayCollection 
	 */
	public function getAllNestedChilds() {
		$nesteds = $this->getChildsByGroup('nesteds');
		foreach ($nesteds as $nested) {
			$nesteds = array_merge($nesteds, $nested->getAllNestedChilds());
		}
		return array_unique($nesteds, SORT_STRING);
	}

	/**
	 * Get nested childs of type $types. 
	 * Can define witch types in $types array (or one type in a string shortname). 
	 * @param mixed $types = []
	 * @return array
	 */
	public function getNestedChildsByTypes($types = []) {
		$types = (array)$types;
		if(count($types) < 1) $types = $this->getAccepts();
		// if(in_array(self::CLASS_CATEGORIE, $types)) unset($types[self::CLASS_CATEGORIE]);
		$nesteds = array();
		foreach($this->getNestedChilds() as $nested) {
			if(in_array($nested->getType(), $types)) $nesteds[] = $nested;
		}
		// return array_unique($nesteds);
		return array_unique($nesteds, SORT_STRING);
	}

	/**
	 * Get ALL nested childs of type $types. 
	 * Can define witch types in $types array (or one type in a string shortname). 
	 * @param mixed $types = []
	 * @param integer $limit = 25
	 * @return array
	 */
	public function getAllNestedChildsByTypes($types = [], $limit = 25) {
		if(is_string($types)) $types = array($types);
		if(count($types) < 1) $types = $this->getAccepts();
		// if(in_array(self::CLASS_CATEGORIE, $types)) unset($types[self::CLASS_CATEGORIE]);
		$nesteds = $this->getNestedChildsByTypes($types);
		if($limit > 0) {
			foreach($this->getAllCategorieChilds() as $child) {
				$nesteds = array_merge($nesteds, $child->getAllNestedChildsByTypes($types, $limit - 1));
			}
		}
		return array_unique($nesteds, SORT_STRING);
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
			return $unique ? array_unique($result, SORT_STRING) : $result;
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
		return $unique ? array_unique($result, SORT_STRING) : $result;
	}

	/**
	 * Get ALL nestedChilds by group
	 * @param string $group = null
	 * @param integer $limit = 25
	 * @return array 
	 */
	public function getAllNestedChildsByGroup($group = null, $addAlias = false, $limit = 25) {
		$nestedChilds = $this->getChildsByGroup($group);
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
		return array_unique($nestedChilds, SORT_STRING);
	}

	// public function getAllNestedChildsByGroup($group, $classes = null) {
	// 	$childs = array();
	// 	foreach($this->getNestedChilds() as $child) {
	// 		$childs = array_merge($childs, $this->getChildsByGroup($group, $classes));
	// 	}
	// 	return $childs;
	// }

	/////////////////////////
	// CATEGORIES
	/////////////////////////

	/**
	 * Get child categories
	 * @return array
	 */
	public function getCategorieChilds($addAlias = false) {
		$alias = array();
		if($addAlias === true) $alias = $this->getAlias();
		$result = array_merge($alias, $this->getChildsByGroup('categorie_parent'));
		return array_unique($result, SORT_STRING);
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
		return array_unique($allCategorieChilds, SORT_STRING);
	}

	/////////////////////////
	// ALIAS
	/////////////////////////

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
	 * @param integer $lvl
	 * @return categorie
	 */
	public function setLvl($lvl = null) {
		// $this->lvl = $lvl == null ? count($this->getCategorieParents()) : (integer) $lvl;
		$mem = $this->lvl;
		if(is_integer($lvl)) {
			$this->lvl = $lvl;
		} else {
			$parent = $this->getCategorieParent();
			if(is_object($parent)) {
				$this->lvl = $parent->getLvl() + 1;
			} else {
				$this->lvl = 0;
			}
		}
		if($mem != $this->lvl) {
			foreach ($this->getCategorieChilds() as $child) {
				$child->setLvl($this->lvl + 1);
			}
		}
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
		return $this->lvl === 0;
		// return !$this->hasCategorieParents();
	}

	public function getAcceptsList() {
		if(!is_array($this->accept_list)) $this->initTypes();
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
	public function hasAccepts($accepts, $hasAtLeastOne = false) {
		$accepts = (array)$accepts;
		$typeAccepts = $this->getAccepts();
		if($hasAtLeastOne) foreach($accepts as $accept) {
			if(in_array($accept, $typeAccepts)) return true;
		} else foreach($accepts as $accept) {
			if(!in_array($accept, $typeAccepts)) return false;
		}
		return !$hasAtLeastOne;
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
		if(!is_array($this->type_list)) $this->initTypes();
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
	public function setType($type = null, $level = null) {
		$mem = $this->type;
		if($level === null) $level = 0;
		// ajoute le type du parent en priorité
		if($type == null) {
			// get parent type
			if($this->getCategorieParent() instanceOf categorie) {
				$this->type = $this->getCategorieParent()->getType();
			} else {
				throw new Exception('This categorie has no parent, so type can not be null. Please choose a type in '.json_encode(array_keys($this->getTypeList())).'!', 1);
			}
		} else {
			if(!array_key_exists($type, $this->getTypeList())) throw new Exception('Error set Type for categorie: type '.json_encode($type).' does not exist! Please, choose in '.json_encode(array_keys($this->getTypeList())).'.', 1);
			$this->type = $type;
		}
		if($mem != $this->type) {
			// refresh accepts
			$this->setAccepts();
			// the same type for children
			// WARNING ! Not aliases --> recursivity hazard !! …and it should not be true !
			foreach($this->getCategorieChilds(false) as $child) {
				$child->setType($this->type, $level + 1);
			}
			// deleting children wich is not accepted
			foreach($this->getNestedChildsByTypes($this->getNotAccepts()) as $child) {
				$nestedposition = $this->getNestedposition($this, $child, "categorie_parent");
				$this->removeNestedpositionChild($nestedposition);
			}
		}
		// echo('<p>Type : '.$this->getType().'</p>');
		// echo('<p>Accepts : '.implode(', ', $this->getAccepts()).'</p>');
		// echo('<p>------------------------------------------</p>');
		return $this;
	}


	/**
	 * Set open
	 * @return categorie 
	 */
	public function setOpen($open = true) {
		$this->open = $open;
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
		return $this->open ? 'open.open' : 'open.closed';
	}

	/**
	 * Toggle open
	 * @return boolean 
	 */
	public function toggleOpen() {
		$this->open = !$this->open;
		return $this->open;
	}


}