<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\services\aeImages;

use site\adminBundle\Entity\pageweb;
use site\adminBundle\Entity\fileFormat;
use site\UserBundle\Entity\User;

use \DateTime;
use \Exception;
use \SplFileInfo;

/**
 * media
 *
 * @ORM\Table()
 * @ORM\Table(name="media")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\mediaRepository")
 */
class media {

	/**
	 * @var integer
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=255)
	 */
	protected $nom;
	
	/**
	 * @var string
	 * @ORM\Column(name="originalnom", type="string", length=255)
	 */
	protected $originalnom;
		
	/**
	 * @var string
	 * @ORM\Column(name="binaryFile", type="blob")
	 */
	protected $binaryFile;
	
	/**
	 * @var string
	 * @ORM\Column(name="format", type="string", length=128)
	 */
	protected $format;

	/**
	 * @var string
	 * @ORM\Column(name="extension", type="string", length=8)
	 */
	protected $extension;

	/**
	 * @var string
	 * @ORM\Column(name="mediaType", type="string", length=32)
	 */
	protected $mediaType;

	/**
	 * Strockage du media : 'database' / 'file'
	 * @var string
	 * @ORM\Column(name="stockSupport", type="string", length=16)
	 */
	protected $stockSupport;

	/**
	 * @ORM\OneToOne(targetEntity="pageweb", inversedBy="background")
	 * @ORM\JoinColumn(nullable=true, unique=true, name="pageweb_id", referencedColumnName="id", onDelete="SET NULL")
	 */
	protected $pagewebBackground;

	/**
	 * @ORM\OneToOne(targetEntity="site\UserBundle\Entity\User", inversedBy="avatar")
	 * @ORM\JoinColumn(nullable=true, unique=true, name="User_id", referencedColumnName="id", onDelete="SET NULL")
	 */
	protected $userAvatar;

	/**
	 * @Gedmo\Slug(fields={"nom"})
	 * @ORM\Column(length=128, unique=true)
	 */
	protected $slug;
	
	/**
	 * @var int
	 * @ORM\Column(name="file_size", type="integer", length=10)
	 */
	protected $fileSize;

	/**
	 * @var DateTime
	 * @ORM\Column(name="created", type="datetime", nullable=false)
	 */
	protected $dateCreation;

	/**
	 * @var DateTime
	 * @ORM\Column(name="updated", type="datetime", nullable=true)
	 */
	protected $dateMaj;


	public $upload_file;
	
	protected $streamBinaryFile;
	protected $infoForPersist;
	protected $authorizedFormatsByType;
	protected $schemaData;
	protected $schemaBase;
	protected $stockSupportList;
	
	public function __construct() {
		$this->dateCreation = new DateTime();
		$this->dateMaj = null;
		$this->infoForPersist = null;
		$date = new DateTime();
		$defaultVersion = $date->format('d-m-Y_H-i-s');
		$this->setNom($defaultVersion);
		$this->init();
	}

	public function __toString(){
		if($this->dateMaj != null) return $this->nom.' modifié le '.$this->dateMaj->format('d-m-Y H:i:s');
		else return $this->nom.' crée le '.$this->dateCreation->format('d-m-Y H:i:s');
	}

	protected function init() {
		$this->streamBinaryFile = null;
		$this->authorizedFormatsByType = array(
			'image'	=> array('png', 'jpg', 'jpeg', 'gif'),
			'pdf'	=> array('pdf'),
			);
		$this->schemaData = '#^(data:image/('.implode("|", $this->authorizedFormatsByType['image']).');base64,)#';
		$this->schemaBase = 'data:image/__FORMAT__;base64,';
		$this->stockSupportList = array('database', 'file');
	}

	/**
	 * @ORM\PostLoad()
	 */
	public function onLoad($construct = false) {
		$this->init();
		// nom
		if($this->getNom() == null) $this->setNom($this->getOriginalnom());
		// binaryFile
		if($this->binaryFile != null) {
			$this->streamBinaryFile = stream_get_contents($this->binaryFile);
		}
	}

	/**
	 * @Assert\True(message="Le type de fichier n'est pas conforme.")
	 */
	public function isAuthorizedFileFormat() {
		return $this->format->getEnabled();
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

	protected function getTypeOf($typeMime) {
		$exp = explode('/', $typeMime);
		if($exp[0] == 'image') return 'image';
		if($exp[1] == 'pdf') return 'pdf';
		return 'inconnu';
	}

	/**
	 * @ORM\PrePersist()
	 * @ORM\PreUpdate()
	 */
	public function upLoad(){
		if($this->getId() != null) {
			// test only on update…
			if(null === $this->upload_file && null === $this->binaryFile) return;
		}
		if(null != $this->upload_file) {
			// File
			$stream = fopen($this->upload_file->getRealPath(),'rb');
			$this->setBinaryFile(stream_get_contents($stream));
			fclose($stream);
			$this->setFileSize(filesize($this->upload_file->getRealPath()));
			$this->setOriginalnom($this->upload_file->getClientOriginalName());
			$this->setExtension($this->getUploadFile_extension());
			$this->setFormat($this->getUploadFile_typemime());
			$this->setStockSupport($this->stockSupportList[1]);
		} else if(null != $this->binaryFile) {
			// cropper
			$info = $this->getInfoForPersist();
			if(preg_match($this->schemaData, $this->binaryFile)) {
				// Format non Raw
				$rotenData = preg_replace($this->schemaData, '', $this->binaryFile);
				$this->setBinaryFile(base64_decode($rotenData));
				unset($rotenData);
			}
			if($info['fileStatus'] == 'filled') {
				$this->setFileSize($info['file']['size']);
				$this->setFormat($info['file']['type']);
				$this->setMediaType($this->getTypeOf($info['file']['type']));
				$ext = explode('.', $info['file']['name']);
				$ext = end($ext);
				if(!in_array($ext, $this->authorizedFormatsByType)) {
					// format non trouvé, on prend sur le type mime
					if(in_array($this->getMediaType(), array('image', 'pdf'))) {
						$ext = explode('/', $info['file']['type'])[1];
					} else $ext = 'txt';
				}
				$this->setExtension($ext);
			}
			$this->setStockSupport($this->stockSupportList[0]);
		}
		if($this->getNom() == null) $this->setNom($this->getOriginalnom());
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
	 * set infoForPersist
	 * @param string $infoForPersist = null
	 * @return media
	 */
	public function setInfoForPersist($infoForPersist = null) {
		if(!is_string($infoForPersist)) $infoForPersist = json_encode($infoForPersist);
		$this->infoForPersist = $infoForPersist;
		return $this;
	}

	/**
	 * get infoForPersist
	 */
	public function getInfoForPersist() {
		return json_decode($this->infoForPersist, true);
	}

	/**
	 * Get id
	 * @return integer 
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get upload file name
	 * @return string 
	 */
	public function getUploadFile_typemime() {
		if (null === $this->upload_file) return false;
		// http://api.symfony.com/2.0/Symfony/Component/HttpFoundation/File/UploadedFile.html
		return $this->upload_file->getMimeType();
	}

	/**
	 * Get upload file extension
	 * @return string 
	 */
	public function getUploadFile_extension() {
		if (null === $this->upload_file) return false;
		// http://api.symfony.com/2.0/Symfony/Component/HttpFoundation/File/UploadedFile.html
		return $this->upload_file->guessExtension();
	}

	/**
	 * Get upload file extension
	 * @return string 
	 */
	public function getUploadFile_isRealyAFile() {
		if (null === $this->upload_file) return false;
		$fileInfo = new SplFileInfo($this->upload_file->getRealPath());
		return ($fileInfo->isFile() && !($fileInfo->isDir()) && !($fileInfo->isExecutable()) && !($fileInfo->isLink()));
	}

	/**
	 * Set binaryFile
	 * @param string $binaryFile
	 * @return Version
	 */
	public function setBinaryFile($binaryFile) {
		$this->binaryFile = $binaryFile;
		return $this;
	}

	/**
	 * Get binaryFile
	 * @return string 
	 */
	public function getBinaryFile() {
		return $this->streamBinaryFile;
		// return stream_get_contents($this->binaryFile);
	}

	/**
	 * Set pagewebBackground
	 * @param pageweb $pagewebBackground
	 * @return media
	 */
	public function setPagewebBackground(pageweb $pagewebBackground = null) {
		$this->pagewebBackground = $pagewebBackground;
		$pagewebBackground->setBackground_reverse($this);
		return $this;
	}

	/**
	 * Set pagewebBackground reversed side
	 * @param pageweb $pagewebBackground
	 * @return media
	 */
	public function setPagewebBackground_reverse(pageweb $pagewebBackground = null) {
		$this->pagewebBackground = $pagewebBackground;
		return $this;
	}

	/**
	 * Get pagewebBackground
	 * @return pageweb 
	 */
	public function getPagewebBackground() {
		return $this->pagewebBackground;
	}

	/**
	 * Set userAvatar
	 * @param User $userAvatar
	 * @return media
	 */
	public function setUserAvatar(User $userAvatar = null) {
		$this->userAvatar = $userAvatar;
		$userAvatar->setAvatar_reverse($this);
		return $this;
	}

	/**
	 * Set userAvatar reversed side
	 * @param User $userAvatar
	 * @return media
	 */
	public function setUserAvatar_reverse(User $userAvatar = null) {
		$this->userAvatar = $userAvatar;
		return $this;
	}

	/**
	 * Get userAvatar
	 * @return User 
	 */
	public function getUserAvatar() {
		return $this->userAvatar;
	}

	/**
	 * Set nom
	 * @param string $nom
	 * @return media
	 */
	public function setNom($nom) {
		$this->nom = $nom;
		return $this;
	}

	/**
	 * Get nom
	 * @return string
	 */
	public function getNom() {
		return $this->nom;
	}

	/**
	 * Set originalnom
	 * @param string $originalnom
	 * @return media
	 */
	public function setOriginalnom($originalnom) {
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
	 * @return Version
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
	 * @return Version
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
	 * Set mediaType
	 * @param string $mediaType
	 * @return Version
	 */
	public function setMediaType($mediaType) {
		$this->mediaType = strtolower($mediaType);
		return $this;
	}

	/**
	 * Get mediaType
	 * @return string
	 */
	public function getMediaType() {
		return $this->mediaType;
	}

	/**
	 * Set stockSupport
	 * @param string $stockSupport
	 * @return Version
	 */
	public function setStockSupport($stockSupport) {
		if(in_array($stockSupport, $this->stockSupportList)) {
			$this->stockSupport = $stockSupport;
		} else throw new Exception("Stock Support not recognized : ".$stockSupport.". Need : ".json_encode($this->stockSupportList), 1);
		return $this;
	}

	/**
	 * Get stockSupport
	 * @return string
	 */
	public function getStockSupport() {
		return $this->stockSupport;
	}

	/**
	 * Set fileSize
	 * @param integer $fileSize
	 * @return Version
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
	 * is version an IMAGE type ?
	 * @return boolean
	 */
	public function isImage(){
		return $this->getMediaType() == 'image';
	}

	/**
	 * is version a screenable IMAGE type ?
	 * @return boolean
	 */
	public function isScreenableImage(){
		return ($this->isImage() && ($this->getBinaryFile() != null));
	}

	/**
	 * is version a PDF type ?
	 * @return boolean
	 */
	public function isPdf(){
		return $this->getMediaType() == "pdf";
	}

	/**
	 * Set slug
	 * @param integer $slug
	 * @return media
	 */
	public function setSlug($slug) {
		$this->slug = $slug;
		return $this;
	}

	/**
	 * Get slug
	 * @return string
	 */
	public function getSlug() {
		return $this->slug;
	}

	/**
	 * Set dateCreation
	 * @param DateTime $dateCreation
	 * @return pageweb
	 */
	public function setDateCreation(DateTime $dateCreation) {
		$this->dateCreation = $dateCreation;
		return $this;
	}

	/**
	 * Get dateCreation
	 * @return DateTime 
	 */
	public function getDateCreation() {
		return $this->dateCreation;
	}

	/**
	 * @ORM\PreUpdate
	 */
	public function updateDateMaj() {
		$this->setDateMaj(new DateTime());
	}

	/**
	 * Set dateMaj
	 * @param DateTime $dateMaj
	 * @return pageweb
	 */
	public function setDateMaj(DateTime $dateMaj) {
		$this->dateMaj = $dateMaj;
		return $this;
	}

	/**
	 * Get dateMaj
	 * @return DateTime 
	 */
	public function getDateMaj() {
		return $this->dateMaj;
	}


}
