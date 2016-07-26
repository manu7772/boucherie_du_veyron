<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use site\adminBundle\Entity\item;

use \DateTime;

/**
 * fiche
 *
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\ficheRepository")
 * @ORM\Table(name="fiche", options={"comment":"fiches : modes d'emplois, recettes, notices, bricolage, etc."})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="class_name", type="string")
 * @ORM\HasLifecycleCallbacks
 * 
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

	// protected $listeTypentites = array(
	// 	1 => "recette",
	// 	2 => "boisson",
	// 	);

	// NESTED VIRTUAL GROUPS
	// les noms doivent commencer par "$group_" et finir par "Parents" (pour les parents) ou "Childs" (pour les enfants)
	// et la partie variable doit comporter au moins 3 lettres
	// reconnaissance auto par : "#^(add|remove|get)(Group_).{3,}(Parent|Child)(s)?$#" (self::VIRTUALGROUPS_PARENTS_PATTERN et self::VIRTUALGROUPS_CHILDS_PATTERN)
	// categories
	protected $group_nestedsParents;
	protected $group_nestedsChilds;
	// article
	protected $group_fichesParents;
	protected $group_fichesChilds;

	public function __construct() {
		parent::__construct();
		$this->datePublication = new DateTime();
		$this->dateExpiration = null;
	}

	public function getNestedAttributesParameters() {
		$new = array(
			'fiches' => array(				// groupe fiches => group_fichesParents / group_imagesChilds
				'data-limit' => 10,				// nombre max. d'enfants / 0 = infini
				'class' => array('fiche'),	// classes acceptées (array) / null = toutes les classes de nested
				'required' => false,
				),
			'nesteds' => array(
				'data-limit' => 0,
				'class' => array('categorie'),
				'required' => false,
				),
			);
		return array_merge(parent::getNestedAttributesParameters(), $new);
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