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
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\articlepositionRepository")
 * @ORM\HasLifecycleCallbacks
 */
 // * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
class articleposition {

	// const CLASS_ARTICLEPOSITION = 'articleposition';

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\article", inversedBy="articlepositionChilds")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
	 * @Gedmo\SortableGroup
	 */
	protected $parent;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\article", inversedBy="articlepositionParents")
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
		$this->parent->removeArticlepositionChild($this);
		$this->child->removeArticlepositionParent($this);
	}

	/**
	 * Set parent and child at the same time
	 * @param article $parent
	 * @param article $child
	 * @return articleposition
	 */
	public function setParentEnfant(article $parent, article $child) {
		$this->parent = $parent;
		$this->child = $child;
		$parent->addArticlepositionChild($this);
		$child->addArticlepositionParent($this);
		return $this;
	}

	/**
	 * Set parent
	 * @param article $parent
	 * @return articleposition
	 */
	public function setParent(article $parent) {
		$this->parent = $parent;
		$parent->addArticlepositionChild($this);
		return $this;
	}

	/**
	 * Get parent
	 * @return article 
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Set child
	 * @param article $child
	 * @return articleposition
	 */
	public function setChild(article $child) {
		$this->child = $child;
		$child->addArticlepositionParent($this);
		return $this;
	}

	/**
	 * Get child
	 * @return article 
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
