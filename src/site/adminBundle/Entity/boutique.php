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

use site\adminBundle\Entity\tier;

use site\adminBundle\Entity\media;
use site\adminBundle\Entity\adresse;

use \DateTime;

/**
 * boutique
 *
 * @ORM\Entity
 * @ORM\Table(name="boutique")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\boutiqueRepository")
 * @UniqueEntity(fields={"nom"}, message="Cette boutique est déjà enregistrée")
 * @ExclusionPolicy("all")
 */
class boutique extends tier {

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=100, nullable=false, unique=false)
	 * @Assert\NotBlank(message = "Vous devez remplir ce champ.")
	 * @Assert\Length(
	 *      min = "2",
	 *      max = "100",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;


	public function __construct() {
		parent::__construct();
	}

	// /**
	//  * Renvoie l'image principale
	//  * @return media
	//  */
	// public function getMainMedia() {
	// 	return $this->getLogo();
	// }


}
