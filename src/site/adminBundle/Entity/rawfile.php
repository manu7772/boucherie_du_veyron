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
use site\services\aeReponse;

use site\adminBundle\Entity\baseSubEntity;

use site\adminBundle\Entity\media;
use site\adminBundle\Entity\image;
use site\adminBundle\Entity\pdf;

use \DateTime;
use \Exception;

/**
 * rawfile
 *
 * @ORM\Entity
 * @ORM\Table(name="rawfile")
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\rawfileRepository")
 * @ORM\HasLifecycleCallbacks
 */
class rawfile extends baseSubEntity {

	// const CLASS_RAWFILE = 'rawfile';
    const CLASS_IMAGE		= "image";
    const CLASS_PDF			= "pdf";
    // const CLASS_VIDEO		= "video";
    // const CLASS_AUDIO		= "audio";

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * Nom du fichier original du rawfile
	 * @var string
	 * @ORM\Column(name="originalnom", type="string", length=255, nullable=true, unique=false)
	 */
	protected $originalnom;

	/**
	 * Type mime (d'origine) du rawfile
	 * @var string
	 * @ORM\Column(name="format", type="string", length=128, nullable=true, unique=false)
	 */
	protected $format;

	/**
	 * Extension originale du nom de fichier du rawfile
	 * @var string
	 * @ORM\Column(name="extension", type="string", length=8, nullable=true, unique=false)
	 */
	protected $extension;

	/**
	 * Taille du fichier d'origine du rawfile
	 * @var int
	 * @ORM\Column(name="file_size", type="integer", length=10, nullable=true, unique=false)
	 */
	protected $fileSize;

	/**
	 * Taille du X du rawfile
	 * @var int
	 * @ORM\Column(name="width_x", type="integer", length=10, nullable=false, unique=false)
	 */
	protected $width;

	/**
	 * Taille du Y du rawfile
	 * @var int
	 * @ORM\Column(name="height_y", type="integer", length=10, nullable=false, unique=false)
	 */
	protected $height;

	/**
	 * Contenu numérique du rawfile
	 * @var string
	 * @ORM\Column(name="binaryFile", type="blob", nullable=false, unique=false)
	 */
	protected $binaryFile;

	/**
	 * Contenu numérique du thumbnail du rawfile
	 * @var string
	 * @ORM\Column(name="binaryLowFile", type="blob", nullable=false, unique=false)
	 */
	protected $binaryLowFile;

	/**
	 * @Gedmo\Slug(fields={"originalnom"})
	 * @ORM\Column(length=128, unique=true)
	 */
	protected $slug;


	protected $streamBinaryFile;
	protected $streamBinaryLowFile;
	protected $schemaData;
	protected $schemaBase;
	protected $authorizedFormatsByType;
	protected $cropperInfo;

	public function __construct() {
		parent::__construct();
		$this->setWidth(0);
		$this->setHeight(0);
		// $this->streamBinaryFile = null;
		// $this->streambinaryLowFile = null;
		$this->init();
	}

	public function getCropperInfo() {
		$aetools = new aetools();
		$this->cropperInfo = $aetools->getConfigParameters('cropper.yml');
		return $this->cropperInfo;
	}

	public function getModelWidth() {
		if($this->cropperInfo == null) $this->getCropperInfo();
		return $this->cropperInfo['modelWidth'];
	}

	protected function init() {
		$this->getCropperInfo();
		$this->authorizedFormatsByType = array(
			self::CLASS_IMAGE	=> array('png', 'jpg', 'jpeg', 'gif'),
			self::CLASS_PDF		=> array('pdf'),
			);
		// CLASS_IMAGE
		$this->schemaData = '#^(data:image/('.implode("|", $this->authorizedFormatsByType[self::CLASS_IMAGE]).');base64,)#';
		$this->schemaBase = 'data:__FORMAT__;base64,';
	}

	/**
	 * @ORM\PostLoad
	 */
	public function onLoad() {
		$this->init();
		if($this->binaryFile != null) {
			$this->streamBinaryFile = stream_get_contents($this->binaryFile);
		}
		if($this->binaryLowFile != null) {
			$this->streamBinaryLowFile = stream_get_contents($this->binaryLowFile);
		}
		$this->getCropperInfo();
	}

	public function getShemaBase($format = null) {
		if(!is_string($format)) {
			$format = 'png';
			if($this->getFormat() != null) {
				$format = $this->getFormat();
			}
		}
		return preg_replace('#(__FORMAT__)#', $format, $this->schemaBase);
	}


	public function getLowImgThumbnail($x = 128, $y = 128, $mode = 'cut') {
		return $this->getShemaBase().base64_encode($this->getThumbnail($this->getBinaryLowFile(), $x, $y, $mode));
	}

	public function getImgThumbnail($x = 128, $y = 128, $mode = 'cut') {
		return $this->getShemaBase().base64_encode($this->getThumbnail($this->getBinaryFile(), $x, $y, $mode));
	}

	public function getLowImg() {
		return $this->getShemaBase().base64_encode($this->getBinaryLowFile());
	}

	public function getImg() {
		return $this->getShemaBase().base64_encode($this->getBinaryFile());
	}

	/**
	 * Retourne un thumbnail du fichier / null si aucun
	 * @param integer $x - taille X
	 * @param integer $y - taille Y
	 * @param string $mode = 'cut'
	 * @return string
	 */
	public function getThumbnail($thumbnail = null, $x = null, $y = null, $mode = 'cut', $format = null) {
		if($thumbnail == null) $thumbnail = $this->getBinaryFile();
		if(!in_array($format, $this->authorizedFormatsByType[self::CLASS_IMAGE])) $format = $this->getExtension();
		$aeImages = new aeImages();
		$image = @imagecreatefromstring($thumbnail);
		if($image != false) {
			$this->setWidth(imagesx($image));
			$this->setHeight(imagesy($image));
			$image = $aeImages->thumb_image($image, $x, $y, $mode);
			ob_start();
			switch ($format) {
				case 'jpeg':
				case 'jpg': imagejpeg($image); break;
				case 'gif': imagegif($image); break;
				case 'png':
				default: imagepng($image); break;
			}
			$thumbnail = ob_get_contents();
			ob_end_clean();
			imagedestroy($image);
		} else throw new Exception("Erreur à la création de l'objet image : site\\adminBundle\\Entity\\rawfile::getThumbnail(), line 204", 1);
		return $thumbnail;
	}

	public function getCropThumbnail($imgSource = null) {
		if($imgSource != null) {
			if(preg_match($this->schemaData, $imgSource)) {
				// Format non Raw
				$imgSource = base64_decode(preg_replace($this->schemaData, '', $imgSource));
			}
			return $this->getShemaBase().base64_encode($this->getThumbnail($imgSource, $this->getModelWidth(), null, 'deform'));
		}
		return $this->getShemaBase().base64_encode($this->getThumbnail($this->getBinaryFile(), $this->getModelWidth(), null, 'deform'));
		// return $this->getShemaBase().base64_encode($this->getThumbnail($this->binaryFile, $this->getModelWidth(), null, 'deform'));
	}

	/**
	 * Renvoie une image à la taille $w x $h selon les paramètres getData (cropper)
	 * @param integer $w
	 * @param integer $h
	 * @param array $data
	 * @return aeReponse
	 */
	public function getCropped($w, $h, $data) {
        set_time_limit(120);
		ini_set('memory_limit', '512M');
		// renvoie l'image traitée selon les données cropper
		$coef = $this->getWidth() / $this->getModelWidth();
		$aeImages = new aeImages();
		$image = @imagecreatefromstring($this->getBinaryFile());
		if($image != false) {
			$aeReponse = $aeImages->getCropped(
				$image,
				$w, 
				$h, 
				$data['getData']['x'] * $coef, 
				$data['getData']['y'] * $coef, 
				$data['getData']['width'] * $coef, 
				$data['getData']['height'] * $coef, 
				$data['getData']['rotate']
			);
			if($aeReponse->getResult() == true) {
				$image = $aeReponse->getData();
				ob_start();
				switch ($this->getExtension()) {
					case 'jpeg':
					case 'jpg': imagejpeg($image); break;
					case 'gif': imagegif($image); break;
					case 'png':
					default: imagepng($image); break;
				}
				$thumbnail = ob_get_contents();
				ob_end_clean();
				$aeReponse->setData($thumbnail);
				imagedestroy($image);
			}
		} else {
			return new aeReponse(false, null, "Error while creating image object.");
		}
		return $aeReponse;
	}

	// public function getClassName(){
	//     return parent::CLASS_RAWFILE;
	// }

	/**
	 * Get id
	 * @return integer 
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set originalnom
	 * @param string $originalnom
	 * @return rawfile
	 */
	public function setOriginalnom($originalnom) {
		$exp = explode('.', $originalnom);
		$this->setExtension(end($exp));
		$this->originalnom = $originalnom;
		return $this;
	}

	/**
	 * Get originalnom
	 * @return string
	 */
	public function getOriginalnom() {
		return $this->originalnom;
	}

	/**
	 * Set format
	 * @param fileFormat $format
	 * @return rawfile
	 */
	public function setFormat($format) {
		$this->format = $format;
		return $this;
	}

	/**
	 * Get format
	 * @return fileFormat
	 */
	public function getFormat() {
		return $this->format;
	}

	/**
	 * Set extension
	 * @param string $extension
	 * @return rawfile
	 */
	public function setExtension($extension) {
		$this->extension = strtolower($extension);
		return $this;
	}

	/**
	 * Get extension
	 * @return string
	 */
	public function getExtension() {
		return $this->extension;
	}

	/**
	 * Set fileSize
	 * @param integer $fileSize
	 * @return rawfile
	 */
	public function setFileSize($fileSize) {
		$this->fileSize = $fileSize;
		return $this;
	}

	/**
	 * Get fileSize
	 * @return integer
	 */
	public function getFileSize() {
		return $this->fileSize;
	}

	/**
	 * Set width
	 * @param integer $width
	 * @return rawfile
	 */
	public function setWidth($width) {
		$this->width = $width;
		return $this;
	}

	/**
	 * Get width
	 * @return integer
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * Set height
	 * @param integer $height
	 * @return rawfile
	 */
	public function setHeight($height) {
		$this->height = $height;
		return $this;
	}

	/**
	 * Get height
	 * @return integer
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * Get dimensions
	 * @return string
	 */
	public function getDimension() {
		if($this->getWidth()+$this->getHeight() == 0) return 'dimensionInconnue';
		return $this->getWidth().'x'.$this->getHeight().'px';
	}

	/**
	 * Set binaryFile
	 * @param string $binaryFile
	 * @return rawfile
	 */
	public function setBinaryFile($binaryFile) {
		if(preg_match($this->schemaData, $binaryFile)) {
			// Format non Raw
			$binaryFile = base64_decode(preg_replace($this->schemaData, '', $binaryFile));
		}
		$this->binaryFile = $binaryFile;
		$this->setBinaryLowFile($this->getThumbnail($this->binaryFile, 64, 64, 'cut', $this->getExtension()));
		return $this;
	}

	/**
	 * Get binaryFile
	 * @return string 
	 */
	public function getBinaryFile() {
		return $this->streamBinaryFile;
	}

	/**
	 * Set binaryLowFile
	 * @param string $binaryLowFile
	 * @return rawfile
	 */
	public function setBinaryLowFile($binaryLowFile) {
		if(preg_match($this->schemaData, $binaryLowFile)) {
			// Format non Raw
			$binaryLowFile = base64_decode(preg_replace($this->schemaData, '', $binaryLowFile));
		}
		$this->binaryLowFile = $binaryLowFile;
		return $this;
	}

	/**
	 * Get binaryLowFile
	 * @return string 
	 */
	public function getBinaryLowFile() {
		return $this->streamBinaryLowFile;
	}



}
