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

use site\adminBundle\Entity\article;

use \DateTime;

/**
 * reseau
 *
 * @ORM\Entity
 * @ORM\Table(name="reseau")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\reseauRepository")
 * @UniqueEntity(fields={"nom"}, message="Cette reseau est déjà enregistrée")
 * @ExclusionPolicy("all")
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

	/**
	 * @var array - INVERSE
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\article", mappedBy="reseaus")
	 * @ORM\JoinColumn(nullable=true, unique=false)
	 */
	protected $articles;


	public function __construct() {
		parent::__construct();
		$this->articles = new ArrayCollection();
	}


	/**
	 * Un élément par défaut dans la table est-il obligatoire ?
	 * @return boolean
	 */
	public function isDefaultNullable() {
		return true;
	}

	/**
	 * Get articles - INVERSE
	 * @return ArrayCollection
	 */
	public function getArticles() {
		return $this->articles;
	}

	/**
	 * Add article - INVERSE
	 * @param article $article
	 * @return reseau
	 */
	public function addArticle(article $article) {
		$this->articles->add($article);
		return $this;
	}

	/**
	 * Remove article - INVERSE
	 * @param article $article
	 * @return boolean
	 */
	public function removeArticle(article $article) {
		return $this->articles->removeElement($article);
	}


}
