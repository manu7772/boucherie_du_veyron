<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
// JMS Serializer
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\MaxDepth;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;

use Labo\Bundle\AdminBundle\Entity\panier as aeBasePanier;

/**
 * panier
 *
 * @ExclusionPolicy("all")
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\panierRepository")
 * @ORM\Table(name="panier", options={"comment":"paniers du site"})
 * @ORM\HasLifecycleCallbacks
 */
class panier extends aeBasePanier {


	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="site\adminsiteBundle\Entity\article")
	 * @ORM\JoinColumn(nullable=false, unique=false)
	 * @Expose
	 * @Groups({"complete", "ajaxlive", "facture"})
	 * @MaxDepth(2)
	 */
	protected $article;

	/**
	 * @ORM\Id
	 * @var integer
	 * @ORM\Column(name="volume", type="integer", nullable=false, unique=false)
	 * @Expose
	 * @Groups({"complete", "ajaxlive", "facture"})
	 */
	protected $volume;

	/**
	 * @ORM\Id
	 * @var string
	 * @ORM\Column(name="unit", type="string", nullable=false, unique=false)
	 * @Expose
	 * @Groups({"complete", "ajaxlive", "facture"})
	 */
	protected $unit;

	public function __construct() {
		parent::__construct();
		$this->volume = null;
		$this->unit = null;
	}


	public function getArrayForId() {
		return array_merge(
			parent::getArrayForId(),
			array(
				'unit' => $this->getUnit(),
				'volume' => (integer)$this->getVolume(),
			)
		);
	}

	public function calculPrix($prix) {
		return parent::calculPrix($prix) * $this->getVolume();
	}

	/**
	 * Set volume
	 * @param $volume
	 * @return panier
	 */
	public function setVolume($volume) {
		$this->volume = $volume;
	}

	/**
	 * Set volume
	 * @return integer
	 */
	public function getVolume() {
		return $this->volume;
	}

	/**
	 * Set unit
	 * @param $unit
	 * @return panier
	 */
	public function setUnit($unit) {
		$this->unit = $unit;
	}

	/**
	 * Set unit
	 * @return string
	 */
	public function getUnit() {
		return $this->unit;
	}


}