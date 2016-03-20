<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;
use site\adminBundle\Entity\baseSubEntity;

use site\services\aeImages;

use site\adminBundle\Entity\item;
use site\UserBundle\Entity\User;

use \DateTime;
use \Exception;
use \SplFileInfo;


/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\mediaRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="class_name", type="string")
 * @ORM\DiscriminatorMap({"image" = "image", "pdf" = "pdf"})
 * @ORM\HasLifecycleCallbacks
 *
 * @ExclusionPolicy("all")
 */
abstract class media extends baseSubEntity {

	const CLASS_IMAGE		= "image";
	const CLASS_PDF			= "pdf";
	// const CLASS_VIDEO		= "video";
	// const CLASS_AUDIO		= "audio";

	/**
	 * @var integer
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * Nom du fichier original du media
	 * @var string
	 * @ORM\Column(name="originalnom", type="string", length=255, nullable=true, unique=false)
	 */
	protected $originalnom;
		
	/**
	 * Contenu numérique du media
	 * @var string
	 * @ORM\Column(name="binaryFile", type="blob", nullable=true, unique=false)
	 */
	protected $binaryFile;
	
	/**
	 * Type mime (d'origine) du media
	 * @var string
	 * @ORM\Column(name="format", type="string", length=128, nullable=true, unique=false)
	 */
	protected $format;

	/**
	 * Extension originale du nom de fichier du media
	 * @var string
	 * @ORM\Column(name="extension", type="string", length=8, nullable=true, unique=false)
	 */
	protected $extension;

	/**
	 * Stockage du media : 'database' / 'file'
	 * @var string
	 * @ORM\Column(name="stockage", type="string", length=16, nullable=false, unique=false)
	 */
	protected $stockage;

	/**
	 * Informations de recadrage cropper
	 * @var string
	 * @ORM\Column(name="croppingInfo", type="text", nullable=true, unique=false)
	 */
	protected $croppingInfo;

	/**
	 * Taille du fichier d'origine du media
	 * (ou taille du champ "binaryFile" -> à développer)
	 * @var int
	 * @ORM\Column(name="file_size", type="integer", length=10, nullable=true, unique=false)
	 */
	protected $fileSize;

	/**
	 * upload file
	 */
	public $upload_file;


	/****************************/
	/*** DOING WITH RAW FILE ***/
	/**************************/

	/**
	 * @var array
	 * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\rawfile", orphanRemoval=true, cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
	 */
	protected $rawfile;

	
	protected $streamBinaryFile;
	protected $infoForPersist;
	protected $authorizedFormatsByType;
	protected $schemaData;
	protected $schemaBase;
	protected $stockageList;
	protected $mediaType;

	public function __construct() {
		parent::__construct();

		$this->infoForPersist = null;
		$this->croppingInfo = null;
		// $date = new DateTime();
		// $defaultVersion = $date->format('d-m-Y_H-i-s');
		// $this->setNom($defaultVersion);
		$this->init();
	}

	// public function __toString(){
	// 	if($this->dateMaj != null) return $this->nom.' modifié le '.$this->dateMaj->format('d-m-Y H:i:s');
	// 	else return $this->nom.' crée le '.$this->dateCreation->format('d-m-Y H:i:s');
	// }

	// public function getClassName(){
	//     return parent::CLASS_MEDIA;
	// }

	protected function init() {
		$this->streamBinaryFile = null;
		$this->authorizedFormatsByType = array(
			self::CLASS_IMAGE	=> array('png', 'jpg', 'jpeg', 'gif'),
			self::CLASS_PDF		=> array('pdf'),
			);

		// CLASS_IMAGE
		$this->schemaData = '#^(data:image/('.implode("|", $this->authorizedFormatsByType[self::CLASS_IMAGE]).');base64,)#';
		$this->schemaBase = 'data:image/__FORMAT__;base64,';

		$this->stockageList = array('database', 'file');
		// stockage en database par défaut
		$this->setStockage($this->stockageList[0]);
	}

	/**
	 * @ORM\PostLoad
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
	 * @Assert\True(message="Le fichier ne contient aucune donnée.")
	 */
	public function isValid() {
		// return $this->format->getEnabled();
		return $this->binaryFile != null ? true : false ;
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
		if($exp[0] == self::CLASS_IMAGE) return self::CLASS_IMAGE;
		if($exp[1] == self::CLASS_PDF) return self::CLASS_PDF;
		return 'inconnu';
	}

	protected function getExtByMime($typeMime) {
		return explode('/', $typeMime)[1];
	}

	/**
	 * @ORM\PrePersist()
	 * @ORM\PreUpdate()
	 */
	public function upLoad(){
		// $info = $this->getInfoForPersist();
		// if($this->getId() != null) {
		// 	// test only on update…
		// 	if(null === $this->upload_file && null === $this->binaryFile && $info === null) return;
		// }
		if(null != $this->upload_file) {
			// File
			$this->setInfoForPersist(null);
			$stream = fopen($this->upload_file->getRealPath(),'rb');
			$this->setBinaryFile(stream_get_contents($stream));
			fclose($stream);
			$this->setFileSize(filesize($this->upload_file->getRealPath()));
			$this->setOriginalnom($this->upload_file->getClientOriginalName());
			$this->setExtension($this->getUploadFile_extension());
			$this->setFormat($this->getUploadFile_typemime());
			$this->setStockage($this->stockageList[1]);
			if($this->getNom() == null) $this->setNom($this->getOriginalnom());
			$this->defineNom();
		}
		return;
	}



	/**
	 * set infoForPersist
	 * @param json/array $infoForPersist = null
	 * @return media
	 */
	public function setInfoForPersist($infoForPersist = null) {
		if(!is_string($infoForPersist)) $infoForPersist = json_encode($infoForPersist);
		$this->infoForPersist = $infoForPersist;
		return $this;
	}

	/**
	 * get infoForPersist
	 * @return array
	 */
	public function getInfoForPersist() {
		return json_decode($this->infoForPersist, true);
	}




	/**
	 * set croppingInfo
	 * Renvoie false si les informations sont différentes des précédentes
	 * @param json/array $croppingInfo = null
	 * @return boolean
	 */
	public function setCroppingInfo($croppingInfo = null) {
		$oldcroppingInfo = $this->croppingInfo;
		if(!is_string($croppingInfo)) {
			$croppingInfo = json_encode($croppingInfo);
		}
		$this->croppingInfo = $croppingInfo;
		return $this->croppingInfo == $oldcroppingInfo;
	}

	/**
	 * get croppingInfo
	 * @return array
	 */
	public function getCroppingInfo() {
		return json_decode($this->croppingInfo, true);
	}

	/**
	 * get croppingInfo in JSON
	 * @return string
	 */
	public function getJsonCroppingInfo() {
		return $this->croppingInfo;
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

	/*!!!! <---- ;-)
	 * Get upload file extension
	 * @return string 
	 */
	// public function getUploadFile_isRealyAFile() {
	// 	if (null === $this->upload_file) return false;
	// 	$fileInfo = new SplFileInfo($this->upload_file->getRealPath());
	// 	return ($fileInfo->isFile() && !($fileInfo->isDir()) && !($fileInfo->isExecutable()) && !($fileInfo->isLink()));
	// }

	/**
	 * Set binaryFile
	 * @param string $binaryFile
	 * @return media
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
	}

	/**
	 * Set rawfile
	 * @param rawfile $rawfile
	 * @return media
	 */
	public function setRawfile(rawfile $rawfile = null) {
		// if(is_object($this->rawfile)) $this->rawfile->setMedia(null);
		$this->rawfile = $rawfile;
		// $rawfile->setMedia($this);
		return $this;
	}

	/**
	 * Get rawfile
	 * @return rawfile 
	 */
	public function getRawfile() {
		return $this->rawfile;
	}




	/**
	 * Define nom
	 * @return media
	 */
	public function defineNom() {
		if($this->nom == null) {
			$date = new DateTime();
			$defaultVersion = $date->format('d-m-Y_H-i-s')."_".rand(10000,99999);
		}
		return $this;
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
	 * @return media
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
	 * @return media
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
	 * @return media
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
	 * Set stockage
	 * @param string $stockage
	 * @return media
	 */
	public function setStockage($stockage) {
		if(in_array($stockage, $this->stockageList)) {
			$this->stockage = $stockage;
		} else throw new Exception("Stock Support not recognized : ".$stockage.". Need : ".json_encode($this->stockageList), 1);
		return $this;
	}

	/**
	 * Get stockage
	 * @return string
	 */
	public function getStockage() {
		return $this->stockage;
	}

	/**
	 * Set fileSize
	 * @param integer $fileSize
	 * @return media
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
	 * is media a screenable IMAGE type ?
	 * @return boolean
	 */
	public function isScreenableImage(){
		return ($this->isImage() && ($this->getBinaryFile() != null));
	}

	/**
	 * is media an IMAGE type ?
	 * @return boolean
	 */
	public function isImage(){
		return $this->getClass(true) == self::CLASS_IMAGE;
	}

	/**
	 * is media a PDF type ?
	 * @return boolean
	 */
	public function isPdf(){
		return $this->getClass(true) == self::CLASS_PDF;
	}



	/**
	 * Get id
	 * @return integer 
	 */
	public function getId() {
		return $this->id;
	}




}
