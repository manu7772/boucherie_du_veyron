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
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\pdf", inversedBy="article", orphanRemoval=true, cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $pdf;

	/**
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\fiche", mappedBy="articles")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $fiches;

	// NESTED VIRTUAL GROUPS
	// les noms doivent commencer par "$group_" et finir par "Parents" (pour les parents) ou "Childs" (pour les enfants)
	// et la partie variable doit comporter au moins 3 lettres
	// reconnaissance auto par : "#^(add|remove|get)(Group_).{3,}(Parent|Child)(s)?$#" (self::VIRTUALGROUPS_PARENTS_PATTERN et self::VIRTUALGROUPS_CHILDS_PATTERN)
	protected $group_articlesParents;
	protected $group_articlesChilds;
	protected $group_articles_reseausParents;
	protected $group_articles_reseausChilds;

	public function __construct() {
		parent::__construct();
		$this->refFabricant = null;
		$this->accroche = null;
		$this->prix = 0;
		$this->prixHT = 0;
		$this->tauxTva = null;
		$this->marque = null;
		$this->fiches = new ArrayCollection();
	}

	public function getNestedAttributesParameters() {
		$new = array(
			'articles' => array(				// groupe articles => group_articlesParents / group_imagesChilds
				'data-limit' => 10,				// nombre max. d'enfants / 0 = infini
				'class' => array('article'),	// classes acceptées (array) / null = toutes les classes de nested
				'required' => false,
				),
			'articles_reseaus' => array(
				'data-limit' => 0,
				'class' => array('reseau'),
				'required' => false,
				),
			);
		return array_merge(parent::getNestedAttributesParameters(), $new);
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
	 * Set fiches
	 * @param arrayCollection $fiches
	 * @return subentity
	 */
	public function setFiches(ArrayCollection $fiches) {
		// incorporation avec "add" et "remove" au cas où il y aurait des opérations (inverse notamment)
		foreach ($this->getFiches() as $fiche) if(!$fiches->contains($fiche)) $this->removeFiche($fiche); // remove
		foreach ($fiches as $fiche) $this->addFiche($fiche); // add
		return $this;
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
	 * Get fiches
	 * @return ArrayCollection 
	 */
	public function getFiches() {
		return $this->fiches;
	}


}



