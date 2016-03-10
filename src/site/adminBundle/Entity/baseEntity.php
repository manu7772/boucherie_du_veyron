<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use \ReflectionClass;
use \DateTime;
use \Exception;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class baseEntity {

	// const CLASS_ITEM			= 'item';
	// const CLASS_MEDIA		= 'media';
	// const CLASS_STATUT		= 'statut';
	// const CLASS_TAUXTVA		= 'tauxTva';

	protected $id;

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", nullable=true, unique=false)
	 */
	protected $nom;

	/**
	 * @var DateTime
	 * @ORM\Column(name="created", type="datetime", nullable=false)
	 */
	protected $created;

	/**
	 * @var DateTime
	 * @ORM\Column(name="updated", type="datetime", nullable=true)
	 */
	protected $updated;

	/**
	 * @Gedmo\Slug(fields={"nom"})
	 * @ORM\Column(length=128, unique=true)
	 */
	protected $slug;

	/**
	 * @var boolean
	 * @ORM\Column(name="default_value", type="boolean", nullable=false, unique=false)
	 */
	protected $default;


	public function __construct() {
		$this->nom = null;
		$this->created = new DateTime();
		$this->updated = null;
		$this->default = false;
	}

	public function __toString() {
		return $this->getNom();
	}

    // abstract public function getClassName();
    public function getClassName() {
        return $this->getClass(true);
    }

	/**
	 * Get id
	 * @return integer 
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Renvoie la liste (array) des classes des parents de l'entité
	 * @param boolean $short = false
	 * @return array
	 */
	public function getParentsClassNames($short = false) {
		$class = new ReflectionClass($this->getClass());
		$short ?
			$him = $class->getShortName():
			$him = $class->getName();
		$parents = array($him);
		while($class = $class->getParentClass()) {
			$short ?
				$parents[] = $class->getShortName():
				$parents[] = $class->getName();
		}
		return $parents;
	}

	/**
	 * Un élément par défaut dans la table est-il obligatoire ?
	 * @return boolean
	 */
	public function isDefaultNullable() {
		return false;
	}

	/**
	 * On peut attribuer plusieurs éléments par défaut ?
	 * true 		= illimité
	 * integer 		= nombre max. d'éléments par défaut
	 * false, 0, 1 	= un seul élément
	 * @return boolean
	 */
	public function isDefaultMultiple() {
		return false;
	}

	/**
	 * Renvoie le nom de la classe (short name par défaut)
	 * @param boolean $short = false
	 * @return string
	 */
	public function getClass($short = false) {
		$class = new ReflectionClass(get_called_class());
		return $short ?
			$class->getShortName():
			$class->getName();
	}

	/**
	 * Set nom
	 * @param string $nom
	 * @return baseEntity
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
	 * Set default
	 * @param string $default
	 * @return baseEntity
	 */
	public function setDefault($default) {
		$default == true ? $this->default = true : $this->default = false;
		return $this;
	}

	/**
	 * Get default
	 * @return boolean
	 */
	public function getDefault() {
		return $this->default;
	}

	/**
	 * Set created
	 * @param DateTime $created
	 * @return baseEntity
	 */
	public function setCreated(DateTime $created) {
		$this->created = $created;
		return $this;
	}

	/**
	 * Get created
	 * @return DateTime 
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * @ORM\PreUpdate
	 */
	public function updateDate() {
		$this->setUpdated(new DateTime());
	}

	/**
	 * Set updated
	 * @param DateTime $updated
	 * @return baseEntity
	 */
	public function setUpdated(DateTime $updated) {
		$this->updated = $updated;
		return $this;
	}

	/**
	 * Get updated
	 * @return DateTime 
	 */
	public function getUpdated() {
		return $this->updated;
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


}