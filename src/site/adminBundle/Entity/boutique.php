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

use site\adminBundle\Entity\tier;

use site\adminBundle\Entity\media;
use site\adminBundle\Entity\adresse;
use site\adminBundle\Entity\site;

use \DateTime;

/**
 * boutique
 *
 * @ORM\Entity
 * @ORM\Table(name="boutique", options={"comment":"boutiques du site"})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\boutiqueRepository")
 * @UniqueEntity(fields={"nom"}, message="Cette boutique est déjà enregistrée")
 * @ExclusionPolicy("all")
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
	 */
	protected $nom;

	/**
	 * - INVERSE
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\site", mappedBy="boutiques")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $sites;


	public function __construct() {
		parent::__construct();
		$this->sites = new ArrayCollection();
	}

	// public function memOldValues($addedfields = null) {
	// 	$fields = array('sites');
	// 	if(count($addedfields) > 0 && is_array($addedfields)) $fields = array_unique(array_merge($fields, $addedfields));
	// 	parent::memOldValues($fields);
	// 	return $this;
	// }

	// /**
	//  * Renvoie l'image principale
	//  * @return media
	//  */
	// public function getMainMedia() {
	// 	return $this->getLogo();
	// }

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
