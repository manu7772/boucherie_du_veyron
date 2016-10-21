<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use Labo\Bundle\AdminBundle\Entity\media;
use site\adminsiteBundle\Entity\article;

/**
 * pdf
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\pdfRepository")
 * @ORM\Table(name="pdf", options={"comment":"pdfs du site"})
 * @ORM\HasLifecycleCallbacks
 */
class pdf extends media {

	/**
	 * - INVERSE
	 * @ORM\OneToOne(targetEntity="site\adminsiteBundle\Entity\article", cascade={"all"}, mappedBy="pdf")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $article;

	public function __construct() {
		parent::__construct();
		$this->article = null;
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
	 * Set article - INVERSE
	 * @param article $article
	 * @return pdf
	 */
	public function setArticle(article $article = null) {
		$this->article = $article;
		return $this;
	}

	/**
	 * Get article - INVERSE
	 * @return article 
	 */
	public function getArticle() {
		return $this->article;
	}

}