<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use Labo\Bundle\AdminBundle\Entity\tier;

/**
 * reseau
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\reseauRepository")
 * @ORM\Table(name="reseau", options={"comment":"reseaux du site"})
 * @UniqueEntity(fields={"nom"}, message="Ce reseau est déjà enregistrée")
 * @ORM\HasLifecycleCallbacks
 */
class reseau extends tier {

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=100, nullable=false, unique=false)
	 * @Assert\NotBlank(message = "Vous devez nommer cet artible.")
	 * @Assert\Length(
	 *      min = "3",
	 *      max = "100",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;

	// public function __construct() {
	// 	parent::__construct();
	// }

	// public function getNestedAttributesParameters() {
	// 	$new = array(
	// 		'articles_reseaus' => array(
	// 			'data-limit' => 0,
	// 			'class' => array('reseau'),
	// 			'required' => false,
	// 			),
	// 		);
	// 	return array_merge(parent::getNestedAttributesParameters(), $new);
	// }

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

}