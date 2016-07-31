<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use Labo\Bundle\AdminBundle\Entity\item;

/**
 * pageweb
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\pagewebRepository")
 * @UniqueEntity(fields={"nom"}, message="pageweb.existe")
 * 
 * @ORM\Table(name="pageweb", options={"comment":"pagewebs du site"})
 * @ORM\HasLifecycleCallbacks
 */
class pageweb extends item {

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=100, nullable=false, unique=true)
	 * @Assert\NotBlank(message = "entity.notblank.nom")
	 * @Assert\Length(
	 *      min = "3",
	 *      max = "25",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;

	/**
	 * @var string
	 * @ORM\Column(name="code", type="text", nullable=true, unique=false)
	 */
	protected $code;

	/**
	 * @var boolean
	 * @ORM\Column(name="extended", type="boolean", nullable=false, unique=false)
	 */
	protected $extended;

	/**
	 * @var string
	 * @ORM\Column(name="title", type="string", length=100, nullable=true, unique=false)
	 */
	protected $title;

	/**
	 * @var string
	 * @ORM\Column(name="titreh1", type="string", length=255, nullable=true, unique=false)
	 */
	protected $titreh1;

	/**
	 * @var string
	 * @ORM\Column(name="metadescription", type="text", nullable=true, unique=false)
	 */
	protected $metadescription;

	/**
	 * @var string
	 * @ORM\Column(name="modele", type="string", length=255, nullable=true, unique=false)
	 */
	protected $modele;

	// NESTED VIRTUAL GROUPS
	// les noms doivent commencer par "$group_" et finir par "Parents" (pour les parents) ou "Childs" (pour les enfants)
	// et la partie variable doit comporter au moins 3 lettres
	// reconnaissance auto par : "#^(add|remove|get)(Group_).{3,}(Parent|Child)(s)?$#" (self::VIRTUALGROUPS_PARENTS_PATTERN et self::VIRTUALGROUPS_CHILDS_PATTERN)
	protected $group_nestedsParents;
	protected $group_nestedsChilds;

	public function __construct() {
		parent::__construct();
		$this->code = null;
		$this->extended = false;
		$this->title = null;
		$this->titreh1 = null;
		$this->metadescription = null;
		$this->modele = null;
	}

	public function getNestedAttributesParameters() {
		$new = array(
			'nesteds' => array(
				'data-limit' => 0,
				'class' => array('categorie'),
				'required' => false,
				),
			);
		return array_merge(parent::getNestedAttributesParameters(), $new);
	}

	// /**
	//  * Renvoie l'image principale
	//  * @return image
	//  */
	// public function getMainMedia() {
	// 	return $this->getImage();
	// }

	/**
	 * Set code
	 * @param string $code
	 * @return pageweb
	 */
	public function setCode($code = null) {
		$this->code = $code;
		if(strip_tags(preg_replace('#([[:space:]])+#', '', $this->code)) == '') $this->code = null;
		return $this;
	}

	/**
	 * Get code
	 * @return string 
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Set extended
	 * @param boolean $extended
	 * @return pageweb
	 */
	public function setExtended($extended) {
		$this->extended = (boolean)$extended;
		return $this;
	}

	/**
	 * Get extended
	 * @return boolean 
	 */
	public function getExtended() {
		return $this->extended;
	}

	/**
	 * Set title
	 * @param string $title
	 * @return pageweb
	 */
	public function setTitle($title = null) {
		$this->title = $title;
		return $this;
	}

	/**
	 * Get title
	 * @return string 
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Set titreh1
	 * @param string $titreh1
	 * @return pageweb
	 */
	public function setTitreh1($titreh1 = null) {
		$this->titreh1 = $titreh1;
		return $this;
	}

	/**
	 * Get titreh1
	 * @return string 
	 */
	public function getTitreh1() {
		return $this->titreh1;
	}

	/**
	 * Set metadescription
	 * @param string $metadescription
	 * @return pageweb
	 */
	public function setMetadescription($metadescription = null) {
		$this->metadescription = $metadescription;
		return $this;
	}

	/**
	 * Get metadescription
	 * @return string 
	 */
	public function getMetadescription() {
		return $this->metadescription;
	}

	/**
	 * Set modele
	 * @param string $modele
	 * @return pageweb
	 */
	public function setModele($modele = null) {
		$this->modele = $modele;
		return $this;
	}

	/**
	 * Get modele
	 * @return string 
	 */
	public function getModele() {
		return $this->modele;
	}

	/**
	 * Get modelename
	 * @return string 
	 */
	public function getModelename() {
		$path = explode("/", $this->modele);		
		return preg_replace("#\.html\.twig$#", '', end($path));
	}

	/**
	 * Get template
	 * @return string 
	 */
	public function getTemplate() {
		$path = preg_split('#(src/|Resources/|views/|/)#', $this->modele);
		return implode(array_slice($path, 0, -2)).':'.$path[count($path)-2].':'.$path[count($path)-1];
	}

}