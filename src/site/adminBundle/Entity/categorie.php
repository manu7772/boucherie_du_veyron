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
	 * @ORM\OneToMany(targetEntity="site\adminBundle\Entity\categorie", mappedBy="parent")
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
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\baseSubEntity", mappedBy="categories", cascade={"persist","remove"})
	 * @ORM\JoinColumn(name="basesubentity_categorie", nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $subEntitys;

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


	public function __construct() {
		parent::__construct();
		$this->pageweb = null;
		$this->parent = null;
		$this->children = new ArrayCollection();
		$this->descriptif = null;
		$this->subEntitys = new ArrayCollection();
		$this->couleur = "rgba(255,255,255,1)";
		$this->url = null;
	}

	/**
	 * Renvoie le nom court de la classe
	 * @return media
	 */
	// public function getClassName() {
	// 	return self::CLASS_CATEGORIE;
	// }

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
	}

	public function getParent() {
		return $this->parent;
	}

	public function getChildren() {
		return $this->children;
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

	// public function addChildren(categorie $children = null) {
	// 	return $this->children;
	// }

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



}
