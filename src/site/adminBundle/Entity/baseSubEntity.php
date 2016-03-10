<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\baseEntity;

use \DateTime;
use \Exception;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class baseSubEntity extends baseEntity {


	/**
	 * @var string
	 * @ORM\Column(name="descriptif", type="text", nullable=true, unique=false)
	 */
	protected $descriptif;

	/**
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\statut")
	 * @ORM\JoinColumn(nullable=false, unique=false)
	 */
	protected $statut;

	/**
	 * @var array
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\tag", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $tags;


	public function __construct() {
		parent::__construct();
		$this->descriptif = null;
		$this->statut = null;
		$this->tags = new ArrayCollection();
	}

	/**
	 * Set descriptif
	 * @param string $descriptif
	 * @return baseSubEntity
	 */
	public function setDescriptif($descriptif) {
		$this->descriptif = $descriptif;
		return $this;
	}

	/**
	 * Get descriptif
	 * @return string 
	 */
	public function getDescriptif() {
		return $this->descriptif;
	}

	/**
	 * Set statut
	 * @param statut $statut
	 * @return baseSubEntity
	 */
	public function setStatut(statut $statut) {
		$this->statut = $statut;
		return $this;
	}

	/**
	 * Get statut
	 * @return statut 
	 */
	public function getStatut() {
		return $this->statut;
	}

	/**
	 * Add tag
	 * @param tag $tag
	 * @return baseSubEntity
	 */
	public function addTag(tag $tag) {
		$this->tags->add($tag);
		return $this;
	}

	/**
	 * Remove tag
	 * @param tag $tag
	 * @return boolean
	 */
	public function removeTag(tag $tag) {
		return $this->tags->removeElement($tag);
	}

	/**
	 * Get tags
	 * @return ArrayCollection 
	 */
	public function getTags() {
		return $this->tags;
	}



}