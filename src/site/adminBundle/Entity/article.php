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
use \Exception;

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
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\pdf", inversedBy="article", orphanRemoval=true, cascade={"persist", "remove"})
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
	 * @ORM\OneToMany(targetEntity="site\adminBundle\Entity\articleposition", orphanRemoval=true, mappedBy="child", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true, unique=false)
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	protected $articlepositionParents;

	/**
	 * @var array
	 * @ORM\OneToMany(targetEntity="site\adminBundle\Entity\articleposition", orphanRemoval=true, mappedBy="parent", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true, unique=false)
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	protected $articlepositionChilds;

	// NESTED VIRTUAL DATA
	protected $articleParents;
	protected $articleChilds;
	protected $articleParentInfo;
	protected $articleChildInfo;

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
		$this->articlepositionParents = new ArrayCollection();
		$this->articlepositionChilds = new ArrayCollection();
		$this->articleParents = new ArrayCollection();
		$this->articleChilds = new ArrayCollection();
		$this->articleParentInfo = array();
		$this->articleChildInfo = array();
	}

	public function memOldValues($addedfields = null) {
		$fields = array('marque', 'reseaus', 'pdf', 'fiches', 'articlepositionParents', 'articlepositionChilds');
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
	 * init nested values
	 */
	public function initNesteds() {
		$this->articleParents = new ArrayCollection();
		$this->articleChilds = new ArrayCollection();
		foreach($this->articlepositionParents as $link) {
			if($link->getChild() !== $this) throw new Exception("Link parent error !!", 1);
			$this->articleParents->add($link->getParent());
			$this->articleParentInfo['position'][$link->getParent()->getId()] = $link->getPosition();
			$this->articleParentInfo['parentLink'][$link->getParent()->getId()] = $link;
		}
		foreach($this->articlepositionChilds as $link) {
			if($link->getParent() !== $this) throw new Exception("Link child error !!", 1);
			$this->articleChilds->add($link->getChild());
			$this->articleChildInfo['position'][$link->getChild()->getId()] = $link->getPosition();
			$this->articleChildInfo['childLink'][$link->getChild()->getId()] = $link;
		}
	}

	public function getArticleParentInfo() { $this->initNesteds(); return isset($this->articleParentInfo['position']) ? $this->articleParentInfo['position'] : null; }
	public function getArticleChildrenInfo() { $this->initNesteds(); return isset($this->articleChildInfo['position']) ? $this->articleChildInfo['position'] : null; }

	/**
	 * Get position
	 * @param article $parent
	 * @return integer 
	 */
	public function getArticlePosition(article $parent) {
		$this->initNesteds();
		return isset($this->articleParentInfo['position'][$parent->getId()]) ? $this->articleParentInfo['position'][$parent->getId()] : null ;
	}

	/**
	 * set first in parent position
	 * @param article $parent
	 * @param integer $position
	 * @return integer 
	 */
	public function setArticlePosition_position(article $parent, $position) {
		if(isset($this->articleParentInfo['parentLink'][$parent->getId()])) {
			$this->articleParentInfo['parentLink'][$parent->getId()]->setPosition((integer) $position);
			$parent->initNesteds();
			$this->initNesteds();
		}
	}

	/**
	 * set first in parent position
	 * @param article $parent
	 * @return integer 
	 */
	public function setArticlePosition_first(article $parent) {
		$this->setArticlePosition_position($parent, 0);
	}

	/**
	 * set last in parent position
	 * @param article $parent
	 * @return integer 
	 */
	public function setArticlePosition_last(article $parent) {
		$this->setArticlePosition_position($parent, -1);
	}


	/**
	 * Add articlepositionParents
	 * @param articleposition $articleposition
	 * @return article
	 */
	public function addArticlepositionParent(articleposition $articleposition) {
		if(!$this->articlepositionParents->contains($articleposition)) {
			$this->articlepositionParents->add($articleposition);
		}
		$this->initNesteds();
		return $this;
	}

	/**
	 * Remove articlepositionParents
	 * @param articleposition $articleposition
	 * @return boolean
	 */
	public function removeArticlepositionParent(articleposition $articleposition) {
		$r = false;
		if($this->articlepositionParents->contains($articleposition)) {
			$r = $this->articlepositionParents->removeElement($articleposition);
		}
		$this->initNesteds();
		return $r;
	}

	/**
	 * Get articlepositionParents
	 * @return ArrayCollection 
	 */
	public function getArticlepositionParents() {
		return $this->articlepositionParents;
	}


	/**
	 * Add articlepositionChild
	 * @param articleposition $articleposition
	 * @return article
	 */
	public function addArticlepositionChild(articleposition $articleposition) {
		if(!$this->articlepositionChilds->contains($articleposition)) {
			$this->articlepositionChilds->add($articleposition);
		}
		$this->initNesteds();
		return $this;
	}

	/**
	 * Remove articlepositionChilds
	 * @param articleposition $articleposition
	 * @return boolean
	 */
	public function removeArticlepositionChild(articleposition $articleposition) {
		$r = false;
		if($this->articlepositionChilds->contains($articleposition)) {
			$r = $this->articlepositionChilds->removeElement($articleposition);
		}
		$this->initNesteds();
		return $r;
	}

	/**
	 * Get articlepositionChilds
	 * @return ArrayCollection 
	 */
	public function getArticlepositionChilds() {
		return $this->articlepositionChilds;
	}



	/**
	 * Add articleParent
	 * @param article $articleParent
	 * @return article
	 */
	public function addArticleParent(article $articleParent) {
		$this->initNesteds();
		if(!$this->articleParents->contains($articleParent)) $this->articleParents->add($articleParent);
		return $this;
	}

	/**
	 * Remove articleParent
	 * @param article $articleParent
	 * @return boolean
	 */
	public function removeArticleParent(article $articleParent) {
		$this->initNesteds();
		if($this->articleParents->contains($articleParent)) return $this->articleParents->removeElement($articleParent);
		return false;
	}

	/**
	 * Get articleParents
	 * @return ArrayCollection 
	 */
	public function getArticleParents() {
		return $this->articleParents;
	}


	/**
	 * Add articlesChild
	 * @param article $articlesChild
	 * @return article
	 */
	public function addArticleChild(article $articlesChild) {
		$this->initNesteds();
		if(!$this->articleChilds->contains($articlesChild)) $this->articleChilds->add($articlesChild);
		return $this;
	}

	/**
	 * Remove articlesChild
	 * @param article $articlesChild
	 * @return boolean
	 */
	public function removeArticleChild(article $articlesChild) {
		$this->initNesteds();
		if($this->articleChilds->contains($articlesChild)) return $this->articleChilds->removeElement($articlesChild);
		return false;
	}

	/**
	 * Get articleChilds
	 * @return ArrayCollection 
	 */
	public function getArticleChilds() {
		return $this->articleChilds;
	}

}



