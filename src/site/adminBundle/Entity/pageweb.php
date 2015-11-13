<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\tag;
use site\adminBundle\Entity\media;

use \DateTime;

/**
 * pageweb
 *
 * @ORM\Entity
 * @ORM\Table(name="pageweb")
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\pagewebRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"nom"}, message="pageweb.existe")
 */
class pageweb {

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
	 *      min = "3",
	 *      max = "25",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;

    /**
     * @var boolean
     * @ORM\Column(name="enabled", type="boolean", nullable=false, unique=false)
     */
	/**
	 * @var boolean
	 * @ORM\Column(name="homepage", type="boolean", nullable=false, unique=false)
	 */
	protected $homepage;

    /**
     * @ORM\OneToOne(targetEntity="media", mappedBy="pagewebBackground", cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=true, name="bpagewebBackground_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $background;

	/**
	 * @var string
	 * @ORM\Column(name="code", type="text", nullable=true, unique=false)
	 */
	protected $code;

	/**
	 * @var string
	 * @ORM\Column(name="title", type="string", length=100, nullable=true, unique=false)
	 */
	protected $title;

	/**
	 * @var string
	 * @ORM\Column(name="titreh1", type="string", length=255, nullable=true, unique=false)
	 */
	protected $titreh1;

	/**
	 * @var string
	 * @ORM\Column(name="metatitle", type="text", nullable=true, unique=false)
	 */
	protected $metatitle;

	/**
	 * @var string
	 * @ORM\Column(name="metadescription", type="text", nullable=true, unique=false)
	 */
	protected $metadescription;

	/**
	 * @var string
	 * @ORM\Column(name="modele", type="string", length=255, nullable=true, unique=false)
	 */
	protected $modele;

	/**
	 * @Gedmo\Slug(fields={"nom"})
	 * @ORM\Column(length=128, unique=true)
	 */
	protected $slug;

	/**
	 * @var array
	 * @ORM\ManyToMany(targetEntity="tag", inversedBy="pagewebs")
	 * @ORM\JoinColumn(nullable=true, unique=false)
	 */
	protected $tags;

	/**
	 * @var DateTime
	 * @ORM\Column(name="created", type="datetime", nullable=false)
	 */
	protected $dateCreation;

	/**
	 * @var DateTime
	 * @ORM\Column(name="updated", type="datetime", nullable=true)
	 */
	protected $dateMaj;


	public function __construct() {
		$this->homepage = false;
		$this->dateCreation = new DateTime();
		$this->dateMaj = null;
		$this->background = null;
		$this->tags = new ArrayCollection();
	}


	/**
	 * Get id
	 * @return integer 
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set nom
	 * @param string $nom
	 * @return pageweb
	 */
	public function setNom($nom)
	{
		$this->nom = $nom;
		return $this;
	}

	/**
	 * Get nom
	 * @return string 
	 */
	public function getNom()
	{
		return $this->nom;
	}

	/**
	 * Set homepage
	 *
	 * @param boolean $homepage
	 * @return version
	 */
	public function setHomepage($homepage) {
		is_bool($homepage) ? $this->homepage = $homepage : $this->homepage = false;
		return $this;
	}

	/**
	 * Get homepage
	 *
	 * @return boolean 
	 */
	public function getHomepage() {
		return $this->homepage;
	}

	/**
	 * Set background
	 * @param media $background
	 * @return pageweb
	 */
	public function setBackground(media $background = null)
	{
		$this->background = $background;
		return $this;
	}

	/**
	 * Get background
	 * @return media 
	 */
	public function getBackground()
	{
		return $this->background;
	}

	/**
	 * Set code
	 * @param string $code
	 * @return pageweb
	 */
	public function setCode($code = null)
	{
		$this->code = $code;
		if(trim($this->code) == '') $this->code = null;
		return $this;
	}

	/**
	 * Get code
	 * @return string 
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Set title
	 * @param string $title
	 * @return pageweb
	 */
	public function setTitle($title = null)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * Get title
	 * @return string 
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set titreh1
	 * @param string $titreh1
	 * @return pageweb
	 */
	public function setTitreh1($titreh1 = null)
	{
		$this->titreh1 = $titreh1;
		return $this;
	}

	/**
	 * Get titreh1
	 * @return string 
	 */
	public function getTitreh1()
	{
		return $this->titreh1;
	}

	/**
	 * Set metatitle
	 * @param string $metatitle
	 * @return pageweb
	 */
	public function setMetatitle($metatitle = null)
	{
		$this->metatitle = $metatitle;
		return $this;
	}

	/**
	 * Get metatitle
	 * @return string 
	 */
	public function getMetatitle()
	{
		return $this->metatitle;
	}

	/**
	 * Set metadescription
	 * @param string $metadescription
	 * @return pageweb
	 */
	public function setMetadescription($metadescription = null)
	{
		$this->metadescription = $metadescription;
		return $this;
	}

	/**
	 * Get metadescription
	 * @return string 
	 */
	public function getMetadescription()
	{
		return $this->metadescription;
	}

	/**
	 * Set modele
	 * @param string $modele
	 * @return pageweb
	 */
	public function setModele($modele = null)
	{
		$this->modele = $modele;
		return $this;
	}

	/**
	 * Get modele
	 * @return string 
	 */
	public function getModele()
	{
		return $this->modele;
	}

	/**
	 * Set slug
	 * @param integer $slug
	 * @return pageweb
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
	 * @param DateTime $dateCreation
	 * @return pageweb
	 */
	public function setDateCreation(DateTime $dateCreation) {
		$this->dateCreation = $dateCreation;
		return $this;
	}

	/**
	 * Get dateCreation
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
	 * @param DateTime $dateMaj
	 * @return pageweb
	 */
	public function setDateMaj(DateTime $dateMaj) {
		$this->dateMaj = $dateMaj;
		return $this;
	}

	/**
	 * Get dateMaj
	 * @return DateTime 
	 */
	public function getDateMaj() {
		return $this->dateMaj;
	}

	/**
	 * Add tag
	 * @param tag $tag
	 * @return pageweb
	 */
	public function addTag(tag $tag) {
		$this->tags->add($tag);
		$tag->addPageweb_reverse($this);
		return $this;
	}
	/**
	 * Add tag (reverse)
	 * @param tag $tag
	 * @return pageweb
	 */
	public function addTag_reverse(tag $tag) {
		$this->tags->add($tag);
		return $this;
	}

	/**
	 * Remove tag
	 * @param tag $tag
	 */
	public function removeTag(tag $tag) {
		$this->tags->removeElement($tag);
		$tag->removePageweb_reverse($this);
	}
	/**
	 * Remove tag (reverse)
	 * @param tag $tag
	 */
	public function removeTag_reverse(tag $tag) {
		$this->tags->removeElement($tag);
	}

	/**
	 * Get tags
	 * @return ArrayCollection 
	 */
	public function getTags() {
		return $this->tags;
	}


}
