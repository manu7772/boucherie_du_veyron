<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
// use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\baseEntity;
use site\adminBundle\Entity\subentityposition;

use \DateTime;
use \Exception;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\subentityRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="class_name", type="string")
 * @ORM\DiscriminatorMap({"item" = "item", "tier" = "tier", "media" = "media", "site" = "site", "rawfile" = "rawfile", "categorie" = "categorie", "article" = "article", "fiche" = "fiche", "pageweb" = "pageweb", "boutique" = "boutique", "marque" = "marque", "reseau" = "reseau", "image" = "image", "pdf" = "pdf"})
 * 
 * @ORM\HasLifecycleCallbacks()
 */
abstract class subentity extends baseEntity {

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

	public function __construct() {
		parent::__construct();
		$this->descriptif = null;
		$this->statut = null;
		$this->deletable = true;
		$this->tags = new ArrayCollection();
		$this->image = null;
		$this->couleur = "rgba(255,255,255,1)";
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
	 * @Assert\IsTrue(message="L'entité subentity n'est pas conforme.")
	 */
	public function isSubentityValid() {
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
	 * Set descriptif
	 * @param string $descriptif
	 * @return subentity
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
	 * @return subentity
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
	 * Set tags
	 * @param arrayCollection $tags
	 * @return subentity
	 */
	public function setTags(ArrayCollection $tags) {
		// $this->tags->clear();
		// incorporation avec "add" et "remove" au cas où il y aurait des opérations (inverse notamment)
		foreach($this->getTags() as $tag) if(!$tags->contains($tag)) $this->removeTag($tag); // remove
		foreach($tags as $tag) $this->addTag($tag); // add
		return $this;
	}

	/**
	 * Add tag
	 * @param tag $tag
	 * @return subentity
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
	 * Set image
	 * @param image $image
	 * @return subentity
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
	 * Get image
	 * @return image $image
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * Set couleur
	 * @param string $couleur
	 * @return subentity
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