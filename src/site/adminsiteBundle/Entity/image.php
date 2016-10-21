<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use Labo\Bundle\AdminBundle\services\aeImages;

use Labo\Bundle\AdminBundle\Entity\media;
use Labo\Bundle\AdminBundle\Entity\subentity;
use site\UserBundle\Entity\User;

use \DateTime;
use \Exception;

/**
 * image
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\imageRepository")
 * @ORM\Table(name="image", options={"comment":"images du site"})
 * @ORM\HasLifecycleCallbacks
 */
class image extends media {

	/**
	 * @ORM\ManyToOne(targetEntity="Labo\Bundle\AdminBundle\Entity\subentity")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $element;

	/**
	 * - INVERSE
	 * @ORM\OneToOne(targetEntity="site\UserBundle\Entity\User", mappedBy="avatar")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $userAvatar;

	/**
	 * @var string
	 * @ORM\Column(name="owner", type="string", nullable=true, unique=false)
	 */
	protected $owner;

	/**
	 * @var integer
	 * @ORM\Column(name="ratioIndex", type="integer", nullable=true, unique=false)
	 */
	protected $ratioIndex;

	/**
	 * @var integer
	 * @ORM\Column(name="width", type="integer", nullable=true, unique=false)
	 */
	protected $width;

	/**
	 * @var integer
	 * @ORM\Column(name="height", type="integer", nullable=true, unique=false)
	 */
	protected $height;

	protected $cropperInfo;

	// NESTED VIRTUAL GROUPS
	// les noms doivent commencer par "$group_" et finir par "Parents" (pour les parents) ou "Childs" (pour les enfants)
	// et la partie variable doit comporter au moins 3 lettres
	// reconnaissance auto par : "#^(add|remove|get)(Group_).{3,}(Parent|Child)(s)?$#" (self::VIRTUALGROUPS_PARENTS_PATTERN et self::VIRTUALGROUPS_CHILDS_PATTERN)

	public function __construct() {
		parent::__construct();
		$this->owner = null;
		$this->element = null;
		$this->userAvatar = null;
		$this->ratioIndex = 0;
		$this->width = 0;
		$this->height = 0;
		// $this->cropperInfo = null;
	}

    // public function getClassName(){
    //     return parent::CLASS_IMAGE;
    // }

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

	public function setCropperInfo($cropperInfo) {
		$this->cropperInfo = $cropperInfo;
	}

	public function getCropperInfo() {
		return $this->cropperInfo;
	}

	public function getShemaBase($format = null) {
		// $this->schemaBase = 'data:image/***;base64,';
		if(!is_string($format)) {
			$format = 'png';
			if($this->getFormat() != null) {
				$format = $this->getFormat();
			}
		}
		return preg_replace('#(__FORMAT__)#', $format, $this->schemaBase);
	}



	public function getImgThumbnail($x = 128, $y = 128, $mode = 'cut') {
		// return $this->getBinaryFile();
		return $this->getShemaBase().base64_encode($this->getThumbnail($x, $y, $mode));
	}

	public function getImg() {
		// return $this->getBinaryFile();
		return $this->getShemaBase().base64_encode($this->getBinaryFile());
	}

	/**
	 * Retourne un thumbnail du fichier / null si aucun
	 * @param integer $x - taille X
	 * @param integer $y - taille Y
	 * @param string $mode = 'cut'
	 * @return string
	 */
	public function getThumbnail($x = 128, $y = 128, $mode = 'cut', $format = null) {
		if(!in_array($format, $this->authorizedFormatsByType[self::CLASS_IMAGE])) $format = $this->getExtension();
		$aeImages = new aeImages();
		$aeImages->computeXandY($this->getWidth(), $this->getHeight(), $x, $y);
		return $aeImages->thumb_image($this->getBinaryFile(), $x, $y, $mode, true, $format);
	}


	/**
	 * Set owner
	 * @param owner $owner
	 * @return image
	 */
	public function setOwner($owner = null) {
		$this->owner = $owner;
		return $this;
	}

	/**
	 * Get owner
	 * @return string 
	 */
	public function getOwner() {
		return $this->owner;
	}

	/**
	 * Get owner entity
	 * @return string 
	 */
	public function getOwnerEntity() {
		return $this->owner != null ? explode(':', $this->owner)[0] : null ;
	}

	/**
	 * Get owner field
	 * @return string 
	 */
	public function getOwnerField() {
		return $this->owner != null ? explode(':', $this->owner)[1] : null ;
	}

	/**
	 * Set ratioIndex
	 * @param ratioIndex $ratioIndex
	 * @return image
	 */
	public function setRatioIndex($ratioIndex = null) {
		$this->ratioIndex = $ratioIndex;
		return $this;
	}

	/**
	 * Get ratioIndex
	 * @return string 
	 */
	public function getRatioIndex() {
		return $this->ratioIndex;
	}

	/**
	 * Set width
	 * @param width $width
	 * @return image
	 */
	public function setWidth($width = null) {
		$this->width = $width;
		return $this;
	}

	/**
	 * Get width
	 * @return string 
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * Set height
	 * @param height $height
	 * @return image
	 */
	public function setHeight($height = null) {
		$this->height = $height;
		return $this;
	}

	/**
	 * Get height
	 * @return string 
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * Get dimensions
	 * @return string
	 */
	public function getDimension() {
		if($this->getWidth()+$this->getHeight() == 0) return 'dimensionInconnue';
		return $this->getWidth().'x'.$this->getHeight().'px';
	}

	/**
	 * Set element
	 * @param subentity $element
	 * @return image
	 */
	public function setElement(subentity $element = null, $name = 'image') {
		$this->element = $element;
		if($element != null) {
			$this->setOwner($element->getShortName().':'.$name);
			// $this->setStatut($element->getStatut());
		} else {
			$this->setOwner(null);
		}
		return $this;
	}

	/**
	 * Get element
	 * @return subentity 
	 */
	public function getElement() {
		return $this->element;
	}

	/**
	 * Set userAvatar - INVERSE
	 * @param User $userAvatar
	 * @return image
	 */
	public function setUserAvatar(User $userAvatar = null) {
		$this->userAvatar = $userAvatar;
		if($userAvatar != null) {
			$this->setOwner($userAvatar->getShortName().':avatar');
			// $this->setStatut($userAvatar->getStatut());
		} else {
			$this->setOwner(null);
		}
		return $this;
	}

	/**
	 * Get userAvatar - INVERSE
	 * @return User 
	 */
	public function getUserAvatar() {
		return $this->userAvatar;
	}

}