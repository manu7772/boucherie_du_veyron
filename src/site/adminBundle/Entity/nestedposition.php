<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\nested;
use site\adminBundle\Entity\nestedgroup;

use \DateTime;
use \ReflectionClass;

/**
 * nestedposition
 *
 * @ORM\Entity
 * @ORM\Table(name="nested_position")
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\nestedpositionRepository")
 * @ORM\HasLifecycleCallbacks
 */
 // * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
class nestedposition {

	// const CLASS_SUBENTITYPOSITION = 'nestedposition';

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\nested", inversedBy="nestedpositionChilds", cascade={"persist"})
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=false, unique=false)
	 */
	protected $parent;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\nested", inversedBy="nestedpositionParents", cascade={"persist"})
	 * @ORM\JoinColumn(name="child_id", referencedColumnName="id", nullable=false, unique=false)
	 */
	protected $child;

	/**
	 * @ORM\Id
	 * @ORM\Column(name="nestedgroup", type="string", nullable=false, unique=false)
	 * @Gedmo\SortableGroup
	 */
	protected $group;

	/**
	 * @ORM\Column(type="integer")
	 * @Gedmo\SortablePosition
	 */
	private $position;


	public function __construct() {
		$this->parent = null;
		$this->child = null;
		$this->nestedgroup = null;
		$this->group = null;
	}

	public function __toString() {
		return $this->getNom();
	}

	public function getNom() {
		return $this->getParent()->getNom().' / '.$this->getChild()->getNom();
	}

    // abstract public function getClassName();
    public function getClassName() {
        return $this->getClass(true);
    }

	/**
	 * Renvoie le nom de la classe (short name par défaut)
	 * @param boolean $short = false
	 * @return string
	 */
	public function getClass($short = false) {
		$class = new ReflectionClass(get_called_class());
		return $short ?
			$class->getShortName():
			$class->getName();
	}

	public function getId() {
		return $this->getParent()->getId().'-'.$this->getChild()->getId();
	}

	/**
	 * @ORM\PreRemove
	 */
	public function onRemove() {
		$this->parent->removeNestedpositionChild($this);
		$this->child->removeNestedpositionParent($this);
	}

	/**
	 * Set parent and child at the same time
	 * @param nested $parent
	 * @param nested $child
	 * @return nestedposition
	 */
	public function setParentChild(nested $parent, nested $child, $group = null) {
		$this->setParent($parent, $group);
		$this->setChild($child);
		$parent->addNestedpositionChild($this);
		$child->addNestedpositionParent($this);
		return $this;
	}

	/**
	 * Set parent
	 * @param nested $parent
	 * @return nestedposition
	 */
	public function setParent(nested $parent, $group = null) {
		$this->parent = $parent;
		$this->setGroupName($group);
		if($this->getChild() != null) $parent->addNestedpositionChild($this);
		return $this;
	}

	/**
	 * Get parent
	 * @return nested 
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Set child
	 * @param nested $child
	 * @return nestedposition
	 */
	public function setChild(nested $child) {
		$this->child = $child;
		if($this->getParent() != null) $child->addNestedpositionParent($this);
		return $this;
	}

	/**
	 * Get child
	 * @return nested 
	 */
	public function getChild() {
		return $this->child;
	}

	/**
	 * Set group
	 * @param string $group
	 * @return nestedposition
	 */
	public function setGroup($group = null) {
		$this->group = $group;
		return $this;
	}

	/**
	 * Get group
	 * @return string
	 */
	public function getGroup() {
		return $this->group;
	}

	/**
	 * Is parent and group ?
	 * @param nested $parent
	 * @param string $group = null
	 * @return boolean
	 */
	public function isParentGroup(nested $parent, $group = null) {
		return $parent === $this->getParent() && $group == $this->getGroupName();
	}

	/**
	 * Is child and group ?
	 * @param nested $child
	 * @param string $group = null
	 * @return boolean
	 */
	public function isChildGroup(nested $child, $group = null) {
		return $child === $this->getChild() && $group == $this->getGroupName();
	}

	public function setGroupName($group = null) {
		$group == null ? $this->setGroup($this->parent->getId()) : $this->setGroup($this->parent->getId().'_'.$group);
	}

	/**
	 * Get group name (without parent id) 
	 * group = 8_images => groupName = "images" / if no group, returns null
	 * @return string / null if no group
	 */
	public function getGroupName() {
		$exp = explode('_', $this->group, 2);
		return isset($exp[1]) ? $exp[1] : null;
	}

	/**
	 * Set position
	 * @param integer $position
	 * @return nestedposition
	 */
	public function setPosition($position) {
		$this->position = $position;
	}

	/**
	 * Get position
	 * @return integer
	 */
	public function getPosition() {
		return $this->position;
	}



}
