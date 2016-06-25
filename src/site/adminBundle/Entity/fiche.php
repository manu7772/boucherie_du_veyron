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
 * @ORM\Table(name="fiche", options={"comment":"fiches : modes d'emplois, recettes, notices, bricolage, etc."})
 * @ORM\HasLifecycleCallbacks
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
		1 => "niveaux.debutant",
		2 => "niveaux.intermediaire",
		3 => "niveaux.confirme",
		);

	protected $durees = array(
        30    =>  "30\"",
        60    =>  "1 h",
        90    =>  "1 h 30\"",
        120   =>  "2 h",
        150   =>  "2 h 30\"",
        180   =>  "3 h",
        210   =>  "3 h 30\"",
        240   =>  "4 h",
        270   =>  "4 h 30\"",
        300   =>  "5 h"
        );

	public function __construct() {
		parent::__construct();
		$this->datePublication = new DateTime();
		$this->dateExpiration = null;
		$this->articles = new ArrayCollection();
		$this->setNiveau(reset($this->listeNiveaux)); // Niveau par défaut
		$this->duree = 30;
	}

	// public function memOldValues($addedfields = null) {
	// 	$fields = array('articles');
	// 	if(count($addedfields) > 0 && is_array($addedfields)) $fields = array_unique(array_merge($fields, $addedfields));
	// 	parent::memOldValues($fields);
	// 	return $this;
	// }
 
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
	 * Get niveauText
	 * @return string 
	 */
	public function getNiveauText() {
		return $this->listeNiveaux[$this->niveau];
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
	 * Set articles
	 * @param arrayCollection $articles
	 * @return subentity
	 */
	public function setArticles(ArrayCollection $articles) {
		// $this->articles->clear();
		// incorporation avec "add" et "remove" au cas où il y aurait des opérations (inverse notamment)
		foreach ($this->getArticles() as $article) if(!$articles->contains($article)) $this->removeArticle($article); // remove
		foreach ($articles as $article) $this->addArticle($article); // add
		return $this;
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
		// $article->addFiche($this);
		if(!$this->articles->contains($article)) $this->articles->add($article);
		return $this;
	}

	/**
	 * Remove article
	 * @param article $article
	 * @return boolean
	 */
	public function removeArticle(article $article) {
		// $article->removeFiche($this);
		return $this->articles->removeElement($article);
	}

}