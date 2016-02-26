<?php

// namespace site\adminBundle\Entity;

// use Doctrine\ORM\Mapping as ORM;
// use Symfony\Component\Validator\Constraints as Assert;
// use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
// use Doctrine\Common\Collections\ArrayCollection;
// use JMS\Serializer\Annotation\ExclusionPolicy;
// use JMS\Serializer\Annotation\Expose;
// // Slug
// use Gedmo\Mapping\Annotation as Gedmo;

// use site\services\aeImages;

// use site\adminBundle\Entity\image;

// use \DateTime;
// use \Exception;

// /**
//  * rawfile
//  *
//  * @ORM\Entity
//  * @ORM\Table(name="rawfile")
//  * @ORM\Entity(repositoryClass="site\adminBundle\Entity\rawfileRepository")
//  */
// class rawfile {

// 	const CLASS_RAWFILE = 'rawfile';

// 	/**
// 	 * @var integer
// 	 * @ORM\Id
// 	 * @ORM\Column(name="id", type="integer")
// 	 * @ORM\GeneratedValue(strategy="AUTO")
// 	 */
// 	protected $id;

// 	/**
// 	 * - INVERSE
// 	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\item", cascade={"all"}, mappedBy="rawfile")
// 	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
// 	 */
// 	protected $image;


// 	public function __construct() {
// 		$this->image = null;
// 	}

//     public function getClassName(){
//         return parent::CLASS_RAWFILE;
//     }



// 	public function getShemaBase($format = null) {
// 		// $this->schemaBase = 'data:image/***;base64,';
// 		if(!is_string($format)) {
// 			$format = 'png';
// 			if($this->getFormat() != null) {
// 				$format = $this->getFormat();
// 			}
// 		}
// 		return preg_replace('#(__FORMAT__)#', $format, $this->schemaBase);
// 	}



// 	public function getImgThumbnail($x = 128, $y = 128, $mode = 'cut') {
// 		// return $this->getBinaryFile();
// 		return $this->getShemaBase().base64_encode($this->getThumbnail($x, $y, $mode));
// 	}

// 	public function getImg() {
// 		// return $this->getBinaryFile();
// 		return $this->getShemaBase().base64_encode($this->getBinaryFile());
// 	}

// 	/**
// 	 * Retourne un thumbnail du fichier / null si aucun
// 	 * @param integer $x - taille X
// 	 * @param integer $y - taille Y
// 	 * @param string $mode = 'cut'
// 	 * @return string
// 	 */
// 	public function getThumbnail($x = 128, $y = 128, $mode = 'cut', $format = null) {
// 		if(!in_array($format, $this->authorizedFormatsByType['image'])) $format = $this->getExtension();
// 		$thumbnail = null;
// 		// if($this->getFormat()->getType() == 'image') {
// 			$aeImages = new aeImages();
// 			$image = @imagecreatefromstring($this->getBinaryFile());
// 			if($image != false) {
// 				$image = $aeImages->thumb_image($image, $x, $y, $mode);
// 				ob_start();
// 				switch ($format) {
// 					case 'jpeg':
// 					case 'jpg': imagejpeg($image); break;
// 					case 'gif': imagegif($image); break;
// 					case 'png': imagepng($image); break;
// 					default: imagepng($image); break;
// 				}
// 				$thumbnail = ob_get_contents();
// 				ob_end_clean();
// 				imagedestroy($image);
// 			} else return "Error while creating image object";
// 		// }
// 		return $thumbnail;
// 	}


// 	/**
// 	 * Set image - INVERSE
// 	 * @param image $image
// 	 * @return image
// 	 */
// 	public function setImage(image $image = null) {
// 		$this->image = $image;
// 		return $this;
// 	}

// 	/**
// 	 * Get image - INVERSE
// 	 * @return image 
// 	 */
// 	public function getImage() {
// 		return $this->image;
// 	}



// }
