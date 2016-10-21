<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use Labo\Bundle\AdminBundle\Entity\item;

use \DateTime;

/**
 * fiche
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\ficheRepository")
 * @ORM\HasLifecycleCallbacks
 */
abstract class fiche extends item {

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=100, nullable=false, unique=false)
	 * @Assert\NotBlank(message = "Vous devez nommer cette fiche")
	 * @Assert\Length(
	 *      min = "3",
	 *      max = "100",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;

	/**
	 * @var DateTime
	 * @ORM\Column(name="datePublication", type="datetime", nullable=false)
	 */
	protected $datePublication;

	/**
	 * @var DateTime
	 * @ORM\Column(name="dateExpiration", type="datetime", nullable=true)
	 */
	protected $dateExpiration;

	/**
	 * @var string
	 * @ORM\Column(name="accroche", type="string", length=200, nullable=true, unique=false)
	 * @Assert\Length(
	 *      max = "200",
	 *      maxMessage = "L'accroche doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $accroche;


	public function __construct() {
		parent::__construct();
		$this->datePublication = new DateTime();
		$this->dateExpiration = null;
		$this->postLoad();
	}

	/**
	 * Un élément par défaut dans la table est-il optionnel ?
	 * @return boolean
	 */
	public function isDefaultNullable() {
		return true;
	}

	/**
	 * Peut'on attribuer plusieurs éléments par défaut ?
	 * true 		= illimité
	 * integer 		= nombre max. d'éléments par défaut
	 * false, 0, 1 	= un seul élément
	 * @return boolean
	 */
	public function isDefaultMultiple() {
		return true;
	}

	/**
	 * @ORM\PostLoad
	 */
	public function postLoad() {
		$this->listeTypentites = array(1 => 'boisson', 2 => 'recette');
	}


	/**
	 * Set datePublication
	 * @param DateTime $datePublication
	 * @return fiche
	 */
	public function setDatePublication($datePublication = null) {
		if(($datePublication < $this->created) || ($datePublication === null)) $datePublication = $this->created;
		$this->datePublication = $datePublication;
		return $this;
	}

	/**
	 * Get datePublication
	 * @return DateTime 
	 */
	public function getDatePublication() {
		return $this->datePublication;
	}

	/**
	 * Set dateExpiration
	 * @param DateTime $dateExpiration
	 * @return fiche
	 */
	public function setDateExpiration($dateExpiration = null) {
		if(($dateExpiration < $this->created) && ($dateExpiration !== null)) $dateExpiration = null;
		$this->dateExpiration = $dateExpiration;
		return $this;
	}

	/**
	 * Get dateExpiration
	 * @return DateTime 
	 */
	public function getDateExpiration() {
		return $this->dateExpiration;
	}

	/**
	 * Set accroche
	 * @param string $accroche
	 * @return fiche
	 */
	public function setAccroche($accroche) {
		$this->accroche = $accroche;
		return $this;
	}

	/**
	 * Get accroche
	 * @return string 
	 */
	public function getAccroche() {
		return $this->accroche;
	}

}