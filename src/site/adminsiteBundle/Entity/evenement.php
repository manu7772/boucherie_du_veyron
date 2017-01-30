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

use Labo\Bundle\AdminBundle\Entity\baseevenement;
use site\adminsiteBundle\Entity\site;

use \DateTime;

/**
 * evenement
 *
 * @ExclusionPolicy("all")
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\evenementRepository")
 * @ORM\Table(name="evenement", options={"comment":"evenements du site"})
 * @ORM\HasLifecycleCallbacks
 */
class evenement extends baseevenement {

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=128, nullable=false, unique=false)
	 * @Assert\NotBlank(message = "Vous devez remplir ce champ.")
	 * @Assert\Length(
	 *      min = "2",
	 *      max = "128",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 * @Expose
	 * @Groups({"complete", "facture"})
	 */
	protected $nom;

	/**
	 * - INVERSE
	 * @ORM\ManyToMany(targetEntity="site\adminsiteBundle\Entity\site", mappedBy="evenements")
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
		return 6;
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
	 * @return evenement
	 */
	public function addSite(site $site) {
		$this->sites->add($site);
		$site->addEvenement($this);
		return $this;
	}

	/**
	 * Remove site
	 * @param site $site
	 * @return boolean
	 */
	public function removeSite(site $site) {
		$r = $this->sites->removeElement($site);
		$site->removeEvenement($this);
		return $r;
	}

	/**
	 * Get sites
	 * @return ArrayCollection
	 */
	public function getSites() {
		return $this->sites;
	}

}