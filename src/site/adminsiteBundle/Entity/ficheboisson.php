<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use Labo\Bundle\AdminBundle\Entity\fiche;

use \DateTime;

/**
 * ficheboisson
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\ficheboissonRepository")
 * @ORM\Table(name="ficheboisson", options={"comment":"fiches boissons"})
 * @ORM\HasLifecycleCallbacks
 */
class ficheboisson extends fiche {

	/**
	 * @var integer
	 * @ORM\Column(name="note", type="integer", nullable=false, unique=false)
	 */
	protected $note;

	// protected $listeTypentites = array(
	// 	1 => "vin",
	// 	2 => "alcool",
	// 	3 => "sans alcool",
	// 	);

	protected $listeNotes = array(
		1 => "notes.bon",
		2 => "notes.tresbon",
		3 => "notes.excellent",
		);


	public function __construct() {
		parent::__construct();
		$this->setNote($this->getDefaultNote()); // Note par défaut
		$this->setTypentite(1);
	}

	/**
	 * @ORM\PostLoad
	 */
	public function postLoad() { // ??????
		// $this->listeTypentites = array(1 => 'boisson', 2 => 'recette');
		$this->listeTypentites = array(
			1 => "vin",
			2 => "alcool",
			3 => "sans alcool",
		);
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
	 * Get lsit of notes
	 * @return array 
	 */
	public function getListeNotes() {
		return $this->listeNotes;
	}
	/**
	 * Get default note
	 * @return integer 
	 */
	public function getDefaultNote() {
		return array_keys($this->getListeNotes())[0];
	}

	/**
	 * Set note
	 * @param string $note
	 * @return ficheboisson
	 */
	public function setNote($note = null) {
		$this->note = $note;
		return $this;
	}

	/**
	 * Get note
	 * @return string 
	 */
	public function getNote() {
		return $this->note;
	}

	/**
	 * Get noteText
	 * @return string 
	 */
	public function getNoteText() {
		return isset($this->listeNotes[$this->note]) ? $this->listeNotes[$this->note] : null;
	}

}