<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use Labo\Bundle\AdminBundle\Entity\message as aemessage;

/**
 * message
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\messageRepository")
 * @ORM\Table(name="message", options={"comment":"messages du site"})
 * @ORM\HasLifecycleCallbacks
 */
class message extends aemessage {

	/**
	 * @var integer
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	public function getId() {
		return $this->id;
	}

}