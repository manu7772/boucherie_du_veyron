<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\tag;
use site\adminBundle\Entity\pageweb;
use site\adminBundle\Entity\article;
use site\adminBundle\Entity\statut;

use \DateTime;

/**
 * categorie
 *
 * @ORM\Entity
 * @ORM\Table(name="categorie")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\categorieRepository")
 * @Gedmo\Tree(type="nested")
 */
class categorie {

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(name="nom", type="string", length=64)
	 * @Assert\NotBlank(message = "Vous devez donner un nom à la catégorie.")
	 * @Assert\Length(
	 *      min = "2",
	 *      max = "64",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;

	/**
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\pageweb")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $pageweb;

	/**
	 * @Gedmo\TreeLeft
	 * @ORM\Column(name="lft", type="integer")
	 */
	protected $lft;

	/**
	 * @Gedmo\TreeLevel
	 * @ORM\Column(name="lvl", type="integer")
	 */
	protected $lvl;

	/**
	 * @Gedmo\TreeRight
	 * @ORM\Column(name="rgt", type="integer")
	 */
	protected $rgt;

	/**
	 * @Gedmo\TreeRoot
	 * @ORM\Column(name="root", type="integer", nullable=true)
	 */
	protected $root;

	/**
	 * @Gedmo\TreeParent
	 * @ORM\ManyToOne(targetEntity="categorie", inversedBy="children", cascade={"persist"})
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $parent;

	/**
	 * @ORM\OneToMany(targetEntity="categorie", mappedBy="parent")
	 * @ORM\OrderBy({"lft" = "ASC"})
	 */
	protected $children;

	/**
	 * @var string
	 * @ORM\Column(name="descriptif", type="text", nullable=true, unique=false)
	 */
	protected $descriptif;

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

	/**
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\article", inversedBy="categories")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $articles;

	/**
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\statut")
	 * @ORM\JoinColumn(nullable=false, unique=false)
	 */
	protected $statut;

	/**
	 * @Gedmo\Slug(fields={"nom"})
	 * @ORM\Column(length=128, unique=true)
	 */
	protected $slug;

	/**
	 * @var string
	 * @ORM\Column(name="couleur", type="string", length=32, nullable=false, unique=false)
	 */
	protected $couleur;

	/**
	 * @var string
	 * @ORM\Column(name="url", type="string", nullable=true, unique=false)
	 */
	protected $url;


	public function __construct() {
		$this->nom = null;
		$this->pageweb = null;
		$this->parent = null;
		$this->children = new ArrayCollection();
		$this->dateCreation = new DateTime();
		$this->dateMaj = null;
		$this->articles = new ArrayCollection();
		$this->couleur = "#FFFFFF";
		$this->url = null;
	}


	/**
	 * get id
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * set nom
	 * @param string $nom
	 * @return categorie
	 */
	public function setNom($nom) {
		$this->nom = $nom;
		return $this;
	}

	/**
	 * get nom
	 * @return string
	 */
	public function getNom() {
		return $this->nom;
	}

	/**
	 * set pageweb
	 * @param pageweb $pageweb
	 * @return categorie
	 */
	public function setPageweb(pageweb $pageweb) {
		$this->pageweb = $pageweb;
	}

	/**
	 * get pageweb
	 * @return pageweb
	 */
	public function getPageweb() {
		return $this->pageweb;
	}

	public function setParent(categorie $parent = null) {
		$this->parent = $parent;
	}

	public function getParent() {
		return $this->parent;
	}

	public function getChildren() {
		return $this->children;
	}

	/**
	 * Set descriptif
	 * @param string $descriptif
	 * @return categorie
	 */
	public function setDescriptif($descriptif = null) {
		$this->descriptif = $descriptif;
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
	 * Set dateCreation
	 * @param DateTime $dateCreation
	 * @return categorie
	 */
	public function setDateCreation(Datetime $dateCreation) {
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
		$this->setDateMaj(new Datetime());
	}

	/**
	 * Set dateMaj
	 * @param DateTime $dateMaj
	 * @return categorie
	 */
	public function setDateMaj(Datetime $dateMaj) {
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
	 * add article
	 * @param article $article
	 * @return categorie
	 */
	public function addArticle(article $article) {
		$this->articles->add($article);
		$article->addCategorie_reverse($this);
		return $this;
	}

	/**
	 * add article reverse
	 * @param article $article
	 * @return categorie
	 */
	public function addArticle_reverse(article $article) {
		$this->articles->add($article);
		return $this;
	}

	/**
	 * remove article
	 * @param article $article
	 * @return boolean
	 */
	public function removeArticle(article $article) {
		$article->removeCategorie_reverse($this);
		return $this->articles->removeElement($article);
	}

	/**
	 * remove article reverse
	 * @param article $article
	 * @return boolean
	 */
	public function removeArticle_reverse(article $article) {
		return $this->articles->removeElement($article);
	}

	/**
	 * get articles
	 * @return ArrayCollection
	 */
	public function getArticles() {
		return $this->articles;
	}



	/**
	 * Set statut
	 * @param integer $statut
	 * @return baseEntity
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
	 * Set slug
	 * @param integer $slug
	 * @return baseEntity
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
	 * Set couleur
	 * @param string $couleur
	 * @return version
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

	/**
	 * Set url
	 * @param string $url
	 * @return categorie
	 */
	public function setUrl($url = null) {
		$this->url = $url;
		return $this;
	}

	/**
	 * Get url
	 * @return string 
	 */
	public function getUrl() {
		return $this->url;
	}


}
