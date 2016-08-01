<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use Labo\Bundle\AdminBundle\Entity\baseEntity;
use Labo\Bundle\AdminBundle\Entity\tier;
use site\UserBundle\Entity\User;

use \DateTime;
use \Exception;

/**
 * adresse
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\adresseRepository")
 * @ORM\Table(name="adresse", options={"comment":"adresses du site"})
 * @ORM\HasLifecycleCallbacks
 */
class adresse extends baseEntity {

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=100, nullable=true, unique=false)
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
	 * @ORM\Column(name="url", type="text", nullable=true, unique=false)
	 */
	protected $url;

	/**
	 * @var string
	 * @ORM\Column(name="type", type="string", length=32, nullable=true, unique=false)
	 */
	protected $type;

	/**
	 * @var string
	 * @ORM\Column(name="adresse", type="text", nullable=true, unique=false)
	 */
	protected $adresse;

	/**
	 * @var string
	 * @ORM\Column(name="cp", type="string", length=10, nullable=true, unique=false)
	 */
	protected $cp;

	/**
	 * @var string
	 * @ORM\Column(name="ville", type="string", length=255, nullable=true, unique=false)
	 */
	protected $ville;

	/**
	 * @var string
	 * @ORM\Column(name="commentaire", type="text", nullable=true, unique=false)
	 */
	protected $commentaire;

	/**
	 * - INVERSE
	 * @ORM\OneToOne(targetEntity="site\UserBundle\Entity\User", mappedBy="adresse")
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
	 */
	protected $user;

	/**
	 * - INVERSE
	 * @ORM\OneToOne(targetEntity="site\UserBundle\Entity\User", mappedBy="adresseLivraison")
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
	 */
	protected $userLivraison;

	/**
	 * - INVERSE
	 * @ORM\OneToOne(targetEntity="Labo\Bundle\AdminBundle\Entity\tier", mappedBy="adresse")
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
	 */
	protected $tier;



	public function __construct() {
		parent::__construct();
		$this->defineNom();
		$this->url = null;
		$this->type = null;
		$this->user = null;
		$this->userLivraison = null;
		$this->tier = null;
	}

	// public function memOldValues($addedfields = null) {
	// 	$fields = array('tier');
	// 	if(count($addedfields) > 0 && is_array($addedfields)) $fields = array_unique(array_merge($fields, $addedfields));
	// 	parent::memOldValues($fields);
	// 	return $this;
	// }

	/**
	 * @Assert\IsTrue(message="L'item n'est pas conforme.")
	 */
	public function isValid() {
		// return ($this->image == null) || $this->image->isValid();
		return true;
	}

	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 * Définit le nom si null
	 * @return adresse
	 */
	public function defineNom() {
		if($this->nom == null) {
			$date = new DateTime();
			$this->nom = $date->format('d-m-Y_H-i-s')."_".rand(100000,999999)."_".substr($this->adresse, 0, 20);
		}
		return $this;
	}

	/**
	 * Set type
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 * @return adresse
	 */
	public function setType() {
		$this->defineNom();
		$links = array(
			'user',
			'userLivraison',
			'tier',
			);
		$this->type = null;
		foreach ($links as $value) {
			$get = 'get'.ucfirst($value);
			$ob = $this->$get();
			if($ob !== null) {
				if(is_array($ob)) $ob = reset($ob);
				if(method_exists($ob, 'getClassName')) {
					$this->type = $ob->getClassName();
				} else {
					$this->type = $value;
				}
			}
		}
		return $this;
	}

	/**
	 * Get type
	 * @return string 
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Set url
	 * @param string $url
	 * @return url
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
	 * Set adresse
	 * @param string $adresse
	 * @return adresse
	 */
	public function setAdresse($adresse = null) {
		$this->adresse = $adresse;
		return $this;
	}

	/**
	 * Get adresse
	 * @return string 
	 */
	public function getAdresse() {
		return $this->adresse;
	}

	/**
	 * Set cp
	 * @param string $cp
	 * @return adresse
	 */
	public function setCp($cp = null) {
		$this->cp = $cp;
		return $this;
	}

	/**
	 * Get cp
	 * @return string 
	 */
	public function getCp() {
		return $this->cp;
	}

	/**
	 * Set ville
	 * @param string $ville
	 * @return adresse
	 */
	public function setVille($ville = null) {
		$this->ville = $ville;
		return $this;
	}

	/**
	 * Get ville
	 * @return string 
	 */
	public function getVille() {
		return $this->ville;
	}

	/**
	 * Set commentaire
	 * @param string $commentaire
	 * @return adresse
	 */
	public function setCommentaire($commentaire = null) {
		$this->commentaire = $commentaire;
		return $this;
	}

	/**
	 * Get commentaire
	 * @return string 
	 */
	public function getCommentaire() {
		return $this->commentaire;
	}


	/*************************/
	/*** TIERS             ***/
	/*************************/

	/**
	 * Set user - INVERSE
	 * @param User $user
	 * @return adresse
	 */
	public function setUser(User $user = null) {
		$this->user = $user;
		return $this;
	}    

	/**
	 * Get user - INVERSE
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Set userLivraison - INVERSE
	 * @param User $userLivraison
	 * @return adresse
	 */
	public function setUserLivraison(User $userLivraison = null) {
		$this->userLivraison = $userLivraison;
		return $this;
	}    

	/**
	 * Get userLivraison - INVERSE
	 * @return User
	 */
	public function getUserLivraison() {
		return $this->userLivraison;
	}

	/**
	 * Set tier - INVERSE
	 * @param tier $tier
	 * @return adresse
	 */
	public function setTier(tier $tier = null) {
		$this->tier = $tier;
		return $this;
	}    

	/**
	 * Get tier - INVERSE
	 * @return tier
	 */
	public function getTier() {
		return $this->tier;
	}

}