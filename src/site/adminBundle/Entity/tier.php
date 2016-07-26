<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use site\adminBundle\Entity\nested;
use site\adminBundle\Entity\categorie;
use site\adminBundle\Entity\adresse;

use \DateTime;
use \Exception;

/**
 * tier
 * 
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\tierRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="class_name", type="string")
 * @ORM\HasLifecycleCallbacks
 */
abstract class tier extends nested {

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

	/**
	 * @var string
	 * @ORM\Column(name="telfixe", type="string", length=14, nullable=true, unique=false)
	 */
	protected $telfixe;

	/**
	 * @var string
	 * @ORM\Column(name="mobile", type="string", length=14, nullable=true, unique=false)
	 */
	protected $mobile;

	/**
	 * @var string
	 * @ORM\Column(name="email", type="string", length=128, nullable=true, unique=false)
	 */
	protected $email;


	public function __construct() {
		parent::__construct();
		$this->adresse = null;
		$this->telfixe = null;
		$this->mobile = null;
		$this->email = null;
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

	protected function formatTel($tel) {
		// return preg_replace_callback(
		// 	'#(\d)#',
		// 	function($matches) {
		// 		return $matches[0];
		// 	},
		// 	$tel
		// );
		return $tel;
	}

	/**
	 * Get telfixe
	 * @return string
	 */
	public function getTelfixe() {
		return $this->formatTel($this->telfixe);
	}

	/**
	 * Set telfixe
	 * @param string $telfixe
	 * @return tier
	 */
	public function setTelfixe($telfixe) {
		$this->telfixe = $this->formatTel($telfixe);
		return $this;
	}

	// /**
	//  * @Assert\IsTrue(message="Le téléphone n'est pas bien renseigné.")
	//  */
	// public function isTelfixe() {
	// 	return preg_match('#^([\d]{2}(\s)?){4}[\d]{2}$#', $this->telfixe) || $this->telfixe == null;
	// }

	/**
	 * Get mobile
	 * @return string
	 */
	public function getMobile() {
		return $this->formatTel($this->mobile);
	}

	/**
	 * Set mobile
	 * @param string $mobile
	 * @return tier
	 */
	public function setMobile($mobile) {
		$this->mobile = $this->formatTel($mobile);
		return $this;
	}

	/**
	 * Get email
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Set email
	 * @param string $email
	 * @return tier
	 */
	public function setEmail($email) {
		$this->email = $email;
		return $this;
	}

}