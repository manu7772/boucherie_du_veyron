<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\article;

use \DateTime;
use \ReflectionClass;

/**
 * articleposition
 *
 * @ORM\Entity
 * @ORM\Table(name="article_position")
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\HasLifecycleCallbacks
 */
 // * @ORM\Entity(repositoryClass="site\adminBundle\Entity\articlepositionRepository")
class articleposition {

	// const CLASS_ARTICLEPOSITION = 'articleposition';

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\article", inversedBy="articlesParents", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
	 * @Gedmo\SortableGroup
	 */
	protected $parent;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\article", inversedBy="articlesChilds", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="child_id", referencedColumnName="id")
	 */
	protected $child;

	/**
	 * @ORM\Column(type="integer")
	 * @Gedmo\SortablePosition
	 */
	private $position;


	public function __construct() {
		$this->parent = null;
		$this->child = null;
		// $this->position = 0;
	}

	public function __toString() {
		return $this->getParent()->getId().'/'.$this->getChild()->getId();
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
		return $this->getParent()->getId().'/'.$this->getChild()->getId();
	}

	/**
	 * @ORM\PreRemove
	 */
	public function onRemove() {
		$this->parent->removeChildren($this);
		$this->child->removeParent($this);
	}

	/**
	 * Set parent
	 * @param parent $parent
	 * @return articleposition
	 */
	public function setParent(parent $parent) {
		$this->parent = $parent;
		$parent->addChildren($this);
		return $this;
	}

	/**
	 * Get parent
	 * @return parent 
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Set child
	 * @param child $child
	 * @return articleposition
	 */
	public function setChild(child $child) {
		$this->child = $child;
		$child->addParent($this);
		return $this;
	}

	/**
	 * Get child
	 * @return child 
	 */
	public function getChild() {
		return $this->child;
	}

	/**
	 * Set position
	 * @param integer $position
	 * @return articleposition
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
