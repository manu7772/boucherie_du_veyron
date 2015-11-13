<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\pageweb;

use \DateTime;

/**
 * tag
 *
 * @ORM\Entity
 * @ORM\Table(name="tag")
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\tagRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"nom"}, message="tag.existe")
 */
class tag {

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;


	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=100, nullable=false, unique=true)
	 * @Assert\NotBlank(message = "entity.notblank.nom")
	 * @Assert\Length(
	 *      min = "2",
	 *      max = "30",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;

	/**
	 * @var array
	 * @ORM\ManyToMany(targetEntity="pageweb", mappedBy="tags")
	 * @ORM\JoinColumn(name="pagewebs_id", referencedColumnName="id", onDelete="SET NULL")
	 */
	protected $pagewebs;

	/**
	 * @Gedmo\Slug(fields={"nom"})
	 * @ORM\Column(length=128, unique=true)
	 */
	protected $slug;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="created", type="datetime", nullable=false)
	 */
	protected $dateCreation;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="updated", type="datetime", nullable=true)
	 */
	protected $dateMaj;


	public function __construct() {
		$this->dateCreation = new DateTime();
		$this->dateMaj = null;
		$this->pagewebs = new ArrayCollection();
	}

	// public function __toString() {
	// 	return "Tags";
	// }

	/**
	 * Get id
	 * @return integer 
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set nom
	 * @param string $nom
	 * @return tag
	 */
	public function setNom($nom) {
		$this->nom = trim($nom);
		return $this;
	}

	/**
	 * Get nom
	 * @return string 
	 */
	public function getNom() {
		return $this->nom;
	}

	/**
	 * Add pageweb
	 * @param pageweb $pageweb
	 * @return tag
	 */
	public function addPageweb(pageweb $pageweb) {
		$this->pagewebs->add($pageweb);
		$pageweb->addTag_reverse($this);
		return $this;
	}
	/**
	 * Add pageweb (reverse)
	 * @param pageweb $pageweb
	 * @return tag
	 */
	public function addPageweb_reverse(pageweb $pageweb) {
		$this->pagewebs->add($pageweb);
		return $this;
	}

	/**
	 * Remove pageweb
	 * @param pageweb $pageweb
	 */
	public function removePageweb(pageweb $pageweb) {
		$this->pagewebs->removeElement($pageweb);
		$pageweb->removeTag_reverse($this);
	}
	/**
	 * Remove pageweb (reverse)
	 * @param pageweb $pageweb
	 */
	public function removePageweb_reverse(pageweb $pageweb) {
		$this->pagewebs->removeElement($pageweb);
	}

	/**
	 * Get pagewebs
	 * @return ArrayCollection 
	 */
	public function getPagewebs() {
		return $this->pagewebs;
	}

	/**
	 * Set slug
	 * @param integer $slug
	 * @return tag
	 */
	public function setSlug($slug) {
		$this->slug = $slug;
		return $this;
	}

	/**
	 * Get slug
	 * @return string
	 */
	public function getSlug() {
		return $this->slug;
	}

	/**
	 * Set dateCreation
	 *
	 * @param DateTime $dateCreation
	 * @return tag
	 */
	public function setDateCreation(DateTime $dateCreation) {
		$this->dateCreation = $dateCreation;
	
		return $this;
	}

	/**
	 * Get dateCreation
	 *
	 * @return DateTime 
	 */
	public function getDateCreation() {
		return $this->dateCreation;
	}

	/**
	 * @ORM\PreUpdate
	 */
	public function updateDateMaj() {
		$this->setDateMaj(new DateTime());
	}

	/**
	 * Set dateMaj
	 *
	 * @param DateTime $dateMaj
	 * @return tag
	 */
	public function setDateMaj(DateTime $dateMaj) {
		$this->dateMaj = $dateMaj;
	
		return $this;
	}

	/**
	 * Get dateMaj
	 *
	 * @return DateTime 
	 */
	public function getDateMaj() {
		return $this->dateMaj;
	}


}
