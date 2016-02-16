<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\baseSubEntity;

use site\adminBundle\Entity\categorie;
use site\adminBundle\Entity\adresse;
// use site\UserBundle\Entity\User;

use \DateTime;
use \Exception;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\tierRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="class_name", type="string")
 * @ORM\DiscriminatorMap({"boutique" = "boutique", "marque" = "marque", "reseau" = "reseau"})
 * @ORM\HasLifecycleCallbacks
 * 
 * @ExclusionPolicy("all")
 */
abstract class tier extends baseSubEntity {

    // const CLASS_ARTICLE		= "article";
    // const CLASS_FICHE		= "fiche";
    // const CLASS_PAGEWEB		= "pageweb";

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var integer - PROPRIÉTAIRE
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\adresse", cascade={"all"}, inversedBy="tier")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $adresse;

	/**
	 * - PROPRIÉTAIRE
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\categorie", inversedBy="tiers")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $categories;

    /**
     *  - PROPRIÉTAIRE
     * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\image", inversedBy="tier", cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
     */
    private $image;

	/**
	 * @var integer - PROPRIÉTAIRE
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\image", inversedBy="logoTier", cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $logo;


	public function __construct() {
		parent::__construct();
		$this->adresse = null;
		$this->categories = new ArrayCollection();
		$this->image = null;
		$this->logo = null;
	}

	/**
	 * Renvoie l'image principale
	 * @return image
	 */
	public function getMainMedia() {
		return $this->getLogo();
	}


	/**
	 * Set adresse - PROPRIÉTAIRE
	 * @param adresse $adresse
	 * @return tier
	 */
	public function setAdresse(adresse $adresse = null) {
		$adresse->setTier($this);
		$this->adresse = $adresse;
		return $this;
	}

	/**
	 * Get adresse - PROPRIÉTAIRE
	 * @return adresse 
	 */
	public function getAdresse() {
		return $this->adresse;
	}

	/**
	 * Set image - PROPRIÉTAIRE
	 * @param image $image
	 * @return tier
	 */
	public function setImage(image $image = null) {
		$image->setTier($this);
		$this->image = $image;
		return $this;
	}

	/**
	 * Get image - PROPRIÉTAIRE
	 * @return image $image
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * Get categories - PROPRIÉTAIRE
	 * @return ArrayCollection 
	 */
	public function getCategories() {
		return $this->categories;
	}

	/**
	 * Add categorie - PROPRIÉTAIRE
	 * @param categorie $categorie
	 * @return tier
	 */
	public function addCategorie(categorie $categorie) {
		$categorie->addTier($this);
		$this->categories->add($categorie);
		return $this;
	}

	/**
	 * Remove categorie - PROPRIÉTAIRE
	 * @param categorie $categorie
	 * @return boolean
	 */
	public function removeCategorie(categorie $categorie) {
		$categorie->removeTier($this);
		return $this->categories->removeElement($categorie);
	}

	/**
	 * Set logo - PROPRIÉTAIRE
	 * @param image $logo
	 * @return tier
	 */
	public function setLogo(image $logo = null) {
		$logo->setLogoTier($this);
		$this->logo = $logo;
		return $this;
	}

	/**
	 * Get logo - PROPRIÉTAIRE
	 * @return image 
	 */
	public function getLogo() {
		return $this->logo;
	}


}