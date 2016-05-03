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

use site\adminBundle\Entity\reseau;
use site\adminBundle\Entity\marque;
use site\adminBundle\Entity\tauxTva;

use \DateTime;

/**
 * article
 *
 * @ORM\Entity
 * @ORM\Table(name="article", options={"comment":"articles du site"})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\articleRepository")
 * @ExclusionPolicy("all")
 */
class article extends item {

	/**
	 * @var string
	 * @ORM\Column(name="refFabricant", type="string", length=100, nullable=true, unique=false)
	 * @Assert\Length(
	 *      max = "100",
	 *      maxMessage = "La référence frabricant doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $refFabricant;

	/**
	 * @var string
	 * @ORM\Column(name="accroche", type="string", length=60, nullable=true, unique=false)
	 * @Assert\Length(
	 *      max = "60",
	 *      maxMessage = "L'accroche doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $accroche;

	/**
	 * @var float
	 * @ORM\Column(name="prix", type="decimal", scale=2, nullable=true, unique=false)
	 */
	protected $prix;

	/**
	 * @var float
	 * @ORM\Column(name="prixHT", type="decimal", scale=2, nullable=true, unique=false)
	 */
	protected $prixHT;

	/**
	 * @var string
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\tauxTva")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $tauxTva;

	/**
	 * - PROPRIÉTAIRE
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\marque", inversedBy="articles")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $marque;

	/**
	 * - PROPRIÉTAIRE
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\reseau", inversedBy="articles")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $reseaus;

	/**
	 * - PROPRIÉTAIRE
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\pdf", cascade={"all"}, inversedBy="article", orphanRemoval=true, cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $pdf;

	/**
	 * - INVERSE
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\fiche", mappedBy="articles")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $fiches;

	/**
	 * @var array
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\article", mappedBy="articlesLies")
	 */
	protected $articlesParents;

	/**
	 * @var array
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\article", inversedBy="articlesParents")
	 * @ORM\JoinTable(name="articlesLinks",
	 *     joinColumns={@ORM\JoinColumn(name="articlesLies_id", referencedColumnName="id")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="articlesParents_id", referencedColumnName="id")}
	 * )
	 */
	protected $articlesLies;


	public function __construct() {
		parent::__construct();
		$this->refFabricant = null;
		$this->accroche = null;
		$this->prix = 0;
		$this->prixHT = 0;
		$this->tauxTva = null;
		$this->marque = null;
		$this->reseaus = new ArrayCollection();
		$this->fiches = new ArrayCollection();
		$this->articlesParents = new ArrayCollection();
		$this->articlesLies = new ArrayCollection();
	}

    // public function getClassName(){
    //     return parent::CLASS_ARTICLE;
    // }

	public function memOldValues($addedfields = null) {
		$fields = array('marque', 'reseaus', 'pdf', 'fiches', 'articlesParents', 'articlesLies');
		if(count($addedfields) > 0 && is_array($addedfields)) $fields = array_unique(array_merge($fields, $addedfields));
		parent::memOldValues($fields);
		return $this;
	}

	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 */
	public function verifPrix() {
		// si les deux prix sont null ou 0
		if(($this->prixHT == null || $this->prixHT == 0) && ($this->prix == null || $this->prix == 0)) {
			$this->prix = $this->prixHT = 0;
		}
		if($this->prix == null || $this->prix == 0) {
			// si le prix TTC est null ou 0
			$this->prix = $this->prixHT * (1 + ($this->tauxTva->getTaux() / 100));
		} else {
			// enfin, calcul pour priorité au prix TTC
			$this->prixHT = $this->prix / (1 + ($this->tauxTva->getTaux() / 100));
		}
	}

	// /**
	//  * Renvoie l'image principale
	//  * @return image
	//  */
	// public function getMainMedia() {
	// 	return $this->getImage();
	// }

	/**
	 * Set refFabricant
	 * @param string $refFabricant
	 * @return article
	 */
	public function setRefFabricant($refFabricant) {
		$this->refFabricant = $refFabricant;
		return $this;
	}

	/**
	 * Get refFabricant
	 * @return string 
	 */
	public function getRefFabricant() {
		return $this->refFabricant;
	}

	/**
	 * Set accroche
	 * @param string $accroche
	 * @return article
	 */
	public function setAccroche($accroche = null) {
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
	 * Set prix TTC
	 * @param float $prix
	 * @return article
	 */
	public function setPrix($prix) {
		$this->prix = $prix;
		return $this;
	}

	/**
	 * Get prix
	 * @return float 
	 */
	public function getPrix() {
		return $this->prix;
	}

	/**
	 * Set prixHT
	 * @param float $prixHT
	 * @return article
	 */
	public function setPrixHT($prixHT = null) {
		$this->prixHT = $prixHT;
		return $this;
	}

	/**
	 * Get prixHT
	 * @return float 
	 */
	public function getPrixHT() {
		return $this->prixHT;
	}

	/**
	 * Set tauxTva
	 * @param tauxTva $tauxTva
	 * @return article
	 */
	public function setTauxTva(tauxTva $tauxTva) {
		$this->tauxTva = $tauxTva;
		return $this;
	}

	/**
	 * Get tauxTva
	 * @return tauxTva 
	 */
	public function getTauxTva() {
		return $this->tauxTva;
	}

	/**
	 * Get TVA avec %
	 * @return string 
	 */
	public function getTextTva() {
		return number_format($this->prixHT * ($this->tauxTva->getTaux() / 100), 2, " ").'%';
	}

	/**
	 * Get TVA float number
	 * @return float 
	 */
	public function getFloatTva() {
		return $this->prixHT * ($this->tauxTva->getTaux() / 100);
	}

	/**
	 * Set marque - PROPRIÉTAIRE
	 * @param marque $marque
	 * @return article
	 */
	public function setMarque(marque $marque = null) {
		// if($marque == null) $marque->removeArticle($this);
		// else $marque->addArticle($this);
		$this->marque = $marque;
		return $this;
	}

	/**
	 * Get marque - PROPRIÉTAIRE
	 * @return marque 
	 */
	public function getMarque() {
		return $this->marque;
	}

	/**
	 * Set pdf - PROPRIÉTAIRE
	 * @param pdf $pdf
	 * @return article
	 */
	public function setpdf(pdf $pdf = null) {
		// $pdf->setArticle($this);
		$this->pdf = $pdf;
		return $this;
	}

	/**
	 * Get pdf - PROPRIÉTAIRE
	 * @return pdf 
	 */
	public function getpdf() {
		return $this->pdf;
	}

	/**
	 * Add reseau
	 * @param reseau $reseau
	 * @return article
	 */
	public function addReseau(reseau $reseau) {
		// $reseau->addArticle($this);
		$this->reseaus->add($reseau);
		return $this;
	}

	/**
	 * Remove reseau
	 * @param reseau $reseau
	 * @return boolean
	 */
	public function removeReseau(reseau $reseau) {
		// $reseau->removeArticle($this);
		return $this->reseaus->removeElement($reseau);
	}

	/**
	 * Get reseaus
	 * @return ArrayCollection 
	 */
	public function getReseaus() {
		return $this->reseaus;
	}

	/**
	 * Get fiches - INVERSE
	 * @return ArrayCollection 
	 */
	public function getFiches() {
		return $this->fiches;
	}

	/**
	 * Add fiche - INVERSE
	 * @param fiche $fiche
	 * @return article
	 */
	public function addFiche(fiche $fiche) {
		if(!$this->fiches->contains($fiche)) $this->fiches->add($fiche);
		return $this;
	}

	/**
	 * Remove fiche - INVERSE
	 * @param fiche $fiche
	 */
	public function removeFiche(fiche $fiche) {
		return $this->fiches->removeElement($fiche);
	}

	/**
	 * Add articlesParents
	 * @param article $articlesParents
	 * @return article
	 */
	public function addArticlesParent(article $articlesParents) {
		$this->articlesParents->add($articlesParents);
		return $this;
	}

	/**
	 * Remove articlesParents
	 * @param article $articlesParents
	 */
	public function removeArticlesParent(article $articlesParents) {
		$this->articlesParents->removeElement($articlesParents);
	}

	/**
	 * Get articlesParents
	 * @return ArrayCollection 
	 */
	public function getArticlesParents() {
		return $this->articlesParents;
	}

	/**
	 * Add articlesLies
	 * @param article $articlesLies
	 * @return article
	 */
	public function addArticlesLie(article $articlesLies) {
		if($articlesLies != $this) $this->articlesLies->add($articlesLies);
		// $articlesLies->addArticlesParent($this);
		return $this;
	}

	/**
	 * Remove articlesLies
	 * @param article $articlesLies
	 * @return boolean
	 */
	public function removeArticlesLie(article $articlesLies) {
		return $this->articlesLies->removeElement($articlesLies);
		// $articlesLies->removeArticlesParent($this);
	}

	/**
	 * Get articlesLies
	 * @return ArrayCollection 
	 */
	public function getArticlesLies() {
		return $this->articlesLies;
	}

}