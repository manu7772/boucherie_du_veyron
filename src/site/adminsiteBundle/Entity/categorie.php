<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// JMS Serializer
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\MaxDepth;
use JMS\Serializer\Annotation\Groups;

use Labo\Bundle\AdminBundle\services\aeDebug;
use Labo\Bundle\AdminBundle\services\aeSnake;
use Labo\Bundle\AdminBundle\services\aeData;

use Labo\Bundle\AdminBundle\Entity\nested;

use \Exception;
use \DateTime;

/**
 * categorie
 *
 * @ExclusionPolicy("all")
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\categorieRepository")
 * @ORM\Table(name="categorie", options={"comment":"collections hiérarchisables d'éléments. Diaporamas, catégories, etc."})
 * @ORM\HasLifecycleCallbacks
 */
class categorie extends nested {

	const CLASS_CATEGORIE	= 'categorie';
	const LIMIT				= 50;

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
	 * @Expose
	 * @Groups({"complete", "export"})
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

	protected $passBySetParent;
	protected $forceType;


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
		$this->forceType = false;
	}


	/**
	 * @ORM\PostLoad
	 */
	public function load() {
		$this->forceType = false;
	}

	/**
	 * Un élément par défaut dans la table est-il optionnel ?
	 * @return boolean
	 */
	public function isDefaultNullable() {
		return true;
	}

	/**
	 * Peut'on attribuer plusieurs éléments par défaut ?
	 * true 		= illimité
	 * integer 		= nombre max. d'éléments par défaut
	 * false, 0, 1 	= un seul élément
	 * @return boolean
	 */
	public function isDefaultMultiple() {
		return true;
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
	 * Set arguments for a property
	 * @param string $method
	 * @param array $arguments
	 * @return categorie
	 */
	public function __call($method, $arguments) {
		$this->addGetIfNoAction($method);
		// if($method !== 'set'.$this->getSnake()->getPropPrefix().'_categorie_nested'.parent::PARENTS_NAME) {
		// if(!preg_match('#^(set|add|remove)'.$this->getSnake()->getPropPrefix().'_categorie_nested('.implode('|', $this->getSnake()->getHierarchyNames()).')$#', $method)) {
		// 	return parent::__call($method, $arguments);
		// }
		$group = $this->getSnake()->getGroupName($method, false);
		$categorie = $arguments;
		if(is_array($arguments)) $arguments = new ArrayCollection($arguments);
		switch(aeData::decamelize($method)) {
			case 'set_'.$this->getSnake()->getPropPrefix().'_categorie_nested_parent':
			case 'set_'.$this->getSnake()->getPropPrefix().'_categorie_nested_parents':
				if(!($categorie instanceOf categorie)) {
					if($arguments->count() > 0) $categorie = $arguments->first();
						else $categorie = null;
				}
				// DO NOT BREAK HERE !
			case 'add_'.$this->getSnake()->getPropPrefix().'_categorie_nested_parent':
			case 'add_'.$this->getSnake()->getPropPrefix().'_categorie_nested_parents':
				if($categorie instanceOf categorie) {
					// echo('<p>Parent of '.json_encode($this->getNom()).' is categorie '.json_encode($categorie->getNom()).' in group "'.$group.'"</p>');
					parent::__call($method, new ArrayCollection(array($categorie)));
					if(!$this->passBySetParent) $this->setCategorieParent($categorie);
					// if($this->getLvl() > 0) $this->setCouleur($this->getRootParent()->getCouleur());
				}
				// if(!$this->passBySetParent && ($categorie === null)) $this->setCategorieParent(null);
				$this->setType();
				$this->setLvl();
				break;
			case 'set_'.$this->getSnake()->getPropPrefix().'_categorie_nested_child':
			case 'set_'.$this->getSnake()->getPropPrefix().'_categorie_nested_childs':
				if($categorie instanceOf categorie) $categorie = new ArrayCollection(array($categorie));
				if($categorie == null) $categorie = new ArrayCollection(array());
				parent::__call($method, $categorie);
				foreach($categorie as $cat) {
					$cat->setCategorieParent($this);
				}
				break;
			default:
				return parent::__call($method, $arguments);
				break;
		}
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
		// categorie_nested
		$this->passBySetParent = true;
		$this->categorieParent = $categorie;
		// echo('<p>Set parent of '.json_encode($this->getNom()).' is categorie '.json_encode($categorie->getNom()).'</p>');
		$this->__call('set_'.$this->getSnake()->getPropPrefix().'_categorie_nested_parents', new ArrayCollection(array($categorie)));
		$this->passBySetParent = false;
		if($categorie instanceOf categorie) {
			// as parent, so in first position !
			$categorie->setNestedPosition_first($this, 'categorie_nested');
		}
		return $this;
	}

	/**
	 * Get array list of parents
	 * @param boolean $self = false
	 * @return array
	 */
	public function getCategorieParents($self = false) {
		$self = (boolean)$self ? array($this) : array();
		$parent = $this->getCategorieParent();
		return is_object($parent) ? array_merge($self, array($parent), $parent->getCategorieParents(false)) : $self;
	}

	/**
	 * Get inversed array list of parents
	 * @param boolean $self = false
	 * @return array
	 */
	public function getCategorieParents_inverse($self = false) {
		return array_reverse($this->getCategorieParents($self));
	}

	/**
	 * has parents
	 * @return boolean
	 */
	public function hasCategorieParents() {
		return count($this->getCategorieParents(false)) > 0;
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
		$parents = new ArrayCollection($this->getCategorieParents(false));
		return $parents->contains($parent);
	}

	/**
	 * Get root parent (with lvl = 0)
	 * @return categorie
	 */
	public function getRootParent() {
		$rootParent = $this->getCategorieParents(false);
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
	public function getAllNestedChildsByTypes($types = [], $limit = null) {
		if($limit === null) $limit = self::LIMIT;
		$types = array($types);
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
				if(in_array($child->getShortName(), $classes)) $result[] = $child;
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
	public function getAllNestedChildsByGroup($group = null, $addAlias = false, $limit = null) {
		if($limit === null) $limit = self::LIMIT;
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
		$result = array_merge($alias, $this->getChildsByGroup('categorie_nested'));
		return array_unique($result, SORT_STRING);
	}

	/**
	 * Get all child categories
	 * @param integer $limit = 25;
	 * @return array
	 */
	public function getAllCategorieChilds($addAlias = false, $limit = null) {
		if($limit === null) $limit = self::LIMIT;
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
		// $this->lvl = $lvl == null ? count($this->getCategorieParents(false)) : (integer) $lvl;
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
		// other method :
		// return !$this->hasCategorieParents();
	}

	/**
	 * Set description of types and accepts (using info_entites.categorie.types_descrition parameter)
	 * @param array $description
	 * @return categorie
	 */
	public function setTypesDescription($description) {
		$this->accept_list = $description['defaults']['accepts'];
		$this->type_description = $description['types'];
		$type_list = array();
		foreach($this->type_description as $key => $value) {
			$type_list[$key] = $value['nom'];
			$this->accept_list = array_unique(array_merge($this->accept_list, $value['accepts']));
		}
		$this->setTypeList($type_list);
		// echo('<h5 style="color:blue;">setTypesDescription to '.json_encode($this->getType()).' : type_description = '.json_encode($this->type_description).'</h5>');
		// echo('<h5 style="color:blue;">setTypesDescription to '.json_encode($this->getType()).' : accept_list = '.json_encode($this->accept_list).'</h5>');
		// echo('<h5 style="color:blue;">setTypesDescription to '.json_encode($this->getType()).' : type_list = '.json_encode($this->getTypeList()).'</h5>');
		return $this;
	}

	public function getAcceptsList() {
		return $this->accept_list;
	}

	/**
	 * set accepts
	 * @param array $accepts = null
	 * @return categorie
	 */
	public function setAccepts($accepts = null) {
		// echo('<h5 style="color:green;">setAccepts on '.json_encode($this->getNom()).' : type_description = '.json_encode($this->type_description).'</h5>');
		if($accepts === null) {
			if(array_key_exists($this->getType(), $this->type_description)) $this->accepts = json_encode($this->type_description[$this->getType()]['accepts']);
				else $this->accepts = json_encode(array());
		} else if(is_array($accepts)) {
			$this->accepts = json_encode($accepts);
		}
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
	 * Set list of types
	 * @param array $typeList
	 * @return categorie
	 */
	public function setTypeList($typeList) {
		$this->type_list = $typeList;
		return $this;
	}

	/**
	 * Renvoie la liste des types de contenu de la catégorie disponibles
	 * @return array
	 */
	public function getTypeList() {
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
		// echo('<h5 style="color:green;">setType to '.$this->getType().' on '.json_encode($this->getNom()).'</h5>');
		if($mem != $this->type || $this->forceType) {
			// refresh accepts
			$this->setAccepts();
			// the same type for children
			// WARNING ! Not aliases --> recursivity hazard !! …and it should not be true !
			foreach($this->getCategorieChilds(false) as $child) {
				if($this->forceType) $child->checkType();
					else $child->setType($this->type, $level + 1);
			}
			// deleting children wich is not accepted
			foreach($this->getNestedChildsByTypes($this->getNotAccepts()) as $child) {
				$nestedposition = $this->getNestedposition($this, $child, "categorie_nested");
				$this->removeNestedpositionChild($nestedposition);
			}
		}
		// echo('<p>Type : '.$this->getType().'</p>');
		// echo('<p>Accepts : '.implode(', ', $this->getAccepts()).'</p>');
		// echo('<p>------------------------------------------</p>');
		return $this;
	}

	public function checkType() {
		$this->forceType = true;
		$this->setType($this->getType());
		$this->forceType = false;
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