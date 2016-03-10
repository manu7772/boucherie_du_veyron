<?php
 
namespace site\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Yaml\Parser;

use site\adminBundle\Entity\media;
use site\adminBundle\Entity\message;
use site\adminBundle\Entity\panier;
use site\adminBundle\Entity\adresse;

use \DateTime;
use \ReflectionClass;

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
	 * @var integer - PROPRIÉTAIRE
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\adresse", cascade={"all"}, inversedBy="user")
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
	 */
	protected $adresse;

	/**
	 * @var integer - PROPRIÉTAIRE
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\adresse", cascade={"all"}, inversedBy="userLivraison")
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
	 */
	protected $adresseLivraison;

	/**
	 * @var string
	 * @ORM\Column(name="telephone", type="string", length=24, nullable=true, unique=false)
	 */
	protected $telephone;

	/**
	 * - INVERSE
	 * @ORM\OneToMany(targetEntity="site\adminBundle\Entity\panier", mappedBy="user", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
	 */
	private $paniers;

	/**
	 * - INVERSE
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
     * - PROPRIÉTAIRE
     * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\image", inversedBy="userAvatar", cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
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
		$this->adresse = null;
		$this->adresseLivraison = null;
		$this->langue = 'fr';
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
	 * Renvoie le nom court de la classe
	 * @return media
	 */
	public function getClassName() {
		$class = new ReflectionClass(get_called_class());
		return $class->getShortName();
	}

	/**
	 * Renvoie la liste (array) des classes des parents de l'entité
	 * @param boolean $short = false
	 * @return array
	 */
	public function getParentsClassNames($short = false) {
		$class = new ReflectionClass(get_called_class());
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
	 * Add panier - INVERSE
	 * @param panier $panier
	 * @return User
	 */
	public function addPanier(panier $panier) {
		$this->paniers->add($panier);
		return $this;
	}

	/**
	 * Remove panier - INVERSE
	 * @param panier $panier
	 * @return boolean
	 */
	public function removePanier(panier $panier) {
		return $this->paniers->removeElement($panier);
	}

	/**
	 * Renvoie le nombre total d'articles dans le panier de l'utilisateur
	 * @return integer
	 */
	public function getArticlesPanier() {
		$Q = 0;
		$paniers = $this->getPaniers();
		foreach ($paniers as $panier) {
			$Q += $panier->getQuantite();
		}
		return $Q;
	}

	/**
	 * Get paniers - INVERSE
	 * @return ArrayCollection 
	 */
	public function getPaniers() {
		return $this->paniers;
	}

	/**
	 * Add message - INVERSE
	 * @param message $message
	 * @return User
	 */
	public function addMessage(message $message) {
		$this->messages->add($message);
		return $this;
	}

	/**
	 * Remove message - INVERSE
	 * @param message $message
	 * @return boolean
	 */
	public function removeMessage(message $message) {
		return $this->messages->removeElement($message);
	}

	/**
	 * Get messages - INVERSE
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
		if($adminhelp == false) $this->adminhelp = false;
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
	 * Set avatar - PROPRIÉTAIRE
	 * @param media $avatar
	 * @return pageweb
	 */
	public function setAvatar(media $avatar = null) {
		$avatar->setUserAvatar($this);
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
	 * Set adresse - PROPRIÉTAIRE
	 * @param adresse $adresse
	 * @return User
	 */
	public function setAdresse(adresse $adresse = null) {
		$adresse->setUser($this);
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
	 * Set adresseLivraison - PROPRIÉTAIRE
	 * @param adresse $adresseLivraison
	 * @return User
	 */
	public function setAdresseLivraison(adresse $adresseLivraison = null) {
		$adresseLivraison->setUserLivraison($this);
		$this->adresseLivraison = $adresseLivraison;
		return $this;
	}

	/**
	 * Get adresseLivraison - PROPRIÉTAIRE
	 * @return adresse 
	 */
	public function getAdresseLivraison() {
		return $this->adresseLivraison;
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
		// $pathToSecurity = __DIR__.'/../../../../app/config/security.yml';
		// $yaml = new Parser();
		// $rolesArray = $yaml->parse(file_get_contents($pathToSecurity));
		// $this->validRoles = $rolesArray['security']['role_hierarchy'];
		$this->validRoles = array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY', 1 => 'ROLE_USER', 2 => 'ROLE_TRANSLATOR', 3 => 'ROLE_EDITOR', 4 => 'ROLE_ADMIN', 5 => 'ROLE_SUPER_ADMIN');
		foreach($this->validRoles as $value => $roleToTest) {
			if(in_array($roleToTest, $user_roles)) $best_role = $roleToTest;
		}
		if($best_role === null) $best_role = reset($this->validRoles);
		return $best_role;
	}

	/**
	 * Renvoie les granted de User
	 * @return array
	 */
	public function getGrants() {
		$pathToSecurity = __DIR__.'/../../../../app/config/security.yml';
		$yaml = new Parser();
		$rolesArray = $yaml->parse(file_get_contents($pathToSecurity));
		return array_merge(array($this->getBestRole()), $rolesArray['security']['role_hierarchy'][$this->getBestRole()]);
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