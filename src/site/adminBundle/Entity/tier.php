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
     * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\image", orphanRemoval=true, cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
	 */
	protected $logo;


	public function __construct() {
		parent::__construct();
		$this->adresse = null;
	}

	/**
	 * Renvoie l'image principale
	 * @return image
	 */
	public function getMainMedia() {
		if($this->getLogo() !== null) return $this->getLogo();
		if($this->getImage() !== null) return $this->getImage();
		return null;
	}

	public function memOldValues($addedfields = null) {
		$fields = array('adresse');
		if(count($addedfields) > 0 && is_array($addedfields)) $fields = array_unique(array_merge($fields, $addedfields));
		parent::memOldValues($fields);
		return $this;
	}

	/**
	 * Set adresse - PROPRIÉTAIRE
	 * @param adresse $adresse
	 * @return tier
	 */
	public function setAdresse(adresse $adresse = null) {
		// $adresse->setTier($this);
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
	 * Set logo - PROPRIÉTAIRE
	 * @param image $logo
	 * @return tier
	 */
	public function setLogo(image $logo = null) {
		if($this->logo != null && $logo == null) {
			$this->logo->setElement(null);
		}
		$this->logo = $logo;
		if($this->logo != null) {
			$this->logo->setElement($this, 'logo');
			$this->logo->setStatut($this->getStatut());
		}
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