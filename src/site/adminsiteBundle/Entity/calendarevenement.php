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
 * calendarevenement
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\calendarevenementRepository")
 * @ORM\Table(name="calendarevenement", options={"comment":"Agendas des évènements du site"})
 * @ORM\HasLifecycleCallbacks
 */
class calendarevenement extends calendar {

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
	 * @return calendarevenement
	 */
	public function setUsercal(LaboUser $usercal = null) {
		if($usercal instanceOf LaboUser) throw new Exception("You can not set LaboUser as owner in calendarevenement entity!", 1);
			else $this->removeOwner();
		return $this;
	}

	/**
	 * set tier
	 * @param tier $tier = null
	 * @return calendarevenement
	 */
	public function setTier(tier $tier = null) {
		if($tier instanceOf tier) throw new Exception("You can not set tier as owner in calendarevenement entity!", 1);
			else $this->removeOwner();
		return $this;
	}

}






