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
use site\services\aetools;

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
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\imageRepository")
 * @ExclusionPolicy("all")
 */
class image extends media {


	/**
	 * - INVERSE
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\item", mappedBy="image")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $item;

	/**
	 * - INVERSE
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\tier", mappedBy="image")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $tier;

	/**
	 * - INVERSE
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\tier", mappedBy="logo")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $logoTier;

	/**
	 * - INVERSE
	 * @ORM\OneToOne(targetEntity="site\UserBundle\Entity\User", mappedBy="avatar")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $userAvatar;

	/**
	 * @var string
	 * @ORM\Column(name="owner", type="string", nullable=true, unique=false)
	 */
	protected $owner;

	/**
	 * @var integer
	 * @ORM\Column(name="ratioIndex", type="integer", nullable=true, unique=false)
	 */
	protected $ratioIndex;

	/**
	 * @var integer
	 * @ORM\Column(name="width", type="integer", nullable=true, unique=false)
	 */
	protected $width;

	/**
	 * @var integer
	 * @ORM\Column(name="height", type="integer", nullable=true, unique=false)
	 */
	protected $height;

	protected $cropperInfo;
	protected $aeReponse;

	public function __construct() {
		parent::__construct();
		$this->owner = null;
		$this->item = null;
		$this->tier = null;
		$this->logoTier = null;
		$this->userAvatar = null;
		$this->ratioIndex = 0;
		$this->width = 0;
		$this->height = 0;
		$this->aeReponse = null;
		$this->getCropperInfo();
	}

    // public function getClassName(){
    //     return parent::CLASS_IMAGE;
    // }

	public function getCropperInfo() {
		$aetools = new aetools();
		$this->cropperInfo = $aetools->getConfigParameters('cropper.yml');
		return $this->cropperInfo;
	}

	/**
	 * @ORM\PrePersist()
	 * @ORM\PreUpdate()
	 */
	public function upLoad(){
		parent::upLoad();

		if(null == $this->upload_file) {
			$info = $this->getInfoForPersist();
			if(isset($info['dataType'])) {
				if($info['dataType'] == "cropper") {
					// cropper
					// echo('<pre>Rawfile id : '.$this->getRawfile()->getId());
					// var_dump($info);
					// die('</pre>');
					// if($info['owner'] != null) $this->setOwner($info['owner']);
					if(isset($info['ratioIndex'])) $this->setRatioIndex($info['ratioIndex']);
						else $this->setRatioIndex(0);
					if($info['width'] != null) $this->setWidth($info['width']);
					if($info['height'] != null) $this->setheight($info['height']);
					if($info['file']['size'] != null) $this->setFileSize($info['file']['size']);
					if($info['file']['type'] != null) {
						$this->setFormat($info['file']['type']);
						$this->setMediaType($this->getTypeOf($info['file']['type']));
					}
					$filehaschanged = false;
					if($info['file']['name'] != null) {
						$filehaschanged = true;
						$this->setOriginalnom($info['file']['name']);
						$ext = explode('.', $info['file']['name']);
						$ext = end($ext);
						if(!in_array($ext, $this->authorizedFormatsByType)) $this->setExtension($this->getExtByMime($info['file']['type']));
						$this->setExtension($ext);
					}
					if($info['delete'] == true) {
						// opération dans le service…
					}
					if($this->getRawfile() == null) {
						// ne possède pas de rawfile
					} else {
						// possède un raw file
						if(isset($info['getData'])) {
							$notChanged = $this->setCroppingInfo($info['getData']);
							if((!$notChanged) || $filehaschanged) {
								// if(!$notChanged)
								// 	echo('Changement de recadrage…');
								// 	else
								// 	echo('Changement d\'image…');
								// echo('<p>Owner entity : '.$this->getOwnerEntity().'</p>');
								// echo('<p>Owner field : '.$this->getOwnerField().'</p>');
								// echo('<p>RatioIndex : '.$this->getRatioIndex().'</p>');
								$this->getCropperInfo();
								if(isset($this->cropperInfo['formats'][$this->getOwnerEntity()][$this->getOwnerField()][$this->getRatioIndex()])) {
									$format = $this->cropperInfo['formats'][$this->getOwnerEntity()][$this->getOwnerField()][$this->getRatioIndex()];
								} else {
									$format = $this->cropperInfo['formats']['default'][$this->getRatioIndex()];
								}
								$this->aeReponse = $this->getRawfile()->getCropped($format[0], $format[1], $info);
								// echo($this->aeReponse->getMessage());
								if($this->aeReponse->getResult() == true) {
									// SUCCESS
									$image = $this->aeReponse->getData();
									// echo('<h1>OK !!</h1>');
									// echo('<img src="'.$this->getShemaBase().base64_encode($image).'">');
									$this->setBinaryFile($image);
								} else {
									// ERROR
								}
							} // else echo('<p>Aucun changement ????</p>');
						} // else echo('<p>Pas de getData ????</p>');
					}
					$this->setStockage($this->stockageList[0]);
				}
			}
		}
	}

	public function getAeReponse() {
		return $this->aeReponse;
	}

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
		if(!in_array($format, $this->authorizedFormatsByType[self::CLASS_IMAGE])) $format = $this->getExtension();
		$thumbnail = null;
		// if($this->getFormat()->getType() == self::CLASS_IMAGE) {
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
	 * Set owner
	 * @param owner $owner
	 * @return image
	 */
	public function setOwner($owner = null) {
		$this->owner = $owner;
		return $this;
	}

	/**
	 * Get owner
	 * @return string 
	 */
	public function getOwner() {
		return $this->owner;
	}

	/**
	 * Get owner entity
	 * @return string 
	 */
	public function getOwnerEntity() {
		return $this->owner != null ? explode(':', $this->owner)[0] : null ;
	}

	/**
	 * Get owner field
	 * @return string 
	 */
	public function getOwnerField() {
		return $this->owner != null ? explode(':', $this->owner)[1] : null ;
	}

	/**
	 * Set ratioIndex
	 * @param ratioIndex $ratioIndex
	 * @return image
	 */
	public function setRatioIndex($ratioIndex = null) {
		$this->ratioIndex = $ratioIndex;
		return $this;
	}

	/**
	 * Get ratioIndex
	 * @return string 
	 */
	public function getRatioIndex() {
		return $this->ratioIndex;
	}

	/**
	 * Set width
	 * @param width $width
	 * @return image
	 */
	public function setWidth($width = null) {
		$this->width = $width;
		return $this;
	}

	/**
	 * Get width
	 * @return string 
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * Set height
	 * @param height $height
	 * @return image
	 */
	public function setHeight($height = null) {
		$this->height = $height;
		return $this;
	}

	/**
	 * Get height
	 * @return string 
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * Set item - INVERSE
	 * @param item $item
	 * @return image
	 */
	public function setItem(item $item = null) {
		$this->item = $item;
		$this->setOwner($item->getClassName().':image');
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
		$this->setOwner($tier->getClassName().':image');
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
		$this->setOwner($logoTier->getClassName().':logo');
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
		$this->setOwner($userAvatar->getClassName().':avatar');
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
