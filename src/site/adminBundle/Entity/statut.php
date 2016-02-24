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
use site\adminBundle\Entity\baseEntity;

// use site\adminBundle\Entity\item;
use site\services\aetools;
use \DateTime;

/**
 * statut
 *
 * @ORM\Entity
 * @ORM\Table(name="statut")
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\statutRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"nom"}, message="statut.existe")
 */
class statut extends baseEntity {

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=100, nullable=false, unique=true)
	 * @Assert\NotBlank(message = "Vous devez remplir ce champ.")
	 * @Assert\Length(
	 *      min = "3",
	 *      max = "100",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;

	/**
	 * @var string
	 * @ORM\Column(name="descriptif", type="text", nullable=true, unique=false)
	 */
	protected $descriptif;

	/**
	 * @var string
	 * @ORM\Column(name="niveau", type="string", length=32, nullable=false, unique=false)
	 */
	protected $niveau;

	/**
	 * @var array
	 * @ORM\Column(name="bundles", type="array", nullable=true, unique=false)
	 */
	protected $bundles;

	/**
	 * @var string
	 * @ORM\Column(name="couleur", type="string", length=24, nullable=false, unique=false)
	 */
	protected $couleur;

	// protected $couleurs;
	protected $bundle_choices;
	protected $role_choices;

	public function __construct() {
		parent::__construct();
		$aetools = new aetools();
		$this->role_choices = $aetools->getListOfRoles();
		$this->bundle_choices = array_flip($aetools->getBundlesList());
		$this->niveau = reset($this->role_choices);
		$this->couleur = "transparent";
		$this->bundles = array();
		foreach ($this->bundle_choices as $bundle) {
			$this->addBundle($bundle);
		}
		// $this->couleurs = array('active', 'success', 'warning', 'danger', 'info');
	}

    // public function getClassName(){
    //     return parent::CLASS_STATUT;
    // }

	/**
	 * Set niveau
	 * @param string $niveau
	 * @return statut
	 */
	public function setNiveau($niveau) {
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
	 * Add bundle
	 * @param string $bundle
	 * @return statut
	 */
	public function addBundle($bundle) {
		if(!is_array($this->bundles)) $this->bundles = array();
		if(is_string($bundle)) $bundle = array($bundle);
		foreach ($bundle as $bun) {
			if(!in_array($bun, $this->bundles)) $this->bundles[] = $bun;
		}
		return $this;
	}

	/**
	 * Remove bundle
	 * @param string $bundle
	 * @return boolean
	 */
	public function removeBundle($bundle) {
		if(!is_array($this->bundles)) $this->bundles = array();
		$r = false;
		if(in_array($bundle, $this->bundles)) {
			foreach ($this->bundles as $key => $bun) {
				if($bun == $bundle) {
					unset($this->bundles[$key]);
					$r = true;
				}
			}
		}
		return $r;
	}

	/**
	 * Set bundles
	 * @param array $bundles
	 * @return statut
	 */
	public function setBundles($bundles) {
		if(is_array($bundles)) $this->bundles = $bundles;
			else $this->bundles = array();
		return $this;
	}

	/**
	 * Get bundles
	 * @return array 
	 */
	public function getBundles() {
		if(!is_array($this->bundles)) $this->bundles = array();
		return $this->bundles;
	}

	/**
	 * Set descriptif
	 * @param string $descriptif
	 * @return statut
	 */
	public function setDescriptif($descriptif = null) {
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
	 * Set couleur
	 * @param string $couleur
	 * @return statut
	 */
	public function setCouleur($couleur) {
		// if(in_array($couleur, $this->couleurs)) $this->couleur = $couleur;
		// 	else $this->couleur = reset($this->couleurs);
		$this->couleur = $couleur;
		return $this;
	}

	/**
	 * Get couleur
	 * @return string 
	 */
	public function getCouleur() {
		return $this->couleur;
	}

	// /**
	//  * Get couleurs
	//  * @return array 
	//  */
	// public function getCouleurs() {
	// 	return $this->couleurs;
	// }

	/**
	 * Get role choices
	 * @return array 
	 */
	public function getRoleChoices() {
		return $this->role_choices;
	}

	/**
	 * Get bundle choices
	 * @return array 
	 */
	public function getBundleChoices() {
		return $this->bundle_choices;
	}

}
