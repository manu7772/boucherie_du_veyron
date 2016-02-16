<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\article;
use site\UserBundle\Entity\User;

use \DateTime;
use \ReflectionClass;

/**
 * panier
 *
 * @ORM\Entity
 * @ORM\Table(name="panier")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\panierRepository")
 */
class panier {

	// const CLASS_PANIER = 'panier';

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="site\UserBundle\Entity\User", inversedBy="paniers")
	 * @ORM\JoinColumn(nullable=false, unique=false)
	 */
	protected $user;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\article")
	 * @ORM\JoinColumn(nullable=false, unique=false)
	 */
	protected $article;

	/**
	 * https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/sortable.md
	 * @Gedmo\SortablePosition
	 * @ORM\Column(type="integer")
	 */
	private $position;

	/**
	 * @var integer
	 * @ORM\Column(name="quantite", type="integer", nullable=false, unique=false)
	 */
	protected $quantite;

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


	public function __construct() {
		$this->created = new DateTime();
		$this->updated = null;
		$this->quantite = 0;
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

	public function getId() {
		return 'User#'.$this->getUser()->getId().'/Article#'.$this->getArticle()->getId();
	}

	public function __toString() {
		return $this->getQuantite()." x ".$this->getArticle()->getNom();
	}

	/**
	 * Set user
	 * @param User $user
	 * @return panier
	 */
	public function setUser(User $user, $doReverse = true) {
		if($doReverse == true) $user->addPanier($this, false);
		$this->user = $user;
		return $this;
	}

	/**
	 * Get user
	 * @return User 
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Set article
	 * @param article $article
	 * @return panier
	 */
	public function setArticle(article $article) {
		$this->article = $article;
	
		return $this;
	}

	/**
	 * Set position
	 * @param integer $position
	 * @return panier
	 */
	public function setPosition($position) {
		$this->position = $position;
	}

	/**
	 * Get position
	 * @return integer
	 */
	public function getPosition() {
		return $this->position;
	}

	/**
	 * Get prixtotal
	 * @return float
	 */
	public function getPrixtotal() {
		if($this->article->getPrix() !== null) return ($this->article->getPrix() * $this->quantite);
		else return 0;
	}

	/**
	 * Get getPrixtotaltxt
	 * @return string
	 */
	public function getPrixtotaltxt() {
		return number_format($this->getPrixtotal(), 2, ",", "");
	}

	/**
	 * Get article
	 * @return article 
	 */
	public function getArticle() {
		return $this->article;
	}

	/**
	 * Set quantite
	 * @param integer $quantite
	 * @return panier
	 */
	public function setQuantite($quantite) {
		$this->quantite = $quantite;
		return $this;
	}

	/**
	 * ajouteQuantite
	 * @param integer $quantite
	 * @return panier
	 */
	public function ajouteQuantite($quantite) {
		$this->quantite += $quantite;
		return $this;
	}

	/**
	 * retireQuantite
	 * @param integer $quantite
	 * @return panier
	 */
	public function retireQuantite($quantite) {
		$this->quantite -= $quantite;
		if($this->quantite < 0) $this->quantite = 0;
		return $this;
	}

	/**
	 * Get quantite
	 * @return integer 
	 */
	public function getQuantite() {
		return $this->quantite;
	}

	/**
	 * Set created
	 * @param DateTime $created
	 * @return panier
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
	 * @return panier
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



}
