<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use site\adminBundle\Entity\nested;

use site\adminBundle\Entity\categorie;
// use site\UserBundle\Entity\User;

use \DateTime;
use \Exception;

/**
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\itemRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="class_name", type="string")
 * @ORM\HasLifecycleCallbacks
 */
abstract class item extends nested {

    // const CLASS_ARTICLE		= "article";
    // const CLASS_FICHE		= "fiche";
    // const CLASS_PAGEWEB		= "pageweb";

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;


	// public function __construct() {
	// 	parent::__construct();
	// }

	// public function memOldValues($addedfields = null) {
	// 	$fields = array();
	// 	if(count($addedfields) > 0 && is_array($addedfields)) $fields = array_unique(array_merge($fields, $addedfields));
	// 	parent::memOldValues($fields);
	// 	return $this;
	// }



}