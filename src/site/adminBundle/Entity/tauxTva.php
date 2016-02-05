<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use \DateTime;

/**
 * tauxTva
 *
 * @ORM\Entity
 * @ORM\Table(name="tauxTva")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\tauxTvaRepository")
 * @UniqueEntity(fields={"nom"}, message="Ce taux de tva existe déjà")
 */
class tauxTva {

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=100, nullable=false, unique=true)
	 * @Assert\NotBlank(message = "Vous devez remplir ce champ.")
	 * @Assert\Length(
	 *      min = "3",
	 *      max = "100",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;

	protected $nomlong;

	/**
	 * @var string
	 * @ORM\Column(name="descriptif", type="text", nullable=true)
	 */
	protected $descriptif;

	/**
	 * @var float
	 * @ORM\Column(name="taux", type="float", nullable=false, unique=true)
	 */
	protected $taux;

	/**
	 * @var DateTime
	 * @ORM\Column(name="created", type="datetime", nullable=false)
	 */
	protected $dateCreation;

	/**
	 * @var DateTime
	 * @ORM\Column(name="updated", type="datetime", nullable=true)
	 */
	protected $dateMaj;


	public function __construct() {
		$this->dateCreation = new DateTime();
		$this->dateMaj = null;
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
	 * @return tauxTva
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
	 * Get nom long
	 * @return string 
	 */
	public function getNomlong() {
		return $this->getTaux(). "% (".$this->getNom().")";
	}

	/**
	 * Set descriptif
	 * @param string $descriptif
	 * @return tauxTva
	 */
	public function setDescriptif($descriptif = null) {
		$this->descriptif = $descriptif;
		return $this;
	}

	/**
	 * Get descriptif
	 * @return string 
	 */
	public function getDescriptif() {
		return $this->descriptif;
	}

	/**
	 * Set taux
	 * @param float $taux
	 * @return tauxTva
	 */
	public function setTaux($taux) {
		$this->taux = floatval($taux);
		return $this;
	}

	/**
	 * Get taux
	 * @return float 
	 */
	public function getTaux() {
		return $this->taux;
	}

	/**
	 * Set dateCreation
	 *
	 * @param DateTime $dateCreation
	 * @return tauxTva
	 */
	public function setDateCreation(DateTime $dateCreation) {
		$this->dateCreation = $dateCreation;
	
		return $this;
	}

	/**
	 * Get dateCreation
	 *
	 * @return DateTime 
	 */
	public function getDateCreation() {
		return $this->dateCreation;
	}

	/**
	 * @ORM\PreUpdate
	 */
	public function updateDateMaj() {
		$this->setDateMaj(new DateTime());
	}

	/**
	 * Set dateMaj
	 *
	 * @param DateTime $dateMaj
	 * @return tauxTva
	 */
	public function setDateMaj(DateTime $dateMaj) {
		$this->dateMaj = $dateMaj;
	
		return $this;
	}

	/**
	 * Get dateMaj
	 *
	 * @return DateTime 
	 */
	public function getDateMaj() {
		return $this->dateMaj;
	}

}
