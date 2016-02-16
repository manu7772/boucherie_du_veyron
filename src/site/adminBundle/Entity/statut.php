<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;
use site\adminBundle\Entity\baseEntity;

// use site\adminBundle\Entity\item;

use \DateTime;

/**
 * statut
 *
 * @ORM\Entity
 * @ORM\Table(name="statut")
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\statutRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"nom"}, message="statut.existe")
 */
class statut extends baseEntity {

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
	 * @ORM\Column(name="descriptif", type="text", nullable=true, unique=false)
	 */
	protected $descriptif;

	/**
	 * @var string
	 * @ORM\Column(name="niveau", type="string", length=32, nullable=false, unique=false)
	 */
	protected $niveau;


	public function __construct() {
		parent::__construct();
		$this->niveau = 'ROLE_USER';
	}


    // public function getClassName(){
    //     return parent::CLASS_STATUT;
    // }

	/**
	 * Set niveau
	 * @param string $niveau
	 * @return statut
	 */
	public function setNiveau($niveau) {
		$this->niveau = $niveau;
		return $this;
	}

	/**
	 * Get niveau
	 * @return string 
	 */
	public function getNiveau() {
		return $this->niveau;
	}

	/**
	 * Set descriptif
	 * @param string $descriptif
	 * @return statut
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

}
