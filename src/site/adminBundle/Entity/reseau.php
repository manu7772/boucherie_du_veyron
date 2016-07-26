<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use site\adminBundle\Entity\tier;

use site\adminBundle\Entity\article;

use \DateTime;

/**
 * reseau
 *
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\reseauRepository")
 * @ORM\Table(name="reseau")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"nom"}, message="Cet reseau est déjà enregistrée")
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

	// NESTED VIRTUAL GROUPS
	// les noms doivent commencer par "$group_" et finir par "Parents" (pour les parents) ou "Childs" (pour les enfants)
	// et la partie variable doit comporter au moins 3 lettres
	// reconnaissance auto par : "#^(add|remove|get)(Group_).{3,}(Parent|Child)(s)?$#" (self::VIRTUALGROUPS_PARENTS_PATTERN et self::VIRTUALGROUPS_CHILDS_PATTERN)
	protected $group_articles_reseausParents;
	protected $group_articles_reseausChilds;

	// public function __construct() {
	// 	parent::__construct();
	// }

	public function getNestedAttributesParameters() {
		$new = array(
			'articles_reseaus' => array(
				'data-limit' => 0,
				'class' => array('reseau'),
				'required' => false,
				),
			);
		return array_merge(parent::getNestedAttributesParameters(), $new);
	}

	/**
	 * Un élément par défaut dans la table est-il obligatoire ?
	 * @return boolean
	 */
	public function isDefaultNullable() {
		return true;
	}


}
