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

use site\adminBundle\services\aeImages;

use site\adminBundle\Entity\media;
use site\adminBundle\Entity\item;
use site\UserBundle\Entity\User;

use \DateTime;
use \Exception;

/**
 * pdf
 *
 * @ORM\Entity
 * @ORM\Table(name="pdf")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\pdfRepository")
 * @ExclusionPolicy("all")
 */
class pdf extends media {


	/**
	 * - INVERSE
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\article", cascade={"all"}, mappedBy="pdf")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $article;

	public function __construct() {
		parent::__construct();
		$this->article = null;
	}

	// public function memOldValues($addedfields = null) {
	// 	$fields = array('article');
	// 	if(count($addedfields) > 0 && is_array($addedfields)) $fields = array_unique(array_merge($fields, $addedfields));
	// 	parent::memOldValues($fields);
	// 	return $this;
	// }

    // public function getClassName(){
    //     return parent::CLASS_IMAGE;
    // }


	/**
	 * Set article - INVERSE
	 * @param User $article
	 * @return media
	 */
	public function setArticle(User $article = null) {
		$this->article = $article;
		return $this;
	}

	/**
	 * Get article - INVERSE
	 * @return User 
	 */
	public function getArticle() {
		return $this->article;
	}





}
