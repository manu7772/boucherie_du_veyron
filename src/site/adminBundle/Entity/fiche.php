<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\item;

use \DateTime;

/**
 * fiche
 *
 * @ORM\Entity
 * @ORM\Table(name="fiche")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\ficheRepository")
 * @ExclusionPolicy("all")
 */
class fiche extends item {

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=100, nullable=false, unique=false)
	 * @Assert\NotBlank(message = "Vous devez nommer cette fiche")
	 * @Assert\Length(
	 *      min = "3",
	 *      max = "100",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;

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
	 * @var array - PROPRIÉTAIRE
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\article", inversedBy="fiches")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $articles;

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
		parent::__construct();
		$this->datePublication = new DateTime();
		$this->dateExpiration = null;
		$this->articles = new ArrayCollection();
		reset($this->listeNiveaux);
		$this->setNiveau(current($this->listeNiveaux)); // Niveau par défaut
		$this->duree = 30;
		$this->tags = new ArrayCollection();
	}

    // public function getClassName(){
    //     return parent::CLASS_FICHE;
    // }

	// /**
	//  * Renvoie l'image principale
	//  * @return image
	//  */
	// public function getMainMedia() {
	// 	return $this->getImage();
	// }

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
	 * Set datePublication
	 * @param DateTime $datePublication
	 * @return fiche
	 */
	public function setDatePublication($datePublication = null) {
		if(($datePublication < $this->created) || ($datePublication === null)) $datePublication = $this->created;
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
		if(($dateExpiration < $this->created) && ($dateExpiration !== null)) $dateExpiration = null;
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
	public function addArticle(article $article) {
		$article->addFiche($this);
		$this->articles->add($article);
		return $this;
	}

	/**
	 * Remove article
	 * @param article $article
	 * @return boolean
	 */
	public function removeArticle(article $article) {
		$article->removeFiche($this);
		return $this->articles->removeElement($article);
	}

}