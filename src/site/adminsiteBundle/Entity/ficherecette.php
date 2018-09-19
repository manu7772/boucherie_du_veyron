<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use Labo\Bundle\AdminBundle\Entity\fiche;

use \DateTime;

/**
 * ficherecette
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\ficherecetteRepository")
 * @ORM\Table(name="ficherecette", options={"comment":"fiches recettes"})
 * @ORM\HasLifecycleCallbacks
 */
class ficherecette extends fiche {

	/**
	 * @var integer
	 * @ORM\Column(name="niveau", type="integer", nullable=false, unique=false)
	 */
	protected $niveau;

	/**
	 * @var string
	 * @ORM\Column(name="duree", type="string", length=32, nullable=false, unique=false)
	 */
	protected $duree;

	protected $listeNiveaux = array(
		1 => "niveaux.debutant",
		2 => "niveaux.intermediaire",
		3 => "niveaux.confirme",
		);

	protected $durees = array(
        15    =>  "15\"",
        30    =>  "30\"",
        45    =>  "45\"",
        60    =>  "1 h",
        90    =>  "1 h 30\"",
        120   =>  "2 h",
        150   =>  "2 h 30\"",
        180   =>  "3 h",
        210   =>  "3 h 30\"",
        240   =>  "4 h",
        270   =>  "4 h 30\"",
        300   =>  "5 h"
        );


	public function __construct() {
		parent::__construct();
		$this->setNiveau($this->getDefaultNiveau()); // Niveau par défaut
		$this->setDuree($this->getDefaultDuree());
		$this->setTypentite(2);
	}

	/**
	 * @ORM\PostLoad
	 */
	public function postLoad() {
		$this->listeTypentites = array(1 => 'boisson', 2 => 'recette');
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
	 * Get lsit of niveaux
	 * @return array 
	 */
	public function getListeNiveaux() {
		return $this->listeNiveaux;
	}
	/**
	 * Get default niveau
	 * @return integer 
	 */
	public function getDefaultNiveau() {
		return array_keys($this->getListeNiveaux())[0];
	}

	/**
	 * Get list of durees
	 * @return array 
	 */
	public function getDurees() {
		return $this->durees;
	}
	/**
	 * Get default duree (in minutes)
	 * @return integer
	 */
	public function getDefaultDuree() {
		return array_keys($this->getDurees())[0];
	}

	/**
	 * Set niveau
	 * @param string $niveau
	 * @return ficherecette
	 */
	public function setNiveau($niveau = null) {
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
	 * Get niveauText
	 * @return string 
	 */
	public function getNiveauText() {
		return $this->listeNiveaux[$this->niveau];
	}

	/**
	 * Set duree
	 * @param string $duree
	 * @return ficherecette
	 */
	public function setDuree($duree = null) {
		$this->duree = $duree;
		return $this;
	}

	/**
	 * Get duree
	 * @return string 
	 */
	public function getDuree() {
		return $this->duree;
	}

	/**
	 * Get duree as text
	 * @return string 
	 */
	public function getDureeTxt() {
		return $this->durees[$this->duree];
	}

}