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
 * @ORM\DiscriminatorMap({"item" = "item", "tier" = "tier", "media" = "media", "rawfile" = "rawfile", "article" = "article", "fiche" = "fiche", "pageweb" = "pageweb", "boutique" = "boutique", "marque" = "marque", "reseau" = "reseau", "image" = "image", "pdf" = "pdf"})
 * 
 * @ORM\HasLifecycleCallbacks()
 */
abstract class baseSubEntity extends baseEntity {

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * - PROPRIÉTAIRE
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\categorie", inversedBy="subEntitys", cascade={"persist","remove"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $categories;

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

	public function __construct() {
		parent::__construct();
		$this->descriptif = null;
		$this->statut = null;
		$this->tags = new ArrayCollection();
		$this->image = null;
		$this->couleur = "rgba(255,255,255,1)";
		$this->categories = new ArrayCollection();
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
	 * @Assert\True(message="L'entité n'est pas conforme.")
	 */
	public function isValid() {
		// $img2 = ($this->getImage() == null) || $this->getImage()->isValid();
		// return $img2;
		return true;
	}

	/**
	 * @ORM\PrePersist()
	 * @ORM\PreUpdate()
	 */
	public function check() {
		// if(is_object($this->getImage())) if(!$this->getImage()->isValid()) $this->setImage(null);
		// // copie statuts
		// if(is_object($this->getImage())) $this->getImage()->setStatut($this->statut);
	}

	/**
	 * Get categories - PROPRIÉTAIRE
	 * @return ArrayCollection 
	 */
	public function getCategories() {
		return $this->categories;
	}

	/**
	 * Add categorie - PROPRIÉTAIRE
	 * @param categorie $categorie
	 * @return baseSubEntity
	 */
	public function addCategorie(categorie $categorie = null) {
		if($categorie != null) {
			if(!$this->categories->contains($categorie)) {
				$categorie->addSubEntity($this);
				$this->categories->add($categorie);
			}
		}
		return $this;
	}

	/**
	 * Remove categorie - PROPRIÉTAIRE
	 * @param categorie $categorie
	 * @return boolean
	 */
	public function removeCategorie(categorie $categorie) {
		$categorie->removeSubEntity($this);
		return $this->categories->removeElement($categorie);
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