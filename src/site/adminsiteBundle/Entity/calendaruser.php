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
use Labo\Bundle\AdminBundle\Entity\baseevenement;
use Labo\Bundle\AdminBundle\Entity\LaboUser;

use \ReflectionClass;
use \DateTime;
use \Exception;

/**
 * calendaruser
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\calendaruserRepository")
 * @ORM\Table(name="calendaruser", options={"comment":"Agendas des LaboUsers du site"})
 * @ORM\HasLifecycleCallbacks
 */
class calendaruser extends calendar {

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
	 * set tier
	 * @param tier $tier = null
	 * @return calendaruser
	 */
	public function setTier(tier $tier = null) {
		if($usercal instanceOf LaboUser) throw new Exception("You can not set LaboUser as owner in calendaruser entity!", 1);
			else $this->removeOwner();
		return $this;
	}

	/**
	 * set baseevenement
	 * @param baseevenement $evenement = null
	 * @return calendaruser
	 */
	public function setEvenement(baseevenement $evenement = null) {
		if($evenement instanceOf baseevenement) throw new Exception("You can not set baseevenement as ownerement in calendaruser entity!", 1);
			else $this->removeOwner();
		return $this;
	}

}






