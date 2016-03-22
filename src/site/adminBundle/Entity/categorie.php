<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\baseSubEntity;
use site\adminBundle\Entity\baseEntity;
use site\adminBundle\Entity\pageweb;

use \DateTime;

/**
 * categorie
 *
 * @ORM\Entity
 * @ORM\Table(name="categorie")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\categorieRepository")
 * @Gedmo\Tree(type="nested")
 */
class categorie extends baseEntity {

	// const CLASS_CATEGORIE = 'categorie';

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
	 * @Gedmo\SortableGroup
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\categorie", inversedBy="children", cascade={"persist"})
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $parent;

	/**
	 * @ORM\OneToMany(targetEntity="site\adminBundle\Entity\categorie", mappedBy="parent", cascade={"persist","remove"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 * @ORM\OrderBy({"lft" = "ASC"})
	 */
	protected $children;

	/**
	 * https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/sortable.md
	 * @Gedmo\SortablePosition
	 * @ORM\Column(type="integer", nullable=false)
	 */
	protected $position;

	/**
	 * @var string
	 * @ORM\Column(name="descriptif", type="text", nullable=true, unique=false)
	 */
	protected $descriptif;

	/**
	 * - INVERSE
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\baseSubEntity", mappedBy="categories")
	 * @ORM\JoinColumn(name="basesubentity_categorie", nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $subEntitys;

	/**
	 * Classes d'entités accceptées pour subEntitys
	 * @var string
	 * @ORM\Column(name="accept", type="text", nullable=true, unique=false)
	 */
	protected $accepts;

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

    /**
     * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\image", orphanRemoval=true, cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
     */
    protected $image;


	// Liste des termes valides pour accept
	protected $accept_list = array(
		'article'			=> 'Articles',
		'image'				=> 'Images',
		'pdf'				=> 'Fichiers PDF',
		'fiche'				=> 'Fiches recettes',
		'marque'			=> 'Marques',
		'reseau'			=> 'Réseaux',
		'boutique'			=> 'Boutiques',
		'pageweb'			=> 'Pages web',
	);
	// subEntitys mémorisées
	protected $subEntitysMem;

	public function __construct() {
		parent::__construct();
		$this->pageweb = null;
		$this->parent = null;
		$this->children = new ArrayCollection();
		$this->descriptif = null;
		$this->subEntitys = new ArrayCollection();
		$this->couleur = "rgba(255,255,255,1)";
		$this->url = null;
		$this->accepts = json_encode(array());
		$this->image = null;
		$this->subEntitysMem = array();
	}

	/**
	 * @ORM\PostLoad
	 * 
	 * mémorise subEntitys
	 * @return categorie
	 */
	public function PostLoad() {
		$this->subEntitysMem = $this->getSubEntitys()->toArray();
		return $this;
	}

	/**
	 * Renvoie l'image principale
	 * @return image
	 */
	public function getMainMedia() {
		return $this->getImage();
	}

	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 * 
	 * Check categorie
	 * @return array
	 */
	public function check() {
		return $this->getRootAccepts(true);
	}

	/**
	 * Un élément par défaut dans la table est-il obligatoire ?
	 * @return boolean
	 */
	public function isDefaultNullable() {
		return true;
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

	public function getLvl() {
		return $this->lvl;
	}

	public function setParent(categorie $parent = null) {
		$this->parent = $parent;
		$parent->addChildren($this);
		return $this;
	}

	public function getParent() {
		return $this->parent;
	}

	public function getParents($includeThis = false) {
		if($includeThis === true) $parents = array($this);
			else $parents = array();
		if($this->getParent() != null) {
			$parent = $this->getParent();
			$parents = array_merge($parents, $parent->getParents(true));
		}
		return $parents;
	}

	public function getChildren() {
		return $this->children;
	}

	public function getAllChildren() {
		$allChildren = $this->getChildren()->toArray();
		if(is_array($allChildren)) foreach ($allChildren as $children) $allChildren = array_merge($allChildren, $children->getAllChildren());
		// if(is_array($allChildren)) foreach ($allChildren as $children) $allChildren = array_unique(array_merge($allChildren, $children->getAllChildren()));
			else $allChildren = array();
		return $allChildren;
	}

	/**
	 * Set position
	 * @param integer $position
	 * @return categorie
	 */
	public function setPosition($position) {
		$this->position = $position;
	}

	/**
	 * Get position
	 * @return integer
	 */
	public function getPosition() {
		return $this->position;
	}

	public function addChildren(categorie $children = null) {
		if(!$this->children->contains($children)) $this->children->add($children);
		return $this;
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
	 * add subEntity
	 * @param baseSubEntity $subEntity
	 * @return categorie
	 */
	public function addSubEntity(baseSubEntity $subEntity = null) {
		if($subEntity != null) $this->subEntitys->add($subEntity);
		return $this;
	}

	/**
	 * remove subEntity
	 * @param baseSubEntity $subEntity
	 * @return boolean
	 */
	public function removeSubEntity(baseSubEntity $subEntity) {
		return $this->subEntitys->removeElement($subEntity);
	}

	/**
	 * get subEntitys
	 * @return ArrayCollection
	 */
	public function getSubEntitys() {
		return $this->subEntitys;
	}

	/**
	 * get subEntitys + subEntitys of children
	 * @return ArrayCollection
	 */
	public function getAllSubEntitys() {
		$subEntitys = $this->getSubEntitys()->toArray();
		foreach ($this->getChildren() as $child) {
			$subEntitys = array_merge($subEntitys, $child->getAllSubEntitys());
		}
		return array_unique($subEntitys);
	}

	/**
	 * get subEntitys
	 * @return ArrayCollection
	 */
	public function getSubEntitysMem() {
		return $this->subEntitysMem;
	}

	public function getAcceptsList() {
		return $this->accept_list;
	}

	/**
	 * add accept
	 * @param json/array $accept = null
	 * @return boolean
	 */
	public function addAccept($accept) {
		if(is_array($accept)) foreach ($accept as $value) $this->addAccept($value);
		$accepts = $this->getAccepts();
		if(!in_array($accept, $accepts) && array_key_exists($accept, $this->accept_list)) {
			$accepts[] = $accept;
			$this->accepts = json_encode($accepts);
			return true;
		}
		return false;
	}

	/**
	 * remove accept
	 * @param json/array $accept = null
	 * @return boolean
	 */
	public function removeAccept($accept) {
		$accepts = $this->getAccepts();
		$keys = array_keys($accepts);
		if(count($keys) > 0) {
			foreach ($keys as $key) unset($accepts[$key]);
			$this->accepts = json_encode($accepts);
			return true;
		}
		return false;
	}

	/**
	 * set accepts
	 * @param json/array $accepts = null
	 * @return categorie
	 */
	public function setAccepts($accepts = null) {
		$test = false;
		if(is_string($accepts)) {
			$accepts2 = json_decode($accepts);
			if($accepts2 == null) $accepts = array($accepts);
				else $accepts = $accept2;
		}
		if(is_array($accepts)) {
			if(count($accepts) > 0) $test = true;	
		}
		if($test === true) {
			foreach ($accepts as $key => $accept) {
				if(!array_key_exists($accept, $this->accept_list)) unset($accepts[$key]);
			}
			$this->accepts = json_encode($accepts);
		}
		return $this;
	}

	/**
	 * has accept
	 * @return boolean
	 */
	public function hasAccept($accept) {
		$accepts = $this->getAccepts();
		return in_array($accept, $accepts);
	}

	/**
	 * get accepts
	 * @return array
	 */
	public function getAccepts() {
		return json_decode($this->accepts, true);
	}

	/**
	 * get accepts
	 * @param boolean $setForThis = false
	 * @return array
	 */
	public function getRootAccepts($setForThis = false) {
		$parents = $this->getParents();
		if(count($parents) > 0) {
			$rootParent = end($parents);
			if($setForThis === true) $this->setAccepts($rootParent->getAccepts());
		} else return $this->getAccepts();
		return $rootParent->getAccepts();
	}

	/**
	 * Set couleur
	 * @param string $couleur
	 * @return categorie
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

	/**
	 * Set image - PROPRIÉTAIRE
	 * @param image $image
	 * @return categorie
	 */
	public function setImage(image $image = null) {
		$this->image = $image;
		if($image != null) $image->setOwner($this->getClassName().':'.'image');
		return $this;
	}

	/**
	 * Get image - PROPRIÉTAIRE
	 * @return image $image
	 */
	public function getImage() {
		return $this->image;
	}



}
