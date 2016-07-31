<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

use Labo\Bundle\AdminBundle\Entity\panier as aepanier;

/**
 * panier
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\panierRepository")
 * @ORM\Table(name="panier", options={"comment":"paniers du site"})
 * @ORM\HasLifecycleCallbacks
 */
class panier extends aepanier {

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="Labo\Bundle\AdminBundle\Entity\LaboUser", inversedBy="paniers")
	 * @ORM\JoinColumn(nullable=false, unique=false)
	 * @Gedmo\SortableGroup
	 */
	protected $user;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="site\adminsiteBundle\Entity\article")
	 * @ORM\JoinColumn(nullable=false, unique=false)
	 */
	protected $article;

	/**
	 * @ORM\Column(type="integer")
	 * https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/sortable.md
	 * @Gedmo\SortablePosition
	 */
	private $position;

}