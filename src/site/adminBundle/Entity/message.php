<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use site\adminBundle\Entity\statut;
use site\UserBundle\Entity\User;

use \DateTime;
use \DateInterval;
use \ReflectionClass;

/**
 * message
 *
 * @ORM\Table()
 * @ORM\Table(name="message")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\messageRepository")
 */
class message {

	/**
	 * @var integer
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var DateTime
	 * @ORM\Column(name="isread", type="datetime", nullable=true, unique=false)
	 */
	private $read;

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=128, nullable=true, unique=false)
	 * @Assert\Length(
	 *      min = "3",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 * )
	 */
	private $nom;

	/**
	 * @var string
	 * @ORM\Column(name="prenom", type="string", length=128, nullable=true, unique=false)
	 * @Assert\Length(
	 *      min = "3",
	 *      minMessage = "Le prénom doit comporter au moins {{ limit }} lettres.",
	 * )
	 */
	private $prenom;

	/**
	 * @var string
	 * @Assert\Email(
	 *     message = "The email '{{ value }}' is not a valid email.",
	 *     checkMX = true
	 * )
	 * @ORM\Column(name="email", type="string", nullable=false, unique=false)
	 */
	private $email;

	/**
	 * @var string
	 * @ORM\Column(name="telephone", type="string", nullable=true, unique=false)
	 */
	private $telephone;

	/**
	 * @var string
	 * @ORM\Column(name="objet", type="string", length=255, nullable=true, unique=false)
	 */
	private $objet;

	/**
	 * @var string
	 * @ORM\Column(name="message", type="text", nullable=false, unique=false)
	 * @Assert\NotBlank(message = "entity.notblank.nom")
	 * @Assert\Length(
	 *      min = "3",
	 *      minMessage = "Le message doit comporter au moins {{ limit }} lettres.",
	 * )
	 */
	private $message;

	/**
	 * @var DateTime
	 * @ORM\Column(name="creation", type="datetime", nullable=false, unique=false)
	 */
	private $creation;

	/**
	 * @var string
	 * @ORM\Column(name="ip", type="string", length=32, nullable=true, unique=false)
	 */
	private $ip;

	/**
	 * @ORM\ManyToOne(targetEntity="statut")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=false, unique=false)
	 */
	protected $statut;

	/**
	 * @ORM\ManyToOne(targetEntity="site\UserBundle\Entity\User", inversedBy="messages")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $user;


	public function __construct() {
		$this->creation = new DateTime();
		$this->read = null;
	}

	public function __toString() {
		return $this->getObjet()."/".$this->getNom();
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
	 * Set read
	 * @param string $read
	 * @return message
	 */
	public function setRead() {
		if(!$this->isRead()) $this->read = new DateTime();
		return $this;
	}

	/**
	 * Get read
	 * @return DateTime
	 */
	public function getRead() {
		return $this->read;
	}

	/**
	 * Is read
	 * @return boolean
	 */
	public function isRead() {
		return $this->read != null;
	}

	/**
	 * Set nom
	 * @param string $nom
	 * @return message
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
	 * @return message
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
	 * Set email
	 * @param string $email
	 * @return message
	 */
	public function setEmail($email) {
		$this->email = $email;
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
	 * Set telephone
	 * @param string $telephone
	 * @return message
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
	 * Set objet
	 * @param string $objet
	 * @return message
	 */
	public function setObjet($objet) {
		$this->objet = $objet;
		return $this;
	}

	/**
	 * Get objet
	 * @return string
	 */
	public function getObjet() {
		return $this->objet;
	}

	/**
	 * Set message
	 * @param string $message
	 * @return message
	 */
	public function setMessage($message) {
		$this->message = $message;
		return $this;
	}

	/**
	 * Get message
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * Set creation
	 * @param DateTime $creation
	 * @return message
	 */
	public function setCreation($creation) {
		$this->creation = $creation;
		return $this;
	}

	/**
	 * Get creation
	 * @return DateTime
	 */
	public function getCreation() {
		return $this->creation;
	}

	/**
	 * Set ip
	 * @param string $ip
	 * @return message
	 */
	public function setIp($ip) {
		$this->ip = $ip;
		return $this;
	}

	/**
	 * Get ip
	 * @return string
	 */
	public function getIp() {
		return $this->ip;
	}

	/**
	 * Set statut
	 * @param statut $statut
	 * @return article
	 */
	public function setStatut(statut $statut) {
		$this->statut = $statut;
		return $this;
	}

	/**
	 * Get statut
	 * @return statut 
	 */
	public function getStatut() {
		return $this->statut;
	}

	/**
	 * Set user
	 * @param user $user
	 * @return article
	 */
	public function setUser(User $user = null, $doReverse = true) {
		if($doReverse == true && $user != null) $user->addMessage($this, false);
		$this->user = $user;
		return $this;
	}

	/**
	 * Get user
	 * @return user 
	 */
	public function getUser() {
		return $this->user;
	}


}

