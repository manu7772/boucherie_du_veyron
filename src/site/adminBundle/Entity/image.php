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

use site\services\aeImages;

use site\adminBundle\Entity\media;
use site\adminBundle\Entity\tier;
use site\adminBundle\Entity\item;
use site\UserBundle\Entity\User;

use \DateTime;
use \Exception;

/**
 * image
 *
 * @ORM\Entity
 * @ORM\Table(name="image")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\imageRepository")
 * @ExclusionPolicy("all")
 */
class image extends media {


	/**
	 * - INVERSE
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\item", cascade={"all"}, mappedBy="image")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $item;

	/**
	 * - INVERSE
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\tier", cascade={"all"}, mappedBy="image")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $tier;

	/**
	 * - INVERSE
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\tier", cascade={"all"}, mappedBy="logo")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $logoTier;

	/**
	 * - INVERSE
	 * @ORM\OneToOne(targetEntity="site\UserBundle\Entity\User", cascade={"all"}, mappedBy="avatar")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $userAvatar;

	public function __construct() {
		parent::__construct();
		$this->item = null;
		$this->tier = null;
		$this->logoTier = null;
		$this->userAvatar = null;
	}

    // public function getClassName(){
    //     return parent::CLASS_IMAGE;
    // }



	public function getShemaBase($format = null) {
		// $this->schemaBase = 'data:image/***;base64,';
		if(!is_string($format)) {
			$format = 'png';
			if($this->getFormat() != null) {
				$format = $this->getFormat();
			}
		}
		return preg_replace('#(__FORMAT__)#', $format, $this->schemaBase);
	}



	public function getImgThumbnail($x = 128, $y = 128, $mode = 'cut') {
		// return $this->getBinaryFile();
		return $this->getShemaBase().base64_encode($this->getThumbnail($x, $y, $mode));
	}

	public function getImg() {
		// return $this->getBinaryFile();
		return $this->getShemaBase().base64_encode($this->getBinaryFile());
	}

	/**
	 * Retourne un thumbnail du fichier / null si aucun
	 * @param integer $x - taille X
	 * @param integer $y - taille Y
	 * @param string $mode = 'cut'
	 * @return string
	 */
	public function getThumbnail($x = 128, $y = 128, $mode = 'cut', $format = null) {
		if(!in_array($format, $this->authorizedFormatsByType['image'])) $format = $this->getExtension();
		$thumbnail = null;
		// if($this->getFormat()->getType() == 'image') {
			$aeImages = new aeImages();
			$image = @imagecreatefromstring($this->getBinaryFile());
			if($image != false) {
				$image = $aeImages->thumb_image($image, $x, $y, $mode);
				ob_start();
				switch ($format) {
					case 'jpeg':
					case 'jpg': imagejpeg($image); break;
					case 'gif': imagegif($image); break;
					case 'png': imagepng($image); break;
					default: imagepng($image); break;
				}
				$thumbnail = ob_get_contents();
				ob_end_clean();
				imagedestroy($image);
			} else return "Error while creating image object";
		// }
		return $thumbnail;
	}


	/**
	 * Set item - INVERSE
	 * @param item $item
	 * @return image
	 */
	public function setItem(item $item = null) {
		$this->item = $item;
		return $this;
	}

	/**
	 * Get item - INVERSE
	 * @return item 
	 */
	public function getItem() {
		return $this->item;
	}

	/**
	 * Set tier - INVERSE
	 * @param tier $tier
	 * @return image
	 */
	public function setTier(tier $tier = null) {
		$this->tier = $tier;
		return $this;
	}

	/**
	 * Get tier - INVERSE
	 * @return tier 
	 */
	public function getTier() {
		return $this->tier;
	}

	/**
	 * Set logoTier - INVERSE
	 * @param tier $logoTier
	 * @return image
	 */
	public function setLogoTier(tier $logoTier = null) {
		$this->logoTier = $logoTier;
		return $this;
	}

	/**
	 * Get logoTier - INVERSE
	 * @return tier 
	 */
	public function getLogoTier() {
		return $this->logoTier;
	}

	/**
	 * Set userAvatar - INVERSE
	 * @param User $userAvatar
	 * @return image
	 */
	public function setUserAvatar(User $userAvatar = null) {
		$this->userAvatar = $userAvatar;
		return $this;
	}

	/**
	 * Get userAvatar - INVERSE
	 * @return User 
	 */
	public function getUserAvatar() {
		return $this->userAvatar;
	}






}
