<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;
use site\adminBundle\Entity\baseEntity;

use \DateTime;

/**
 * tauxTva
 *
 * @ORM\Entity
 * @ORM\Table(name="tauxTva")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\tauxTvaRepository")
 * @UniqueEntity(fields={"nom"}, message="Ce taux de tva existe déjà")
 */
class tauxTva extends baseEntity {

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


	public function __construct() {
		parent::__construct();
	}

    // public function getClassName(){
    //     return parent::CLASS_TAUXTVA;
    // }

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


}
