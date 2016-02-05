<?php
 
namespace site\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use site\adminBundle\Entity\media;
use site\adminBundle\Entity\message;
use site\adminBundle\Entity\panier;

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
	 * @var string
	 * @ORM\Column(name="telephone", type="string", length=24, nullable=true, unique=false)
	 */
	protected $telephone;

	/**
	 * @ORM\OneToMany(targetEntity="site\adminBundle\Entity\panier", mappedBy="user", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
	 */
	private $paniers;

	/**
	 * @ORM\OneToMany(targetEntity="site\adminBundle\Entity\message", mappedBy="user")
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
	 */
	private $messages;

	/**
	 * @var boolean
	 * @ORM\Column(name="adminhelp", type="boolean", nullable=false, unique=false)
	 */
	private $adminhelp;

	/**
	 * @ORM\Column(name="admintheme", type="string", length=32, unique=false, nullable=true)
	 */
	protected $admintheme;

    /**
     * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\media", mappedBy="userAvatar", cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=true, name="userAvatar_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $avatar;

	/**
	 * @ORM\Column(name="langue", type="string", length=32, unique=false, nullable=true)
	 */
	protected $langue;

	protected $adminskins;
	private $validRoles;

	public function __construct() {
		parent::__construct();
		$this->paniers = new ArrayCollection();
		$this->messages = new ArrayCollection();
		$this->adminhelp = true;
		$this->admintheme = $this->getDefaultAdminskin();
		$this->avatar = null;
		$this->langue = 'default_locale';
		$this->validRoles = array(1 => 'ROLE_USER', 2 => 'ROLE_TRANSLATOR', 3 => 'ROLE_EDITOR', 4 => 'ROLE_ADMIN', 5 => 'ROLE_SUPER_ADMIN');
	}

	public function getAdminskins() {
		return array("skin-0" => "skin-0", "skin-1" => "skin-1", "skin-2" => "skin-2", "skin-3" => "skin-3");
	}

	public function getDefaultAdminskin() {
		$skins = $this->getAdminskins();
		return reset($skins);
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
	 * @return User
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
	 * Set prenom
	 * @param string $prenom
	 * @return User
	 */
	public function setPrenom($prenom) {
		$this->prenom = $prenom;
		return $this;
	}

	/**
	 * Get prenom
	 * @return string 
	 */
	public function getPrenom() {
		return $this->prenom;
	}

	/**
	 * Set telephone
	 * @param string $telephone
	 * @return User
	 */
	public function setTelephone($telephone) {
		$this->telephone = $telephone;
		return $this;
	}

	/**
	 * Get telephone
	 * @return string 
	 */
	public function getTelephone() {
		return $this->telephone;
	}

	/**
	 * Add panier
	 * @param panier $panier
	 * @return User
	 */
	public function addPanier(panier $panier, $doReverse = true) {
		if($doReverse == true) $panier->setUser($this, false);
		$this->paniers->add($panier);
		return $this;
	}

	/**
	 * Remove panier
	 * @param panier $panier
	 * @return boolean
	 */
	public function removePanier(panier $panier) {
		return $this->paniers->removeElement($panier);
	}

	/**
	 * Get paniers
	 * @return ArrayCollection 
	 */
	public function getPaniers() {
		return $this->paniers;
	}

	/**
	 * Add message
	 * @param message $message
	 * @return User
	 */
	public function addMessage(message $message, $doReverse = true) {
		if($doReverse == true) $message->setUser($this, false);
		$this->messages->add($message);
		return $this;
	}

	/**
	 * Remove message
	 * @param message $message
	 * @return boolean
	 */
	public function removeMessage(message $message) {
		return $this->messages->removeElement($message);
	}

	/**
	 * Get messages
	 * @return ArrayCollection 
	 */
	public function getMessages() {
		return $this->messages;
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
	 * Set admintheme
	 * @param boolean $admintheme
	 * @return User
	 */
	public function setAdmintheme($admintheme) {
		$skins = $this->getAdminskins();
		if(in_array($admintheme, $skins)) $this->admintheme = $admintheme;
			else $this->admintheme = $this->getDefaultAdminskin();
		return $this;
	}

	/**
	 * Get admintheme
	 * @return boolean 
	 */
	public function getAdmintheme() {
		$skins = $this->getAdminskins();
		if(in_array($this->admintheme, $skins)) return $this->admintheme;
			else return $this->getDefaultAdminskin();
	}

	/**
	 * Set avatar
	 * @param media $avatar
	 * @return pageweb
	 */
	public function setAvatar(media $avatar = null) {
		$this->avatar = $avatar;
		$avatar->setUserAvatar_reverse($this);
		return $this;
	}

	/**
	 * Set avatar
	 * @param media $avatar
	 * @return pageweb
	 */
	public function setAvatar_reverse(media $avatar = null) {
		$this->avatar = $avatar;
		return $this;
	}

	/**
	 * Get avatar
	 * @return media 
	 */
	public function getAvatar() {
		return $this->avatar;
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

	/**
	 * Renvoie le nom du plus haut role d'un user (ou de l'user de cette entité)
	 * @param User $user = null
	 * @return string
	 */
	public function getBestRole(User $user = null) {
		if($user === null) $user = $this;
		$user_roles = $user->getRoles();
		$best_role = null;
		$this->validRoles = array(1 => 'ROLE_USER', 2 => 'ROLE_TRANSLATOR', 3 => 'ROLE_EDITOR', 4 => 'ROLE_ADMIN', 5 => 'ROLE_SUPER_ADMIN');
		foreach($this->validRoles as $value => $roleToTest) {
			if(in_array($roleToTest, $user_roles)) $best_role = $roleToTest;
		}
		if($best_role === null) $best_role = reset($this->validRoles);
		return $best_role;
	}

	/**
	 * Renvoie la valeur du plus haut role d'un user (ou de l'user de cette entité)
	 * @param User $user = null
	 * @return integer
	 */
	public function getBestRoleValue(User $user = null) {
		$nom_role = $this->getBestRole($user);
		$results = array_keys($this->validRoles, $nom_role);
		if(count($results) > 0) return reset($results);
		return 0;
	}

	/**
	 * Renvoie true si l'utilisateur a des droits au moins identiques sur l'User passé en paramètre
	 * @param User $user
	 * @return boolean
	 */
	public function haveRight(User $user) {
		return $this->getBestRoleValue() >= $user->getBestRoleValue() ? true : false;
	}



}