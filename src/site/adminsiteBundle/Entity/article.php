<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// JMS Serializer
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\MaxDepth;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;

use Labo\Bundle\AdminBundle\Entity\item;
use site\adminsiteBundle\Entity\marque;
use site\adminsiteBundle\Entity\tauxTva;
use site\adminsiteBundle\Entity\pdf;

use Labo\Bundle\AdminBundle\services\aeUnits;

/**
 * article
 *
 * @ExclusionPolicy("all")
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\articleRepository")
 * @ORM\Table(name="article", options={"comment":"articles du site"})
 * @ORM\HasLifecycleCallbacks
 */
class article extends item {

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @Expose
	 * @Groups({"complete", "ajaxlive", "facture"})
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(name="refFabricant", type="string", length=100, nullable=true, unique=false)
	 * @Assert\Length(
	 *      max = "100",
	 *      maxMessage = "La référence frabricant doit comporter au maximum {{ limit }} lettres."
	 * )
	 * @Expose
	 * @Groups({"complete", "facture"})
	 */
	protected $refFabricant;

	/**
	 * @var string
	 * @ORM\Column(name="accroche", type="string", length=60, nullable=true, unique=false)
	 * @Assert\Length(
	 *      max = "60",
	 *      maxMessage = "L'accroche doit comporter au maximum {{ limit }} lettres."
	 * )
	 * @Expose
	 * @Groups({"complete"})
	 */
	protected $accroche;

	/**
	 * @var boolean
	 * @ORM\Column(name="vendable", type="boolean", nullable=false, unique=false)
	 * @Expose
	 * @Groups({"complete"})
	 */
	protected $vendable;

	/**
	 * @var boolean
	 * @ORM\Column(name="surdevis", type="boolean", nullable=false, unique=false)
	 * @Expose
	 * @Groups({"complete"})
	 */
	protected $surdevis;

	/**
	 * @var boolean
	 * @ORM\Column(name="groupbasket", type="boolean", nullable=false, unique=false)
	 * @Expose
	 * @Groups({"complete", "ajaxlive", "facture"})
	 */
	protected $groupbasket;

	/**
	 * @var float
	 * @ORM\Column(name="prix", type="decimal", scale=2, nullable=true, unique=false)
	 * @Expose
	 * @Groups({"complete", "ajaxlive", "facture"})
	 */
	protected $prix;

	/**
	 * @var float
	 * @ORM\Column(name="prixHT", type="decimal", scale=2, nullable=true, unique=false)
	 * @Expose
	 * @Groups({"complete", "ajaxlive", "facture"})
	 */
	protected $prixHT;

	/**
	 * @var string
	 * @ORM\Column(name="unitprix", type="string", length=8, nullable=false, unique=false)
	 * @Expose
	 * @Groups({"complete", "ajaxlive", "facture"})
	 */
	protected $unitprix;

	/**
	 * @var string
	 * @ORM\Column(name="unit", type="string", length=8, nullable=false, unique=false)
	 * @Expose
	 * @Groups({"complete", "ajaxlive"})
	 */
	protected $unit;

	/**
	 * @var integer
	 * @ORM\Column(name="defaultquantity", type="integer", nullable=false, unique=false)
	 * @Expose
	 * @Groups({"complete", "ajaxlive"})
	 */
	protected $defaultquantity;

	/**
	 * @var integer
	 * @ORM\Column(name="maxquantity", type="integer", nullable=true, unique=false)
	 * @Expose
	 * @Groups({"complete", "ajaxlive"})
	 */
	protected $maxquantity;

	/**
	 * @var integer
	 * @ORM\Column(name="minquantity", type="integer", nullable=true, unique=false)
	 * @Expose
	 * @Groups({"complete", "ajaxlive"})
	 */
	protected $minquantity;

	/**
	 * @var integer
	 * @ORM\Column(name="increment", type="integer", nullable=false, unique=false)
	 * @Expose
	 * @Groups({"complete", "ajaxlive"})
	 */
	protected $increment;

	/**
	 * @var string
	 * @ORM\ManyToOne(targetEntity="site\adminsiteBundle\Entity\tauxTva", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 * @Expose
	 * @Groups({"complete", "facture"})
	 */
	protected $tauxTva;

	/**
	 * @ORM\ManyToOne(targetEntity="site\adminsiteBundle\Entity\marque", inversedBy="articles", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 * @Expose
	 * @Groups({"complete", "facture"})
	 */
	protected $marque;

	/**
	 * @ORM\OneToOne(targetEntity="site\adminsiteBundle\Entity\pdf", inversedBy="article", orphanRemoval=true, cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $pdf;

	/**
	 * @Expose
	 * @Groups({"complete", "ajaxlive"})
	 * @Accessor(getter="isPanierable")
	 */
	protected $isPanierable;
	/**
	 * @Expose
	 * @Groups({"complete", "ajaxlive", "facture"})
	 * @Accessor(getter="getPrixUnit")
	 */
	protected $prixUnit;
	/**
	 * @Expose
	 * @Groups({"complete", "facture"})
	 * @Accessor(getter="getPrixUnitText")
	 */
	protected $prixUnitText;
	/**
	 * @Expose
	 * @Groups({"complete", "facture"})
	 * @Accessor(getter="getUnitprixText")
	 */
	protected $unitprixText;
	/**
	 * @Expose
	 * @Groups({"complete", "ajaxlive", "facture"})
	 * @Accessor(getter="getTextTva")
	 */
	protected $textTva;
	/**
	 * @Expose
	 * @Groups({"complete", "ajaxlive", "facture"})
	 * @Accessor(getter="getFloatTva")
	 */
	protected $floatTva;
	/**
	 * @Expose
	 * @Groups({"complete", "facture"})
	 * @Accessor(getter="getTaux_tva")
	 */
	protected $taux_tva;
	/**
	 * @Expose
	 * @Groups({"complete", "ajaxlive", "facture"})
	 * @Accessor(getter="getListOfUnits")
	 */
	protected $aeUnits;
	/**
	 * @Expose
	 * @Groups({"complete", "ajaxlive", "facture"})
	 * @Accessor(getter="getDevise")
	 */
	protected $devise;
	/**
	 * @Expose
	 * @Groups({"complete"})
	 * @Accessor(getter="getDevises")
	 */
	protected $devises;

	public function __construct() {
		parent::__construct();
		$this->load();
		$this->vendable = true;
		$this->surdevis = false;
		$this->groupbasket = false;
		$this->refFabricant = null;
		$this->accroche = null;
		$this->prix = 0;
		$this->prixHT = 0;
		$this->tauxTva = null;
		$this->marque = null;
		$this->pdf = null;
		$this->increment = 1;
		$this->defaultquantity = 1;
		$this->maxquantity = null;
		$this->minquantity = 1;
		$this->unitprix = $this->aeUnits->getDefaultUnit();
		$this->unit = null;
		$this->devise = null;
		$this->devises = null;
		// $this->unit = $this->aeUnits->getDefaultUnit();
	}

	/**
	 * @ORM\PostLoad
	 */
	public function load() {
		$this->aeUnits = new aeUnits();
	}

	public function getListOfUnits() {
		return $this->aeUnits->getListOfUnits();
	}

	public function getChoiceListOfUnits() {
		return $this->aeUnits->getChoiceListOfUnits();
	}

	/**
	 * Un élément par défaut dans la table est-il optionnel ?
	 * @return boolean
	 */
	public function isDefaultNullable() {
		return true;
	}

	/**
	 * Peut'on attribuer plusieurs éléments par défaut ?
	 * true 		= illimité
	 * integer 		= nombre max. d'éléments par défaut
	 * false, 0, 1 	= un seul élément
	 * @return boolean
	 */
	public function isDefaultMultiple() {
		return true;
	}

	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 */
	public function check() {
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
		if($this->unit == null || $this->unit == $this->unitprix) {
			$this->unit = $this->getUnitprix();
			$this->setGroupbasket(true);
		}
		// parent
		parent::check();
	}

	// /**
	//  * Renvoie l'image principale
	//  * @return image
	//  */
	// public function getMainMedia() {
	// 	return $this->getImage();
	// }

	/**
	 * is putable in panier
	 * @return boolean
	 */
	public function isPanierable() {
		return in_array($this->getStatut()->getNiveau(), array('IS_AUTHENTICATED_ANONYMOUSLY', 'ROLE_USER'))
			&& $this->isVendable()
			&& !$this->isSurdevis()
			&& ((float)$this->getPrix() > 0)
			&& ((float)$this->getPrixHT() > 0)
			;
	}

	/**
	 * Set vendable
	 * @param boolean $vendable
	 * @return article
	 */
	public function setVendable($vendable) {
		$this->vendable = $vendable;
		return $this;
	}

	/**
	 * Get vendable
	 * @return boolean 
	 */
	public function getVendable() {
		return $this->vendable;
	}

	/**
	 * Is vendable
	 * @return boolean 
	 */
	public function isVendable() {
		return $this->vendable;
	}

	/**
	 * Set surdevis
	 * @param boolean $surdevis
	 * @return article
	 */
	public function setSurdevis($surdevis) {
		$this->surdevis = $surdevis;
		return $this;
	}

	/**
	 * Get surdevis
	 * @return boolean 
	 */
	public function getSurdevis() {
		return $this->surdevis;
	}

	/**
	 * Is surdevis
	 * @return boolean 
	 */
	public function isSurdevis() {
		return $this->surdevis;
	}

	/**
	 * Set groupbasket
	 * @param boolean $groupbasket
	 * @return article
	 */
	public function setGroupbasket($groupbasket) {
		$this->groupbasket = $groupbasket;
		return $this;
	}

	/**
	 * Get groupbasket
	 * @return boolean 
	 */
	public function getGroupbasket() {
		return $this->groupbasket;
	}

	/**
	 * Is groupbasket
	 * @return boolean 
	 */
	public function isGroupbasket() {
		return $this->groupbasket;
	}

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
		return $this->prix * $this->getDevise()['ratio'];
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
		return $this->prixHT * $this->getDevise()['ratio'];
	}

	/**
	 * Set unitprix
	 * @param string $unitprix
	 * @return article
	 */
	public function setUnitprix($unitprix) {
		$this->unitprix = $this->aeUnits->unitExists($unitprix) ? $unitprix : $this->unitprix;
		return $this;
	}

	/**
	 * Get unitprix
	 * @return string 
	 */
	public function getUnitprix() {
		return $this->unitprix;
	}

	/**
	 * Get unitprix
	 * @return string 
	 */
	public function getPrixUnit() {
		return number_format($this->getPrix(), 2, ',', '');
	}

	/**
	 * Get text of prix and unit (please, use raw filter in twig)
	 * @return string (of html code)
	 */
	public function getPrixUnitText() {
		$devise = $this->getDevise()['symb'];
		return $this->getPrixUnit().'<small><sup>'.$devise.'TTC</sup><span>/'.$this->getUnitprix().'</span></small>';
	}

	/**
	 * Get unitprix text
	 * @return string 
	 */
	public function getUnitprixText() {
		return $this->aeUnits->getUnitName($this->getUnitprix());
	}

	/**
	 * Set devises info
	 * @param array $devises
	 * @return article
	 */
	public function setDevises($devises) {
		$this->devises = $devises;
		return $this;
	}

	/**
	 * Get devises info
	 * @return array 
	 */
	public function getDevises() {
		return $this->devises;
	}

	/**
	 * Get devise info
	 * @param string $lang = null
	 * @return array
	 */
	public function getDevise($lang = null) {
		if($lang === null) $lang = $this->getLocale();
		return $this->devises[$lang];
	}

	/**
	 * @Assert\IsTrue(message = "Les unité au prix et unité doivent être compatibles.")
	 */
	public function isUnitValid() {
		return $this->aeUnits->isSameGroup($this->getUnitprix(), $this->getUnit());
	}

	/**
	 * Set unit
	 * @param string $unit
	 * @return article
	 */
	public function setUnit($unit = null) {
		$this->unit = $this->aeUnits->unitExists($unit) || ($unit == null) ? $unit : $this->unit;
		return $this;
	}

	/**
	 * Get unit
	 * @return string 
	 */
	public function getUnit() {
		return $this->unit;
	}

	/**
	 * Get unit text
	 * @return string 
	 */
	public function getUnitText() {
		return $this->aeUnits->getUnitName($this->getUnit());
	}

	/**
	 * Set defaultquantity
	 * @param integer $defaultquantity
	 * @return article
	 */
	public function setDefaultquantity($defaultquantity) {
		$this->defaultquantity = (integer)$defaultquantity;
		if($this->defaultquantity < 1) $this->defaultquantity = 1;
		return $this;
	}

	/**
	 * Get defaultquantity
	 * @return integer 
	 */
	public function getDefaultquantity() {
		return $this->defaultquantity;
	}

	/**
	 * Set maxquantity
	 * @param integer $maxquantity
	 * @return article
	 */
	public function setMaxquantity($maxquantity) {
		$this->maxquantity = (integer)$maxquantity;
		if($this->maxquantity <= $this->minquantity) $this->maxquantity = $this->minquantity;
		return $this;
	}

	/**
	 * Get maxquantity
	 * @return integer 
	 */
	public function getMaxquantity() {
		return $this->maxquantity;
	}

	/**
	 * Set minquantity
	 * @param integer $minquantity
	 * @return article
	 */
	public function setMinquantity($minquantity) {
		$this->minquantity = (integer)$minquantity;
		if($this->minquantity < 1) $this->minquantity = 1;
		if($this->maxquantity <= $this->minquantity) $this->maxquantity = $this->minquantity;
		return $this;
	}

	/**
	 * Get minquantity
	 * @return integer 
	 */
	public function getMinquantity() {
		return $this->minquantity;
	}

	/**
	 * Set increment
	 * @param integer $increment
	 * @return article
	 */
	public function setIncrement($increment) {
		$this->increment = (integer)$increment;
		if($this->increment < 1) $this->increment = 1;
		return $this;
	}

	/**
	 * Get increment
	 * @return integer 
	 */
	public function getIncrement() {
		return $this->increment;
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
		// return number_format($this->prixHT * ($this->tauxTva->getTaux() / 100), 2, ",", "").'€';
		return preg_replace('#,?(0)*$#', '', number_format($this->tauxTva->getTaux(), 2, ",", "")).'%';
	}

	/**
	 * Get TVA float number
	 * @return float 
	 */
	public function getFloatTva() {
		return $this->prixHT * ($this->tauxTva->getTaux() / 100);
	}

	/**
	 * Get TVA taux
	 * @return float 
	 */
	public function getTaux_tva() {
		return $this->tauxTva->getTaux();
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

}