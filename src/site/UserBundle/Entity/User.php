<?php
 
namespace site\UserBundle\Entity;

use Labo\Bundle\AdminBundle\Entity\LaboUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Yaml\Parser;
// JMS Serializer
// use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
// use JMS\Serializer\Annotation\MaxDepth;
use JMS\Serializer\Annotation\Groups;

use \DateTime;
use \ReflectionClass;

/**
 * @ORM\Entity(repositoryClass="site\UserBundle\Entity\UserRepository")
 * @ORM\Table(name="User")
 */
class User extends LaboUser {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @Expose
	 * @Groups({"complete", "ajaxlive", "facture"})
	 */
	protected $id;



}