<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\MaxDepth;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;

use Labo\Bundle\AdminBundle\Entity\calendar;
use Labo\Bundle\AdminBundle\Entity\tier;
use Labo\Bundle\AdminBundle\Entity\LaboUser;

use \ReflectionClass;
use \DateTime;
use \Exception;

/**
 * calendartier
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\siteRepository")
 * @ORM\Table(name="calendartier", options={"comment":"Agendas tiers du site"})
 * @ORM\HasLifecycleCallbacks
 */
class calendartier extends calendar {

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @Expose
	 * @Groups({"complete", "fullcalendar"})
	 * @Accessor(getter="getIdForFC")
	 */
	protected $id;

	/**
	 * set user
	 * @param LaboUser $usercal = null
	 */
	public function setUsercal(LaboUser $usercal = null) {
		// throw new Exception("You can not put user in calendartier entity!", 1);
		return $this;
	}

}






