<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\baseEntity;

use \DateTime;
use \Exception;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\baseSubEntityRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="class_name", type="string")
 * @ORM\DiscriminatorMap({"item" = "item", "tier" = "tier", "media" = "media", "rawfile" = "rawfile", "categorie" = "categorie", "article" = "article", "fiche" = "fiche", "pageweb" = "pageweb", "boutique" = "boutique", "marque" = "marque", "reseau" = "reseau", "image" = "image", "pdf" = "pdf"})
 * 
 * @ORM\HasLifecycleCallbacks()
 */
abstract class baseSubEntity extends baseEntity {

	const CLASS_CATEGORIE = 'categorie';

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\categorie", mappedBy="childrens", cascade={"persist"})
	 */
	protected $parents;

	/**
	 * https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/sortable.md
	 * @Gedmo\SortablePosition
	 * @ORM\Column(type="integer", nullable=false)
	 */
	protected $position;

	/**
	 * @var string
	 * @ORM\Column(name="descriptif", type="text", nullable=true, unique=false)
	 */
	protected $descriptif;

	/**
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\statut")
	 * @ORM\JoinColumn(nullable=false, unique=false)
	 */
	protected $statut;

	/**
	 * @var boolean
	 * @ORM\Column(name="deletable", type="boolean", nullable=false, unique=false)
	 */
	protected $deletable;

	/**
	 * @var array
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\tag", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $tags;

    /**
     * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\image", orphanRemoval=true, cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
     */
    protected $image;

	/**
	 * @var string
	 * @ORM\Column(name="couleur", type="string", length=24, nullable=false, unique=false)
	 */
	protected $couleur;

	protected $class_name;
	protected $old_values;

	public function __construct() {
		parent::__construct();
		$this->old_values = array();
		$this->parents = new ArrayCollection();
		$this->position = 0;
		$this->descriptif = null;
		$this->statut = null;
		$this->deletable = true;
		$this->tags = new ArrayCollection();
		$this->image = null;
		$this->couleur = "rgba(255,255,255,1)";
	}

	/**
	 * @ORM\PostLoad
	 */
	public function memOldValues($addedfields = null) {
		$fields = array('parents');
		if(count($addedfields) > 0 && is_array($addedfields)) $fields = array_unique(array_merge($fields, $addedfields));
		foreach ($fields as $field) {
			if(is_object($this->$field) && method_exists($this->$field, "toArray")) $this->old_values[$field] = $this->$field->toArray();
				else $this->old_values[$field] = $this->$field;
		}
		return $this;
	}

	public function getOldValues($field = null) {
		if(is_string($field) && isset($this->old_values[$field])) return $this->old_values[$field];
			else return $this->old_values;
	}


	public function getClass_name() {
		return $this->class_name;
	}

	/**
	 * Renvoie l'image principale
	 * @return image
	 */
	public function getMainMedia() {
		return $this->getImage();
	}

	/**
	 * Get keywords
	 * @return string 
	 */
	public function getKeywords() {
		return implode($this->getTags()->toArray(), ', ');
	}

	/**
	 * Get keywords
	 * @return array 
	 */
	public function getArrayKeywords() {
		return $this->getTags()->toArray();
	}

	/**
	 * @Assert\IsTrue(message="L'entité subBase n'est pas conforme.")
	 */
	public function isBaseSubEntityValid() {
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
	 * Add parent 
	 * @param categorie $parent
	 * @return baseSubEntity
	 */
	public function addParent(categorie $parent) {
		if(!$this->parents->contains($parent)) $this->parents->add($parent);
		// $parent->addChildren($this);
		return $this;
	}

	/**
	 * Remove parent
	 * @param categorie $parent
	 * @return boolean
	 */
	public function removeParent(categorie $parent) {
		// $parent->removeChildren($this);
		return $this->parents->removeElement($parent);
	}

	/**
	 * @ORM\PreRemove
	 * Remove all parents
	 * @return baseSubEntity
	 */
	public function removeParents() {
		foreach ($this->getParents() as $parent) {
			$this->removeParent($parent);
			// $this->removeSubEntity($parent);
		}
		return $this;
	}

	/**
	 * Get parents
	 * @return array
	 */
	public function getParents($includeThis = false) {
		return $this->parents->toArray();
	}

	/**
	 * Renvoie les parents récursifs de l'entité. 
	 * Inclut ou non l'entité elle-même dans la liste. Le premier élément du tableau est le parent direct, et ainsi de suite. Le dernier élément est le parent root.
	 * @param boolean $includeThis = false
	 * @return array
	 */
	public function getAllParents($includeThis = false) {
		$parents = array();
		if($includeThis === true) $parents = array($this);
		foreach ($this->getParents() as $parent) {
			$parents = array_merge($parents, $parent->getAllParents(true));
		}
		return $parents;
	}

	/**
	 * Renvoie les parents récursifs de l'entité en tableau inversé (contraire de getParents())
	 * Inclut ou non l'entité elle-même dans la liste. Le premier élément du tableau est le parent root, et ainsi de suite. Le dernier élément est le premier parent direct.
	 * @param boolean $includeThis = false
	 * @return array
	 */
	public function getParentsInverse($includeThis = false) {
		return array_reverse($this->getParents($includeThis));
	}

	/**
	 * Renvoie les parents récursifs de l'entité en tableau inversé (contraire de getParents())
	 * Inclut ou non l'entité elle-même dans la liste. Le premier élément du tableau est le parent root, et ainsi de suite. Le dernier élément est le premier parent direct.
	 * @param boolean $includeThis = false
	 * @return array
	 */
	public function getAllParentsInverse($includeThis = false) {
		return array_reverse($this->getAllParents($includeThis));
	}

	/**
	 * Renvoie le parent ROOT ($level = 0) ou de niveau $level. 
	 * !!! METHODE À DÉVELOPPER !!!
	 * NULL si aucun parent n'a été trouvé. 
	 * @param integer $level = 0
	 * @return baseSubEntity
	 */
	// public function getRootParents($level = 0) {
		// !!! METHODE À DÉVELOPPER !!!
		// $parents = $this->getParentsInverse(false);
		// if(count($parents) >= ($level + 1)) return $parents[$level];
		// return null;
	// }

	/**
	 * Set position
	 * @param integer $position
	 * @return baseSubEntity
	 */
	public function setPosition($position) {
		$this->position = $position;
		return $this;
	}

	/**
	 * Get position
	 * @return integer
	 */
	public function getPosition() {
		return $this->position;
	}

	/**
	 * Set descriptif
	 * @param string $descriptif
	 * @return baseSubEntity
	 */
	public function setDescriptif($descriptif = null) {
		$this->descriptif = $descriptif;
		if(strip_tags(preg_replace('#([[:space:]])+#', '', $this->descriptif)) == '') $this->descriptif = null;
		return $this;
	}

	/**
	 * Get descriptif
	 * @return string 
	 */
	public function getDescriptif() {
		return $this->descriptif;
	}

	/**
	 * Set statut
	 * @param statut $statut
	 * @return baseSubEntity
	 */
	public function setStatut(statut $statut) {
		$this->statut = $statut;
		return $this;
	}

	/**
	 * Get statut
	 * @return statut 
	 */
	public function getStatut() {
		return $this->statut;
	}

	/**
	 * Set deletable
	 * @param string $deletable
	 * @return baseEntity
	 */
	public function setDeletable($deletable) {
		$this->deletable = (bool) $deletable;
		return $this;
	}

	/**
	 * Get deletable
	 * @return boolean
	 */
	public function getDeletable() {
		return $this->deletable;
	}

	/**
	 * Add tag
	 * @param tag $tag
	 * @return baseSubEntity
	 */
	public function addTag(tag $tag) {
		$this->tags->add($tag);
		return $this;
	}

	/**
	 * Remove tag
	 * @param tag $tag
	 * @return boolean
	 */
	public function removeTag(tag $tag) {
		return $this->tags->removeElement($tag);
	}

	/**
	 * Get tags
	 * @return ArrayCollection 
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * Get tags as a string
	 * @return string 
	 */
	public function getTagsText($glue = ', ') {
		if(!is_string($glue)) $glue = ', ';
		return implode($glue, $this->tags);
	}

	/**
	 * Set image - PROPRIÉTAIRE
	 * @param image $image
	 * @return baseSubEntity
	 */
	public function setImage(image $image = null) {
		if($this->image != null && $image == null) {
			$this->image->setElement(null);
		}
		$this->image = $image;
		if($this->image != null) {
			$this->image->setElement($this);
			$this->image->setStatut($this->getStatut());
		}
		return $this;
	}

	/**
	 * Get image - PROPRIÉTAIRE
	 * @return image $image
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * Set couleur
	 * @param string $couleur
	 * @return baseSubEntity
	 */
	public function setCouleur($couleur) {
		$this->couleur = $couleur;
		return $this;
	}

	/**
	 * Get couleur
	 * @return string 
	 */
	public function getCouleur() {
		return $this->couleur;
	}



}