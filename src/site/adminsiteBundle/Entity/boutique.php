<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// JMS Serializer
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\MaxDepth;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;

use Labo\Bundle\AdminBundle\Entity\tier;
use site\adminsiteBundle\Entity\site;

use \DateTime;

/**
 * boutique
 *
 * @ExclusionPolicy("all")
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\boutiqueRepository")
 * @ORM\Table(name="boutique", options={"comment":"boutiques du site"})
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"nom"}, message="Cette boutique est déjà enregistrée")
 */
class boutique extends tier {

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
	 * @Expose
	 * @Groups({"complete", "facture"})
	 */
	protected $nom;

	/**
	 * - INVERSE
	 * @ORM\ManyToMany(targetEntity="site\adminsiteBundle\Entity\site", mappedBy="boutiques")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 * @Expose
	 * @Groups({"complete"})
	 */
	protected $sites;


	public function __construct() {
		parent::__construct();
		$this->sites = new ArrayCollection();
	}

	// /**
	//  * Renvoie l'image principale
	//  * @return media
	//  */
	// public function getMainMedia() {
	// 	return $this->getLogo();
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

	/**
	 * Set sites
	 * @param arrayCollection $sites
	 * @return subentity
	 */
	public function setSites(ArrayCollection $sites) {
		// $this->sites->clear();
		// incorporation avec "add" et "remove" au cas où il y aurait des opérations (inverse notamment)
		foreach ($this->getSites() as $site) if(!$sites->contains($site)) $this->removeSite($site); // remove
		foreach ($sites as $site) $this->addSite($site); // add
		return $this;
	}

	/**
	 * Add site
	 * @param site $site
	 * @return boutique
	 */
	public function addSite(site $site) {
		$this->sites->add($site);
		return $this;
	}

	/**
	 * Remove site
	 * @param site $site
	 * @return boolean
	 */
	public function removeSite(site $site) {
		return $this->sites->removeElement($site);
	}

	/**
	 * Get sites
	 * @return ArrayCollection
	 */
	public function getSites() {
		return $this->sites;
	}

}