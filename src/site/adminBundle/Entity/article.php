<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\statut;
use site\adminBundle\Entity\media;
use site\adminBundle\Entity\marque;
use site\adminBundle\Entity\tauxTva;
use site\adminBundle\Entity\categorie;

use \DateTime;

/**
 * article
 *
 * @ORM\Entity
 * @ORM\Table(name="article")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\articleRepository")
 */
class article {

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
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\statut")
	 * @ORM\JoinColumn(nullable=false, unique=false)
	 */
	protected $statut;

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
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\marque")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $marque;

	/**
	 * @var array
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\media", cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $image;

	/**
	 * @var array
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\media")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $images;

	/**
	 * @var array
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\media", cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
	 */
	protected $fichierPdf;

	/**
	 * @var array
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\media", cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
	 */
	protected $ficheTechniquePdf;

	/**
	 * @var array
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\categorie", mappedBy="articles")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $categories;

	/**
	 * @var array
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\fiche", inversedBy="articles")
	 * @ORM\JoinColumn(nullable=true, unique=false)
	 */
	protected $fiches;

	/**
	 * @var array
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\tag", inversedBy="articles")
	 * @ORM\JoinColumn(nullable=true, unique=false)
	 */
	protected $tags;

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

	/**
	 * @Gedmo\Slug(fields={"nom"})
	 * @ORM\Column(length=128, unique=true)
	 */
	protected $slug;

	protected $keywords;

	public function __construct() {
		$this->nom = null;
		$this->descriptif = null;
		$this->dateCreation = new DateTime();
		$this->dateMaj = null;
		$this->statut = null;
		$this->refFabricant = null;
		$this->accroche = null;
		$this->keywords = null;
		$this->prix = 0;
		$this->prixHT = 0;
		$this->tauxTva = null;
		$this->marque = null;
		$this->image = null;
		$this->images = new ArrayCollection();
		$this->fichierPdf = null;
		$this->ficheTechniquePdf = null;
		$this->categories = new ArrayCollection();
		$this->fiches = new ArrayCollection();
		$this->tags = new ArrayCollection();
		$this->articlesParents = new ArrayCollection();
		$this->articlesLies = new ArrayCollection();
	}

	/**
	 * 
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

	public function __toString() {
		return $this->getNom();
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
	 * @return article
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
	 * @return article
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
	 * @return article
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
	 * @return article
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
	 * Set statut
	 * @param statut $statut
	 * @return article
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
	public function setMarque(marque $marque) {
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
	 * Set image
	 * @param image $image
	 * @return article
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
	 * Add image
	 * @param media $image
	 * @return article
	 */
	public function addImage(media $image) {
		$this->images->add($image);
		$image->addArticle_reverse($this);
		return $this;
	}

	/**
	 * Add image reverse
	 * @param media $image
	 * @return article
	 */
	public function addImage_reverse(media $image) {
		$this->images->add($image);
		return $this;
	}

	/**
	 * Remove image
	 * @param media $image
	 */
	public function removeImage(media $image) {
		$image->removeArticle_reverse($this);
		return $this->images->removeElement($image);
	}

	/**
	 * Remove image reverse
	 * @param media $image
	 */
	public function removeImage_reverse(media $image) {
		return $this->images->removeElement($image);
	}

	/**
	 * Get images
	 * @return ArrayCollection 
	 */
	public function getImages() {
		return $this->images;
	}

	/**
	 * Set fichierPdf
	 * @param fichierPdf $fichierPdf
	 * @return article
	 */
	public function setFichierPdf(media $fichierPdf = null) {
		$this->fichierPdf = $fichierPdf;
		if($fichierPdf != null) $fichierPdf->setArticle_reverse($this);
			else $fichierPdf->setArticle_reverse(null);
		return $this;
	}

	/**
	 * Set fichierPdf reverse
	 * @param fichierPdf $fichierPdf
	 * @return article
	 */
	public function setFichierPdf_reverse(media $fichierPdf = null) {
		$this->fichierPdf = $fichierPdf;
		return $this;
	}

	/**
	 * Get fichierPdf
	 * @return media 
	 */
	public function getFichierPdf() {
		return $this->fichierPdf;
	}

	/**
	 * Set ficheTechniquePdf
	 * @param media $ficheTechniquePdf
	 * @return article
	 */
	public function setFicheTechniquePdf(media $ficheTechniquePdf = null) {
		$this->ficheTechniquePdf = $ficheTechniquePdf;
		if($ficheTechniquePdf != null) $ficheTechniquePdf->setArticle_reverse($this);
			else $ficheTechniquePdf->setArticle_reverse(null);
		return $this;
	}

	/**
	 * Set ficheTechniquePdf reverse
	 * @param media $ficheTechniquePdf
	 * @return article
	 */
	public function setFicheTechniquePdf_reverse(media $ficheTechniquePdf = null) {
		$this->ficheTechniquePdf = $ficheTechniquePdf;
		return $this;
	}

	/**
	 * Get ficheTechniquePdf
	 * @return media 
	 */
	public function getFicheTechniquePdf() {
		return $this->ficheTechniquePdf;
	}

	/**
	 * Get categories
	 * @return ArrayCollection 
	 */
	public function getCategories() {
		return $this->categories;
	}

	/**
	 * Add categorie
	 * @param categorie $categorie
	 * @return article
	 */
	public function addCategorie(categorie $categorie) {
		$this->categories->add($categorie);
		$categorie->addArticle_reverse($this);
		return $this;
	}

	/**
	 * Add categorie reverse
	 * @param categorie $categorie
	 * @return article
	 */
	public function addCategorie_reverse(categorie $categorie) {
		$this->categories->add($categorie);
		return $this;
	}

	/**
	 * Remove categorie
	 * @param categorie $categorie
	 * @return boolean
	 */
	public function removeCategorie(categorie $categorie) {
		$categorie->removeArticle_reverse($this);
		return $this->categories->removeElement($categorie);
	}

	/**
	 * Remove categorie
	 * @param categorie $categorie
	 * @return boolean
	 */
	public function removeCategorie_reverse(categorie $categorie) {
		return $this->categories->removeElement($categorie);
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
	public function addFiche(fiche $fiche, $doReverse = true) {
		if($doReverse == true) $fiche->addArticle($this, false);
		$this->fiches->add($fiches);
		return $this;
	}

	/**
	 * Remove fiche
	 * @param fiche $fiche
	 */
	public function removeFiche(fiche $fiche, $doReverse = true) {
		if($doReverse == true) $fiche->removeArticle($this, false);
		return $this->fiches->removeElement($fiche);
	}

	/**
	 * Set keywords
	 * @ORM\PostLoad
	 * @param string $keywords
	 * @return article
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
	 * @return article
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
		$this->articlesLies->add($articlesLies);
		$articlesLies->addArticlesParent($this);
		return $this;
	}

	/**
	 * Remove articlesLies
	 * @param article $articlesLies
	 */
	public function removeArticlesLie(article $articlesLies) {
		$this->articlesLies->removeElement($articlesLies);
		$articlesLies->removeArticlesParent($this);
	}

	/**
	 * Get articlesLies
	 * @return ArrayCollection 
	 */
	public function getArticlesLies() {
		return $this->articlesLies;
	}

}