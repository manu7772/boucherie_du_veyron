<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use Labo\Bundle\AdminBundle\Entity\tier;
use site\adminsiteBundle\Entity\article;

/**
 * marque
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\marqueRepository")
 * @ORM\Table(name="marque", options={"comment":"marques du site"})
 * @UniqueEntity(fields={"nom"}, message="Cette marque est déjà enregistrée")
 * @ORM\HasLifecycleCallbacks
 */
class marque extends tier {

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

	/**
	 * - INVERSE
	 * @ORM\OneToMany(targetEntity="site\adminsiteBundle\Entity\article", mappedBy="marque", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $articles;


	public function __construct() {
		parent::__construct();
		$this->articles = new ArrayCollection();
	}

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

	/**
	 * Set articles
	 * @param arrayCollection $articles
	 * @return subentity
	 */
	public function setArticles(ArrayCollection $articles) {
		// $this->articles->clear();
		// incorporation avec "add" et "remove" au cas où il y aurait des opérations (inverse notamment)
		foreach ($this->getArticles() as $article) if(!$articles->contains($article)) $this->removeArticle($article); // remove
		foreach ($articles as $article) $this->addArticle($article); // add
		return $this;
	}

	/**
	 * Add article - INVERSE
	 * @param article $article
	 * @return marque
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

	/**
	 * Get articles - INVERSE
	 * @return ArrayCollection
	 */
	public function getArticles() {
		return $this->articles;
	}

}