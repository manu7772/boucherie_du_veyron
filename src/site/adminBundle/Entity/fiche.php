<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use \DateTime;

/**
 * fiche
 *
 * @ORM\Entity
 * @ORM\Table(name="fiche")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\ficheRepository")
 */
class fiche {

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=100, nullable=false, unique=false)
	 * @Assert\NotBlank(message = "Vous devez nommer cet artible.")
	 * @Assert\Length(
	 *      min = "3",
	 *      max = "100",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;

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
	 * @var DateTime
	 * @ORM\Column(name="datePublication", type="datetime", nullable=false)
	 */
	protected $datePublication;

	/**
	 * @var DateTime
	 * @ORM\Column(name="dateExpiration", type="datetime", nullable=true)
	 */
	protected $dateExpiration;

	/**
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\statut")
	 * @ORM\JoinColumn(nullable=false, unique=false)
	 */
	protected $statut;

	/**
	 * @var string
	 * @ORM\Column(name="accroche", type="string", length=200, nullable=true, unique=false)
	 * @Assert\Length(
	 *      max = "200",
	 *      maxMessage = "L'accroche doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $accroche;

	/**
	 * @var string
	 * @ORM\Column(name="niveau", type="string", length=30, nullable=false, unique=false)
	 */
	protected $niveau;

	/**
	 * @var string
	 * @ORM\Column(name="duree", type="string", length=20, nullable=false, unique=false)
	 */
	protected $duree;

	/**
	 * @var array
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\media", cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $image;

	/**
	 * @Gedmo\Slug(fields={"nom"})
	 * @ORM\Column(length=128, unique=true)
	 */
	protected $slug;

	/**
	 * @var array
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\article", mappedBy="fiches")
	 * @ORM\JoinColumn(nullable=true, unique=false)
	 */
	protected $articles;

	/**
	 * @var array
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\tag", inversedBy="fiches")
	 * @ORM\JoinColumn(nullable=true, unique=false)
	 */
	protected $tags;

	protected $keywords;

	protected $listeNiveaux = array(
		"débutant" => "débutant",
		"intermédiaire" => "intermédiaire",
		"confirmé" => "confirmé",
		);

	protected $durees = array(
        30    =>  "30 minutes",
        60    =>  "1 heure",
        90    =>  "1 heure 30",
        120   =>  "2 heures",
        150   =>  "2 heures 30",
        180   =>  "3 heures",
        210   =>  "3 heures 30",
        240   =>  "4 heures",
        270   =>  "4 heures 30",
        300   =>  "5 heures"
        );

	public function __construct() {
		$this->dateCreation = new DateTime();
		$this->datePublication = new DateTime();
		$this->dateMaj = null;
		$this->dateExpiration = null;
		$this->articles = new ArrayCollection();
		reset($this->listeNiveaux);
		$this->setNiveau(current($this->listeNiveaux)); // Niveau par défaut
		$this->duree = 30;
		$this->tags = new ArrayCollection();
		$this->keywords = null;
	}

	public function __toString() {
		return $this->getNom();
	}

	/**
	 * get niveaux
	 * @return array 
	 */
	public function getListeNiveaux() {
		return $this->listeNiveaux;
	}

	/**
	 * get durees
	 * @return array 
	 */
	public function getDurees() {
		return $this->durees;
	}

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
	 * @return fiche
	 */
	public function setNom($nom) {
		$this->nom = $nom;
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
	 * Set descriptif
	 * @param string $descriptif
	 * @return fiche
	 */
	public function setDescriptif($descriptif) {
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
	 * @return fiche
	 */
	public function setDateCreation($dateCreation) {
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
	 * @return fiche
	 */
	public function setDateMaj($dateMaj) {
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
	 * Set datePublication
	 * @param DateTime $datePublication
	 * @return fiche
	 */
	public function setDatePublication($datePublication = null) {
		if(($datePublication < $this->dateCreation) || ($datePublication === null)) $datePublication = $this->dateCreation;
		$this->datePublication = $datePublication;
		return $this;
	}

	/**
	 * Get datePublication
	 * @return DateTime 
	 */
	public function getDatePublication() {
		return $this->datePublication;
	}

	/**
	 * Set dateExpiration
	 * @param DateTime $dateExpiration
	 * @return fiche
	 */
	public function setDateExpiration($dateExpiration = null) {
		if(($dateExpiration < $this->dateCreation) && ($dateExpiration !== null)) $dateExpiration = null;
		$this->dateExpiration = $dateExpiration;
		return $this;
	}

	/**
	 * Get dateExpiration
	 * @return DateTime 
	 */
	public function getDateExpiration() {
		return $this->dateExpiration;
	}

	/**
	 * Set statut
	 * @param statut $statut
	 * @return fiche
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
	 * Set accroche
	 * @param string $accroche
	 * @return fiche
	 */
	public function setAccroche($accroche) {
		$this->accroche = $accroche;
		return $this;
	}

	/**
	 * Get accroche
	 * @return string 
	 */
	public function getAccroche() {
		return $this->accroche;
	}

	/**
	 * Set niveau
	 * @param string $niveau
	 * @return fiche
	 */
	public function setNiveau($niveau = null) {
		$this->niveau = $niveau;
		return $this;
	}

	/**
	 * Get niveau
	 * @return string 
	 */
	public function getNiveau() {
		return $this->niveau;
	}

	/**
	 * Set duree
	 * @param string $duree
	 * @return fiche
	 */
	public function setDuree($duree = null) {
		$this->duree = $duree;
		return $this;
	}

	/**
	 * Get duree
	 * @return string 
	 */
	public function getDuree() {
		return $this->duree;
	}

	/**
	 * Set slug
	 * @param string $slug
	 * @return fiche
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
	 * Set image
	 * @param image $image
	 * @return fiche
	 */
	public function setImage(media $image = null) {
		$this->image = $image;
		return $this;
	}

	/**
	 * Get image
	 * @return image 
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * Get articles
	 * @return ArrayCollection 
	 */
	public function getArticles() {
		return $this->articles;
	}

	/**
	 * Add article
	 * @param article $article
	 * @return video
	 */
	public function addArticle(article $article, $doReverse = true) {
		if($doReverse == true) $article->addFiche($this, false);
		$this->articles->add($article);
		return $this;
	}

	/**
	 * Remove article
	 * @param article $article
	 * @return boolean
	 */
	public function removeArticle(article $article, $doReverse = true) {
		if($doReverse == true) $article->removeFiche($this, false);
		return $this->articles->removeElement($article);
	}

	/**
	 * Set keywords
	 * @ORM\PostLoad
	 * @param string $keywords
	 * @return fiche
	 */
	public function setKeywords($keywords = null) {
		$this->keywords = implode($this->getTags()->toArray(), ', ');
		return $this;
	}

	/**
	 * Get keywords
	 * @return string 
	 */
	public function getKeywords() {
		return $this->keywords;
	}

	/**
	 * Add tag
	 * @param tag $tag
	 * @return fiche
	 */
	public function addTag(tag $tag, $doReverse = true) {
		if($doReverse == true) $tag->addArticle($this, false);
		$this->tags->add($tag);
		return $this;
	}

	/**
	 * Remove tag
	 * @param tag $tag
	 * @return boolean
	 */
	public function removeTag(tag $tag, $doReverse = true) {
		if($doReverse == true) $tag->removeArticle($this, false);
		return $this->tags->removeElement($tag);
	}

	/**
	 * Get tags
	 * @return ArrayCollection 
	 */
	public function getTags() {
		return $this->tags;
	}

}