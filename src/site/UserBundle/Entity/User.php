<?php
 
namespace site\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="User")
 * @ORM\Entity(repositoryClass="site\UserBundle\Entity\UserRepository")
 */
class User extends BaseUser {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nom", type="string", length=50, nullable=true, unique=false)
	 * @Assert\NotBlank(message = "Vous devez préciser votre nom.")
	 * @Assert\Length(
	 *      min = "3",
	 *      max = "50",
	 *      minMessage = "Votre nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Votre nom peut comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="prenom", type="string", length=100, nullable=true, unique=false)
	 * @Assert\Length(
	 *      min = "3",
	 *      max = "50",
	 *      minMessage = "Votre prénom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Votre prénom peut comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $prenom;

	/**
	 * @var boolean
	 * @ORM\Column(name="adminhelp", type="boolean", nullable=false, unique=false)
	 */
	private $adminhelp;

	/**
	 * @ORM\Column(name="langue", type="string", length=32, unique=false, nullable=true)
	 */
	protected $langue;


	public function __construct() {
		parent::__construct();
		$this->adminhelp = true;
		$this->langue = 'default_locale';
	}

	/**
	 * Get id
	 *
	 * @return integer 
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set nom
	 *
	 * @param string $nom
	 * @return User
	 */
	public function setNom($nom) {
		$this->nom = $nom;
	
		return $this;
	}

	/**
	 * Get nom
	 *
	 * @return string 
	 */
	public function getNom() {
		return $this->nom;
	}

	/**
	 * Set prenom
	 *
	 * @param string $prenom
	 * @return User
	 */
	public function setPrenom($prenom) {
		$this->prenom = $prenom;
	
		return $this;
	}

	/**
	 * Get prenom
	 *
	 * @return string 
	 */
	public function getPrenom() {
		return $this->prenom;
	}

	/**
	 * Set adminhelp
	 * @param boolean $adminhelp
	 * @return User
	 */
	public function setAdminhelp($adminhelp) {
		if(is_bool($adminhelp)) $this->adminhelp = $adminhelp;
			else $this->adminhelp = true;
		return $this;
	}

	/**
	 * Get adminhelp
	 * @return boolean 
	 */
	public function getAdminhelp() {
		return $this->adminhelp;
	}

	/**
	 * get langue
	 * @return string
	 */
	public function getLangue() {
		return $this->langue;
	}

	/**
	 * set langue
	 * @param string $langue
	 * @return User
	 */
	public function setLangue($langue = null) {
		if($langue !== null) $this->langue = $langue;
		return $this;
	}



}