<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\categorie;
use site\adminBundle\Entity\baseSubEntity;

use \DateTime;
use \ReflectionClass;

/**
 * categorieposition
 *
 * @ORM\Entity
 * @ORM\Table(name="categorie_subentity")
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\HasLifecycleCallbacks
 */
 // * @ORM\Entity(repositoryClass="site\adminBundle\Entity\categoriepositionRepository")
class categorieposition {

	// const CLASS_CATEGORIEPOSITION = 'categorieposition';

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\categorie", inversedBy="childrens", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="categorie_id", referencedColumnName="id")
	 * @Gedmo\SortableGroup
	 */
	protected $categorie;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\baseSubEntity", inversedBy="parents", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="baseSubEntity_id", referencedColumnName="id")
	 */
	protected $subEntity;

	/**
	 * @ORM\Column(type="integer")
	 * @Gedmo\SortablePosition
	 */
	private $position;


	public function __construct() {
		$this->categorie = null;
		$this->subEntity = null;
		// $this->position = 0;
	}

	public function __toString() {
		return $this->getCategorie()->getId().'/'.$this->getSubEntity()->getId();
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
		return $this->getCategorie()->getId().'/'.$this->getSubEntity()->getId();
	}

	/**
	 * @ORM\PreRemove
	 */
	public function onRemove() {
		$this->categorie->removeChildren($this);
		$this->subEntity->removeParent($this);
	}

	/**
	 * Set categorie
	 * @param categorie $categorie
	 * @return categorieposition
	 */
	public function setCategorie(categorie $categorie) {
		$this->categorie = $categorie;
		$categorie->addChildren($this);
		return $this;
	}

	/**
	 * Get categorie
	 * @return categorie 
	 */
	public function getCategorie() {
		return $this->categorie;
	}

	/**
	 * Set subEntity
	 * @param subEntity $subEntity
	 * @return categorieposition
	 */
	public function setSubEntity(subEntity $subEntity) {
		$this->subEntity = $subEntity;
		$subEntity->addParent($this);
		return $this;
	}

	/**
	 * Get subEntity
	 * @return subEntity 
	 */
	public function getSubEntity() {
		return $this->subEntity;
	}

	/**
	 * Set position
	 * @param integer $position
	 * @return categorieposition
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
