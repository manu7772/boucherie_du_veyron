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

use site\adminBundle\Entity\articleposition;
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
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\marque", inversedBy="articles")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $marque;

	/**
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\reseau", inversedBy="articles")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $reseaus;

	/**
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\pdf", cascade={"all"}, inversedBy="article", orphanRemoval=true, cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $pdf;

	/**
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\fiche", mappedBy="articles")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $fiches;

	/**
	 * @var array
	 * @ORM\OneToMany(targetEntity="site\adminBundle\Entity\articleposition", orphanRemoval=true, mappedBy="parent", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true, unique=false)
	 */
	protected $articlesParents;

	/**
	 * @var array
	 * @ORM\OneToMany(targetEntity="site\adminBundle\Entity\articleposition", orphanRemoval=true, mappedBy="child", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true, unique=false)
	 */
	protected $articlesChilds;

	// NESTED VIRTUAL DATA
	protected $virtualParents;
	protected $virtualChilds;
	protected $ArticleLinkInfo;

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
		$this->articlesChilds = new ArrayCollection();
		$this->virtualParents = new ArrayCollection();
		$this->virtualChilds = new ArrayCollection();
		$this->ArticleLinkInfo = array();
	}

	public function memOldValues($addedfields = null) {
		$fields = array('marque', 'reseaus', 'pdf', 'fiches', 'articlesParents', 'articlesChilds');
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
	 * Set marque
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
	 * Get marque
	 * @return marque 
	 */
	public function getMarque() {
		return $this->marque;
	}

	/**
	 * Set pdf
	 * @param pdf $pdf
	 * @return article
	 */
	public function setpdf(pdf $pdf = null) {
		// $pdf->setArticle($this);
		$this->pdf = $pdf;
		return $this;
	}

	/**
	 * Get pdf
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
	 * Get fiches
	 * @return ArrayCollection 
	 */
	public function getFiches() {
		return $this->fiches;
	}

	/**
	 * Add fiche
	 * @param fiche $fiche
	 * @return article
	 */
	public function addFiche(fiche $fiche) {
		if(!$this->fiches->contains($fiche)) $this->fiches->add($fiche);
		return $this;
	}

	/**
	 * Remove fiche
	 * @param fiche $fiche
	 */
	public function removeFiche(fiche $fiche) {
		return $this->fiches->removeElement($fiche);
	}



	/**
	 * @ORM\PostLoad
	 */
	public function initNesteds() {
		$this->virtualParents = new ArrayCollection();
		$this->articlesChilds = new ArrayCollection();
		foreach($this->articlesParents as $link) {
			$this->virtualParents->add($link->getParent());
			$this->ArticleLinkInfo[$link->getParent()->getId()] = array();
			$this->ArticleLinkInfo[$link->getParent()->getId()]['position'] = $link->getPosition();
			$this->ArticleLinkInfo[$link->getParent()->getId()]['parentLink'] = $link;
		}
		foreach($this->articlesChilds as $link) {
			$this->virtualChilds->add($link->getChild());
			$this->ArticleLinkInfo[$link->getChild()->getId()] = array();
			$this->ArticleLinkInfo[$link->getChild()->getId()]['childLink'] = $link;
		}
	}

	/**
	 * Get position
	 * @param article $parent
	 * @return integer 
	 */
	public function getArticlePosition(article $parent) {
		return isset($this->ArticleLinkInfo[$parent->getId()]['position']) ? $this->ArticleLinkInfo[$parent->getId()]['position'] : null ;
	}

	/**
	 * Add articlesParent
	 * @param article $articlesParent
	 * @return article
	 */
	public function addArticlesParent(article $articlesParent) {
		// if(!$this->virtualParents->contains($articlesParent)) {
			// $this->virtualParents->add($articlesParent);
			// $articleposition = new articleposition();
			// $articleposition->addChild($this)
			// 	->addParent($articlesParent);
			// $articlesParent->addArticlepositionChild($this);
			// $this->addArticlepositionParent($articleposition);
		// }
		return $this;
	}

	/**
	 * Remove articlesParent
	 * @param article $articlesParent
	 * @return boolean
	 */
	public function removeArticlesParent(article $articlesParent) {
		if($this->virtualParents->contains($articlesParent) && isset($this->ArticleLinkInfo[$articlesParent->getId()]['parentLink'])) {
			$articleposition = $this->ArticleLinkInfo[$articlesParent->getId()]['parentLink'];
			$this->virtualParents->removeElement($articlesParent);
			return $this->articlesParents->removeElement($articleposition);
		}
		return false;
	}

	/**
	 * Add articleposition in parent
	 * @param articleposition $articleposition
	 * @return article
	 */
	public function addArticlepositionParent(articleposition $articleposition) {
		if(!$this->articlesParents->contains($articleposition)) $this->articlesParents->add($articleposition);
		return $this;
	}

	/**
	 * Remove articleposition in parent
	 * @param articleposition $articleposition
	 * @return article
	 */
	public function removeArticlepositionParent(articleposition $articleposition) {
		$this->articlesParents->removeElement($articleposition);
		return $this;
	}

	/**
	 * Add articleposition in child
	 * @param articleposition $articleposition
	 * @return article
	 */
	public function addArticlepositionChild(articleposition $articleposition) {
		if(!$this->articlesChilds->contains($articleposition)) $this->articlesChilds->add($articleposition);
		return $this;
	}

	/**
	 * Remove articleposition in child
	 * @param articleposition $articleposition
	 * @return article
	 */
	public function removeArticlepositionChild(articleposition $articleposition) {
		$this->articlesChilds->removeElement($articleposition);
		return $this;
	}

	/**
	 * Get articlesParents
	 * @return ArrayCollection 
	 */
	public function getArticlesParents() {
		return $this->virtualParents;
	}

	/**
	 * Add articlesChild
	 * @param article $articlesChild
	 * @return article
	 */
	public function addArticlesChild(article $articlesChild) {
		$this->initNesteds();
		if(!$this->virtualChilds->contains($articlesChild)) {
			$this->virtualChilds->add($articlesChild);
			$articleposition = new articleposition();
			$articleposition->addChild($articlesChild)
				->addParent($this);
			$articlesChild->addArticlepositionParent($articleposition);
			$this->addArticlepositionChild($articleposition);
		}
		return $this;
	}

	/**
	 * Remove articlesChild
	 * @param article $articlesChild
	 * @return boolean
	 */
	public function removeArticlesChild(article $articlesChild) {
		if($this->virtualChilds->contains($articlesChild) && isset($this->ArticleLinkInfo[$this->getId()]['childLink'])) {
			$articleposition = $this->ArticleLinkInfo[$this->getId()]['childLink'];
			$this->virtualChilds->removeElement($articlesChild);
			$articlesChild->removeArticlesParent($this);
			return $this->articlesParents->removeElement($articleposition);
		}
		return false;
	}

	/**
	 * Get articlesChilds
	 * @return ArrayCollection 
	 */
	public function getArticlesChilds() {
		return $this->virtualChilds;
	}

}



