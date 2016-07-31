<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use site\adminsiteBundle\Entity\fiche;

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

	protected $listeTypentites = array(
		1 => "vin",
		2 => "alcool",
		3 => "sans alcool",
		);

	protected $listeNotes = array(
		1 => "notes.bon",
		2 => "notes.tresbon",
		3 => "notes.excellent",
		);


	// NESTED VIRTUAL GROUPS
	// les noms doivent commencer par "$group_" et finir par "Parents" (pour les parents) ou "Childs" (pour les enfants)
	// et la partie variable doit comporter au moins 3 lettres
	// reconnaissance auto par : "#^(add|remove|get)(Group_).{3,}(Parent|Child)(s)?$#" (self::VIRTUALGROUPS_PARENTS_PATTERN et self::VIRTUALGROUPS_CHILDS_PATTERN)
	// categories
	// article_boisson
	protected $group_article_ficheboissonParents;
	protected $group_article_ficheboissonChilds;

	public function __construct() {
		parent::__construct();
		$this->setNote($this->getDefaultNote()); // Note par défaut
	}

	public function getNestedAttributesParameters() {
		$new = array(
			'article_ficheboisson' => array(
				'data-limit' => 0,
				'class' => array('article'),
				'required' => false,
				),
			);
		return array_merge(parent::getNestedAttributesParameters(), $new);
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