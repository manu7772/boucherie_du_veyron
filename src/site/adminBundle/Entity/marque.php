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
 * marque
 *
 * @ORM\Entity
 * @ORM\Table(name="marque")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\marqueRepository")
 * @UniqueEntity(fields={"nom"}, message="Cette marque est déjà enregistrée")
 * @ExclusionPolicy("all")
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
	 * @ORM\OneToMany(targetEntity="site\adminBundle\Entity\article", mappedBy="marque", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $articles;


	public function __construct() {
		parent::__construct();
		$this->articles = new ArrayCollection();
	}

	// public function memOldValues($addedfields = null) {
	// 	$fields = array('articles');
	// 	if(count($addedfields) > 0 && is_array($addedfields)) $fields = array_unique(array_merge($fields, $addedfields));
	// 	parent::memOldValues($fields);
	// 	return $this;
	// }

	/**
	 * Un élément par défaut dans la table est-il obligatoire ?
	 * @return boolean
	 */
	public function isDefaultNullable() {
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
