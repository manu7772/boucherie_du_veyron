<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
// use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\nested;
use site\adminBundle\Entity\subentity;
use site\adminBundle\Entity\nestedposition;

use \DateTime;
use \Exception;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\nestedRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="class_name", type="string")
 * @ORM\DiscriminatorMap({"item" = "item", "tier" = "tier", "media" = "media", "rawfile" = "rawfile", "categorie" = "categorie", "article" = "article", "fiche" = "fiche", "pageweb" = "pageweb", "boutique" = "boutique", "marque" = "marque", "reseau" = "reseau", "image" = "image", "pdf" = "pdf"})
 * 
 * @ORM\HasLifecycleCallbacks()
 */
abstract class nested extends subentity {

	const VIRTUALGROUPS_PARENTS_PATTERN = '#^(set|add|remove|get)(Group_)(.{3,})(Parent)(s)?$#';
	const VIRTUALGROUPS_CHILDS_PATTERN = '#^(set|add|remove|get)(Group_)(.{3,})(Child)(s)?$#';
	const VIRTUALGROUPS_ALL_PATTERN = '#^(group_)(.{3,})(Parent|Child)(s)?$#';

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	protected $class_name;

	// NESTED

	/**
	 * @var array
	 * @ORM\OneToMany(targetEntity="site\adminBundle\Entity\nestedposition", orphanRemoval=true, mappedBy="child", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true, unique=false)
	 */
	protected $nestedpositionParents;

	/**
	 * @var array
	 * @ORM\OneToMany(targetEntity="site\adminBundle\Entity\nestedposition", orphanRemoval=true, mappedBy="parent", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true, unique=false)
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	protected $nestedpositionChilds;

	// NESTED VIRTUAL DATA
	// virtuals
	protected $nestedParents;
	protected $nestedChilds;
	// NESTED VIRTUAL GROUPS
	// les noms doivent commencer par "$group_" et finir par "Parents" (pour les parents) ou "Childs" (pour les enfants)
	// et la partie variable doit comporter au moins 3 lettres
	// reconnaissance auto par : "#^(add|remove|get)(Group_).{3,}(Parent|Child)(s)?$#" (self::VIRTUALGROUPS_PARENTS_PATTERN et self::VIRTUALGROUPS_CHILDS_PATTERN)
	protected $group_imagesParents;
	protected $group_imagesChilds;
	// parent categorie
	// protected $group_categorie_parentParents;
	// protected $group_categorie_parentChilds;

	public function __construct() {
		parent::__construct();
		$this->nestedpositionParents = new ArrayCollection();
		$this->nestedpositionChilds = new ArrayCollection();
		// NESTED
		$this->initNestedAttributes();
	}

	public function devecho($text) {
		echo($text);
	}

	/**
	 * Get names of attributes for nested data
	 * renvoie array de type attribut => groupe : 'group_imagesParents' => 'images'
	 * @return array
	 */
	public function getNestedAttributes() {
		$attr = get_object_vars($this);
		$result = array();
		foreach($attr as $name => $value) if(preg_match('#^(group_)(.{3,})(Parents|Childs)$#', $name)) $result[$name] = preg_replace('#^(group_)(.{3,})(Parents|Childs)$#', '${2}', $name);
		return $result;
	}

	public function getNestedAttributesParameters() {
		return array(
			'images' => array(					// groupe images => group_imagesParents / group_imagesChilds
				'data-limit' => 0,				// nombre max. d'enfants / 0 = infini
				'class' => array('image'),		// classes acceptées (array) / null = toutes les classes de nested
				'required' => false,
				),
			);
	}


	public function getClass_name() {
		return $this->class_name;
	}

	/**
	 * @Assert\IsTrue(message="L'entité nested n'est pas conforme.")
	 */
	public function isNestedValid() {
		return true;
	}

	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 */
	public function check() {
		// parent
		parent::check();
	}


	/**
	 * @ORM\PostLoad
	 */
	public function postLoadConstructor() {
		$this->initNestedAttributes();
		$this->initNesteds(null);
	}

	protected function initNestedAttributes() {
		$this->nestedParents = new ArrayCollection();
		$this->nestedChilds = new ArrayCollection();
		// virtual groups
		foreach($this->getNestedAttributes() as $attribute => $group) {
			$this->$attribute = new ArrayCollection();
		}
		return $this;
	}

	/**
	 * init nested values
	 * $onlyThisGroup :
	 *  - string : updates only this group
	 *  - null : updates all groups
	 *  - false : does not update groups
	 * @param string $onlyThisGroup
	 * @return nested
	 */
	public function initNesteds($onlyThisGroup = null) {
		$groups = $this->getNestedAttributesParameters();
		// PARENTS
		foreach($this->nestedpositionParents as $link) {
			if($link->getChild() !== $this) throw new Exception("Link parent error !!", 1);
			if($link->getParent() != null) $this->addNestedParent($link->getParent(), $link->getGroupName());
		}
		// CHILDS
		foreach($this->nestedpositionChilds as $link) {
			if($link->getParent() !== $this) throw new Exception("Link child error !!", 1);
			if($link->getChild() != null) $this->addNestedChild($link->getChild(), $link->getGroupName());
		}
		return $this;
	}

	/**
	 * Get nestedposition
	 * @param nested $parent
	 * @param nested $child
	 * @param string $group
	 * @return nestedposition
	 */
	public function getNestedposition(nested $parent, nested $child, $group = null) {
		$result = false;
		foreach($this->nestedpositionParents as $link) {
			if($link->getParent() == $parent && $link->getChild() == $child && ($link->getGroupName() == $group || $group == null)) return $link;
		}
		foreach($this->nestedpositionChilds as $link) if($link->getGroupName() == $group || $group == null) {
			if($link->getParent() == $parent && $link->getChild() == $child) return $link;
		}
		return $result;
	}

	/**
	 * Lien parent <=> child existe ?
	 * @param nested $parent
	 * @param nested $child
	 * @param string $group
	 * @return boolean
	 */
	public function hasNestedposition(nested $parent, nested $child, $group = null) {
		return $this->getNestedposition($parent, $child, $group) != false ? true : false;
	}

	public function getPositionFromHisParent(nested $parent, $group = null) {
		foreach($this->nestedpositionParents as $link) {
			if($link->isParentGroup($parent, $group) && $link->getChild() == $this) return $link->getPosition();
		}
		return false;
	}

	/**
	 * has Parent ?
	 * @param nested $parent
	 * @return boolean
	 */
	public function hasParent(nested $parent, $group = null) {
		$result = false;
		foreach($this->nestedpositionParents as $link) {
			if($link->getParent() == $parent && $link->getChild() == $this && ($link->getGroupName() == $group || $group == null)) $result = true;
		}
		return $result;
	}

	/**
	 * has Child ?
	 * @param nested $child
	 * @return boolean
	 */
	public function hasChild(nested $child, $group = null) {
		$result = false;
		foreach($this->nestedpositionChilds as $link) {
			if($link->getChild() == $child && ($link->getGroupName() == $group || $group == null)) $result = true;
		}
		return $result;
	}


	/**
	 * Get first child in group
	 * @return nested
	 */
	public function getNestedFirstChild($group = null) {
		foreach($this->getNestedpositionChilds() as $link) {
			if($link->getGroupName() == $group && $link->getPosition() == 0) return $link->getChild();
		}
		return false;
	}


	public function __isset($name) {
		return array_key_exists($name, $this->getNestedAttributes());
	}

	public function __get($property) {
		return $this->$property;
	}

	public function __set($property, $values) {
		if(is_array($values)) $value1 = $values[0];
			else $value1 = $values;
		if(!property_exists($this, $property)) throw new Exception('Invalid property '.json_encode($property).' !', 1);
		$short = preg_replace(self::VIRTUALGROUPS_ALL_PATTERN, '${2}', $property);
		$hierarchy = preg_replace(self::VIRTUALGROUPS_ALL_PATTERN, '${3}', $property);
		// $hierarchy == 'Child' ? $hierarchy_inverse = 'Parent' : $hierarchy_inverse = 'Child';
		if($value1 instanceOf nested) $value1 = new ArrayCollection(array($value1));
		if(is_array($value1)) $value1 = new ArrayCollection($value1);
		if(!$value1 instanceOf ArrayCollection) throw new Exception('Error while operating on '.json_encode($property).' property : argument must be ArrayCollection or instance of "site\adminBundle\Entity\nested", but '.gettype($value1).' given.', 1);
		// $this->devecho('<p>Removes… ('.count($this->__get($property)).' -> ');
		foreach($this->$property as $value) if(!$value1->contains($value)) {
			// $this->$property->remove($value);
			$this->{'removeNested'.$hierarchy}($value);
			$link = $this->getNestedposition($this, $value, $short);
			if($link != false) $this->{'removeNestedposition'.$hierarchy}($link);
			// $this->devecho('*');
		}
		// $this->devecho(')</p>');
		// $this->devecho('<p>Adds… ('.count($this->__get($property)).' -> ');
		foreach($value1 as $key => $value) {
			if($value instanceOf nested) {
				if(in_array($value->getClassName(), $this->getNestedAttributesParameters()[$short]['class'])) {
					$this->$property->add($value);
					$this->{'addNested'.$hierarchy}($value);
					if($hierarchy == 'Child') {
						// attribute as parent
						if(!$this->hasNestedposition($this, $value, $short)) {
							$nestedposition = new nestedposition();
							$nestedposition->setParentChild($this, $value, $short);
							// $this->devecho('<strong>'.$key.'</strong> '.$value.' <i>('.$short.')</i> /');
						}
					} else {
						// attribute as child
						if(!$this->hasNestedposition($value, $this, $short)) {
							$nestedposition = new nestedposition();
							$nestedposition->setParentChild($value, $this, $short);
							// $this->devecho('<strong>'.$key.'</strong> '.$this.' <i>('.$short.')</i> /');
						}
					}
				} else {
					throw new Exception("Element of type ".json_encode(gettype($value))." is not a valid attribute, should be ".json_encode($this->getNestedAttributesParameters()[$short]['class']).".", 1);
				}
			} else {
				throw new Exception("Element of type ".json_encode(gettype($value)).' is not instance of site\adminBundle\Entity\nested.', 1);
			}
		}
		// $this->devecho(')</p>');
		// $this->devecho('<p>Ok…</p>');
		return $this;
	}

	public function __call($method, $arguments) {
		// 1. - Nested virtual groups
		//      $arguments[0] = nestedposition
		if(preg_match(self::VIRTUALGROUPS_PARENTS_PATTERN, $method)) {
			// le groupe est reconnu pour opérations sur les parents
			$group = preg_replace(self::VIRTUALGROUPS_PARENTS_PATTERN, '${3}', $method);						// 'images'
			switch (preg_replace(self::VIRTUALGROUPS_PARENTS_PATTERN, '${1}', $method)) {
				case 'add':
					if(!$arguments[0] instanceOf nestedposition) throw new Exception('Error while calling '.json_encode($method).' : first argument must be instance of site\adminBundle\Entity\nestedposition', 1);
					$this->addNestedpositionParent($arguments[0]);
					break;
				case 'remove':
					if(!$arguments[0] instanceOf nestedposition) throw new Exception('Error while calling '.json_encode($method).' : first argument must be instance of site\adminBundle\Entity\nestedposition', 1);
					$this->removeNestedpositionParent($arguments[0]);
					break;
				case 'set':
					$this->__set(lcfirst(preg_replace('#^set#', '', $method)), $arguments[0]);
					break;
				case 'get':
					return $this->__get(lcfirst(preg_replace("#^get#", '', $method)));
					break;
			}
		} else if(preg_match(self::VIRTUALGROUPS_CHILDS_PATTERN, $method)) {
			// le groupe est reconnu pour opérations sur les enfants
			$group = preg_replace(self::VIRTUALGROUPS_CHILDS_PATTERN, '${3}', $method);						// 'images'
			switch (preg_replace(self::VIRTUALGROUPS_CHILDS_PATTERN, '${1}', $method)) {
				case 'add':
					if(!$arguments[0] instanceOf nestedposition) throw new Exception('Error while calling '.json_encode($method).' : first argument must be instance of site\adminBundle\Entity\nestedposition', 1);
					$this->addNestedpositionChild($arguments[0]);
					break;
				case 'remove':
					if(!$arguments[0] instanceOf nestedposition) throw new Exception('Error while calling '.json_encode($method).' : first argument must be instance of site\adminBundle\Entity\nestedposition', 1);
					$this->removeNestedpositionChild($arguments[0]);
					break;
				case 'set':
					$this->__set(lcfirst(preg_replace('#^set#', '', $method)), $arguments[0]);
					break;
				case 'get':
					return $this->__get(lcfirst(preg_replace("#^get#", '', $method)));
					break;
			}
		} else {
			// return new ArrayCollection();
			throw new Exception(json_encode($method)." n'est pas un champ disponible !", 1);
		}
	}


	/**
	 * set parent position at $position / returns true if operation done, if not, returns false
	 * @param nested $parent
	 * @param integer $position
	 * @return boolean
	 */
	public function setNestedPosition_position(nested $parent, $group = null, $position) {
		foreach($this->nestedpositionParents as $link) if($link->isParentGroup($parent, $group)) {
			$link->setPosition((integer) $position);
			return true;
		}
		return false;
	}

	/**
	 * set first in parent position
	 * @param nested $parent
	 * @return integer 
	 */
	public function setNestedPosition_first(nested $parent, $group = null) {
		return $this->setNestedPosition_position($parent, $group, 0);
	}

	/**
	 * set last in parent position
	 * @param nested $parent
	 * @return integer 
	 */
	public function setNestedPosition_last(nested $parent, $group = null) {
		return $this->setNestedPosition_position($parent, $group, -1);
	}


	/**
	 * Add nestedpositionParents
	 * @param nestedposition $nestedposition
	 * @return nested
	 */
	public function addNestedpositionParent(nestedposition $nestedposition) {
		if($nestedposition->getChild() !== $this)
			throw new Exception("Link parent error : link child ".json_encode($nestedposition->getChild())." is wrong. Should be ".json_encode($this)." !!", 1);
		if(!$this->nestedpositionParents->contains($nestedposition)) {
			$this->nestedpositionParents->add($nestedposition);
			// echo('<p>addNestedpositionParent : parent is '.$nestedposition->getParent().' / group is '.$nestedposition->getGroupName().'</p>');
			$this->addNestedParent($nestedposition->getParent(), $nestedposition->getGroupName());
		}
		return $this;
	}

	/**
	 * Remove nestedpositionParents
	 * @param nestedposition $nestedposition
	 * @return boolean
	 */
	public function removeNestedpositionParent(nestedposition $nestedposition) {
		if($nestedposition->getChild() !== $this)
			throw new Exception("Link parent error : link child ".json_encode($nestedposition->getChild())." is wrong. Should be ".json_encode($this)." !!", 1);
		$r = false;
		if($this->nestedpositionParents->contains($nestedposition)) {
			// $parent = $nestedposition->getParent();
			// $parent->removeNestedpositionChild($nestedposition);
			$r = $this->nestedpositionParents->removeElement($nestedposition);
			if($r) $this->removeNestedParent($nestedposition->getParent(), $nestedposition->getGroupName());
		}
		return $r;
	}

	/**
	 * Get nestedpositionParents
	 * @return ArrayCollection 
	 */
	public function getNestedpositionParents() {
		return $this->nestedpositionParents;
	}


	/**
	 * Add nestedpositionChild
	 * @param nestedposition $nestedposition
	 * @return nested
	 */
	public function addNestedpositionChild(nestedposition $nestedposition) {
		if($nestedposition->getParent() !== $this)
			throw new Exception("Link child error : link parent ".json_encode($nestedposition->getParent())." is wrong. Should be ".json_encode($this)." !!", 1);
		if(!$this->nestedpositionChilds->contains($nestedposition)) {
			$this->nestedpositionChilds->add($nestedposition);
			$this->addNestedChild($nestedposition->getChild(), $nestedposition->getGroupName());
		}
		return $this;
	}

	/**
	 * Remove nestedpositionChilds
	 * @param nestedposition $nestedposition
	 * @return boolean
	 */
	public function removeNestedpositionChild(nestedposition $nestedposition) {
		if($nestedposition->getParent() !== $this)
			throw new Exception("Link child error : link parent ".json_encode($nestedposition->getParent())." is wrong. Should be ".json_encode($this)." !!", 1);
		$r = false;
		if($this->nestedpositionChilds->contains($nestedposition)) {
			// $child = $nestedposition->getChild();
			// $child->removeNestedpositionParent($nestedposition);
			$r = $this->nestedpositionChilds->removeElement($nestedposition);
			if($r) $this->removeNestedChild($nestedposition->getChild(), $nestedposition->getGroupName());
		}
		return $r;
	}

	/**
	 * Get nestedpositionChilds
	 * @return ArrayCollection 
	 */
	public function getNestedpositionChilds() {
		return $this->nestedpositionChilds;
	}



	/**
	 * Add nestedParent
	 * @param nested $nestedParent
	 * @return nested
	 */
	public function addNestedParent(nested $nestedParent, $group = null) {
		if(!$this->nestedParents->contains($nestedParent)) {
			$this->nestedParents->add($nestedParent);
		}
		if($group != null) {
			$groupAttr = 'group_'.$group.'Parents';
			$groups = $this->getNestedAttributesParameters();
			if(
				property_exists($this, $groupAttr)
				&& array_key_exists($group, $groups)
				&& (!$this->$groupAttr->contains($nestedParent))
				) {
					// echo('<p>addNestedParent : '.$groupAttr.' ('.get_class($this->$groupAttr).' ['.count($this->$groupAttr).']) => '.$nestedParent->getClassName().' (name : '.$nestedParent.')</p>');
					if($nestedParent->getId() != null) $this->$groupAttr->add($nestedParent);
				}
		}
		return $this;
	}

	/**
	 * Remove nestedParent
	 * @param nested $nestedParent
	 * @return boolean
	 */
	public function removeNestedParent(nested $nestedParent, $group = null) {
		$r = false;
		// teste si d'autres liens (d'autres groupes) sont présents
		$alreadyLinked = false;
		foreach($this->getNestedpositionParents() as $link) {
			if($link->isParentGroup($nestedParent, $group)) $alreadyLinked = true;
		}
		if($this->nestedParents->contains($nestedParent) && !$alreadyLinked) {
			return $this->nestedParents->removeElement($nestedParent);
		}
		if($group != null) {
			$groupAttr = 'group_'.$group.'Parents';
			if(property_exists($this, $groupAttr) && ($this->$groupAttr->contains($nestedParent))) {
				$r = $this->$groupAttr->removeElement($nestedParent);
			}
		}
		return $r;
	}

	/**
	 * Get nestedParents
	 * @return ArrayCollection 
	 */
	public function getNestedParents() {
		return $this->nestedParents;
	}

	/**
	 * Get nestedParents by class
	 * @return array 
	 */
	public function getNestedParentsByClass($classes = []) {
		if(count((array)$classes) == 0) {
			return $this->getNestedParents();
		} else {
			$result = array();
			foreach ((array)$classes as $classe) {
				foreach ($this->getNestedParents() as $parent) {
					if($parent->getClassName() == $classe) $result[] = $parent;
				}
			}
			return array_unique($result);
		}
	}

	/**
	 * Get ALL nestedParents by class
	 * @return array 
	 */
	public function getAllNestedParentsByClass($classes = [], $limit = 100) {
		$nestedParents = $this->getNestedParentsByClass((array)$classes);
		foreach((array)$nestedParents as $parent) if($limit > 0) {
			$nestedParents = array_merge((array)$nestedParents, (array)$parent->getNestedParentsByClass((array)$classes, $limit - 1));
		}
		return array_unique((array)$nestedParents);
	}

	// /**
	//  * Get nestedParents by type
	//  * @return array 
	//  */
	// public function getNestedParentsByType($types = []) {
	// 	if(count((array)$types) == 0) {
	// 		return $this->getNestedParents();
	// 	} else {
	// 		$result = array();
	// 		foreach((array)$types as $type) {
	// 			foreach ($this->getNestedParents() as $parent) {
	// 				if($parent->getType() == $type) $result[] = $parent;
	// 			}
	// 		}
	// 		return array_unique($result);
	// 	}
	// }

	/**
	 * Add nestedChild
	 * @param nested $nestedChild
	 * @return nested
	 */
	public function addNestedChild(nested $nestedChild, $group = null) {
		if(!$this->nestedChilds->contains($nestedChild)) {
			$this->nestedChilds->add($nestedChild);
		}
		if($group != null) {
			$groupAttr = 'group_'.$group.'Childs';
			$groups = $this->getNestedAttributesParameters();
			if(
				property_exists($this, $groupAttr)
				&& array_key_exists($group, $groups)
				&& (!$this->$groupAttr->contains($nestedChild))
				) {
					// echo('<p>addNestedChild : '.$groupAttr.' ('.get_class($this->$groupAttr).' ['.count($this->$groupAttr).']) => '.$nestedChild->getClassName().' (name : '.$nestedChild.')</p>');
					if($nestedChild->getId() != null) $this->$groupAttr->add($nestedChild);
				}
		}
		return $this;
	}

	/**
	 * Remove nestedChild
	 * @param nested $nestedChild
	 * @return boolean
	 */
	public function removeNestedChild(nested $nestedChild, $group = null) {
		$r = false;
		// teste si d'autres liens (d'autres groupes) sont présents
		$alreadyLinked = false;
		foreach($this->getNestedpositionChilds() as $link) {
			if($link->isChildGroup($nestedChild, $group)) $alreadyLinked = true;
		}
		if($this->nestedChilds->contains($nestedChild) && !$alreadyLinked) {
			return $this->nestedChilds->removeElement($nestedChild);
		}
		if($group != null) {
			$groupAttr = 'group_'.$group.'Childs';
			if(property_exists($this, $groupAttr) && ($this->$groupAttr->contains($nestedChild))) {
				$r = $this->$groupAttr->removeElement($nestedChild);
			}
		}
		return $r;
	}

	/**
	 * Get nestedChilds
	 * @param boolean $excludeNotAccepts = false
	 * @return ArrayCollection 
	 */
	public function getNestedChilds($excludeNotAccepts = false) {
		return $this->nestedChilds;
	}

	/**
	 * Get ALL nestedChilds
	 * @param boolean $excludeNotAccepts = false
	 * @return array 
	 */
	public function getAllNestedChilds($excludeNotAccepts = false, $limit = 100) {
		$nestedChilds = $this->getNestedChilds($excludeNotAccepts);
		if($nestedChilds instanceOf ArrayCollection) $nestedChilds = $nestedChilds->toArray();
		foreach($nestedChilds as $child) if($limit > 0) {
			$nestedChilds = array_merge($nestedChilds, $child->getAllNestedChilds($excludeNotAccepts, $limit - 1));
		}
		return array_unique((array)$nestedChilds);
	}




}