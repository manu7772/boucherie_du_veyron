<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use site\adminBundle\Entity\baseEntity;
use site\adminBundle\Entity\item;

use \DateTime;

/**
 * tag
 *
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\tagRepository")
 * @ORM\Table(name="tag")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"nom"}, message="tag.existe")
 */
class tag extends baseEntity {

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
	 * @Assert\NotBlank(message = "entity.notblank.nom")
	 * @Assert\Length(
	 *      min = "2",
	 *      max = "30",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;



	public function __construct() {
		parent::__construct();
		// $this->items = new ArrayCollection();
		// $this->medias = new ArrayCollection();
	}

	/**
	 * Un élément par défaut dans la table est-il obligatoire ?
	 * @return boolean
	 */
	public function isDefaultNullable() {
		return true;
	}

	/**
	 * On peut attribuer plusieurs éléments par défaut ?
	 * true 		= illimité
	 * integer 		= nombre max. d'éléments par défaut
	 * false, 0, 1 	= un seul élément
	 * @return boolean
	 */
	public function isDefaultMultiple() {
		return 5;
	}


	// public function __call($name, $arguments = null) {
	// 	if(preg_match('#^get#', $name)) {
	// 		// Renvoi des items ou media selon type
	// 		$test = strtolower(preg_replace('#^get#', '', $name));
	// 		$items = new ArrayCollection();
	// 		foreach ($this->items as $item) {
	// 			if($item->getClassName() == $test) $items->add($item);
	// 		}
	// 		return $items;
	// 	}
	// 	return null;
	// }


	// public function getPagewebs() {
	// 	$items = new ArrayCollection();
	// 	foreach ($this->items as $item) {
	// 		if($item->getClassName() == 'pageweb') $items->add($item);
	// 	}
	// 	return $items;
	// }

	// public function getArticles() {
	// 	$items = new ArrayCollection();
	// 	foreach ($this->items as $item) {
	// 		if($item->getClassName() == 'article') $items->add($item);
	// 	}
	// 	return $items;
	// }

	// public function getFiches() {
	// 	$items = new ArrayCollection();
	// 	foreach ($this->items as $item) {
	// 		if($item->getClassName() == 'fiche') $items->add($item);
	// 	}
	// 	return $items;
	// }

}
