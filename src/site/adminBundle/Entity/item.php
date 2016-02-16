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
// use site\UserBundle\Entity\User;

use \DateTime;
use \Exception;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\itemRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="class_name", type="string")
 * @ORM\DiscriminatorMap({"article" = "article", "fiche" = "fiche", "pageweb" = "pageweb"})
 * @ORM\HasLifecycleCallbacks
 * 
 * @ExclusionPolicy("all")
 */
abstract class item extends baseSubEntity {

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
     *  - PROPRIÉTAIRE
     * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\image", inversedBy="item", cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
     */
    private $image;

	/**
	 * - PROPRIÉTAIRE
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\categorie", inversedBy="items")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $categories;


	public function __construct() {
		parent::__construct();
		$this->image = null;
		// $this->images = new ArrayCollection();
		$this->categories = new ArrayCollection();
	}


	/**
	 * Get keywords
	 * @return string 
	 */
	public function getKeywords() {
		return implode($this->getTags()->toArray(), ', ');
	}

	/**
	 * Get keywords
	 * @return array 
	 */
	public function getArrayKeywords() {
		return $this->getTags()->toArray();
	}

	/**
	 * Renvoie l'image principale
	 * @return image
	 */
	public function getMainMedia() {
		return $this->getImage();
	}

	/**
	 * Set image - PROPRIÉTAIRE
	 * @param image $image
	 * @return item
	 */
	public function setImage(image $image = null) {
		$image->setItem($this);
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
	 * @return item
	 */
	public function addCategorie(categorie $categorie) {
		$categorie->addItem($this);
		$this->categories->add($categorie);
		return $this;
	}

	/**
	 * Remove categorie - PROPRIÉTAIRE
	 * @param categorie $categorie
	 * @return boolean
	 */
	public function removeCategorie(categorie $categorie) {
		$categorie->removeItem($this);
		return $this->categories->removeElement($categorie);
	}


}