<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

use Labo\Bundle\AdminBundle\Entity\baseEntity;

/**
 * tauxTva
 *
 * @ExclusionPolicy("all")
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\tauxTvaRepository")
 * @ORM\Table(name="tauxTva", options={"comment":"taux de TVA du site"})
 * @UniqueEntity(fields={"nom"}, message="Ce taux de tva existe déjà")
 * @ORM\HasLifecycleCallbacks
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
	 * @Expose
	 * @Groups({"complete", "facture"})
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
	 * @Expose
	 * @Groups({"complete", "facture"})
	 */
	protected $taux;


	public function __construct() {
		parent::__construct();
	}

	/**
	 * Un élément par défaut dans la table est-il optionnel ?
	 * @return boolean
	 */
	public function isDefaultNullable() {
		return false;
	}

	/**
	 * Peut'on attribuer plusieurs éléments par défaut ?
	 * true 		= illimité
	 * integer 		= nombre max. d'éléments par défaut
	 * false, 0, 1 	= un seul élément
	 * @return boolean
	 */
	public function isDefaultMultiple() {
		return false;
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

}