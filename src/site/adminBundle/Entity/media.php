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

use \DateTime;
use \SplFileInfo;

/**
 * media
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="mediaRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class media {

	/**
	 * @var integer
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=255)
	 */
	private $nom;
	
	/**
	 * @var string
	 * @ORM\Column(name="originalnom", type="string", length=255)
	 */
	private $originalnom;
		
	/**
	 * @var string
	 * @ORM\Column(name="binaryFile", type="blob")
	 */
	private $binaryFile;
	
	/**
	 * @ORM\ManyToOne(targetEntity="fileFormat")
	 */
	private $format;

	/**
	 * @ORM\OneToOne(targetEntity="pageweb", inversedBy="background")
	 * @ORM\JoinColumn(nullable=true, unique=true, name="pageweb_id", referencedColumnName="id", onDelete="SET NULL")
	 */
	private $pagewebBackground;

	/**
	 * @Gedmo\Slug(fields={"nom"})
	 * @ORM\Column(length=128, unique=true)
	 */
	protected $slug;
	
	public $upload_file;
	
	/**
	 * @var int
	 * @ORM\Column(name="file_size", type="integer", length=10)
	 */
	private $fileSize;

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

	private $rawCodeFile;
	
	public function __construct() {
		
		$this->dateCreation = new DateTime();
		$this->dateMaj = null;
		$this->rawCodeFile = null;
		$date = new DateTime();
		$defaultVersion = $date->format('d-m-Y_H-i-s');
		$this->setNom($defaultVersion);
	}

	public function __toString(){
		return $this->name.' modifié le '.$this->updated->format('d-m-Y H:i:s');
	}

	/**
	 * @Assert\True(message="Le type de fichier n'est pas conforme.")
	 */
	public function isAuthorizedFileFormat() {
		return $this->format->getEnabled();
	}

	/**
	 * @ORM\PrePersist()
	 * @ORM\PreUpdate()
	 */
	public function upload(){
		if (null === $this->upload_file) return;
		
		$strm = fopen($this->upload_file->getRealPath(),'rb');
		$this->setBinaryFile(stream_get_contents($strm));
		$this->setFileSize(filesize($this->upload_file->getRealPath()));
		$this->setOriginalnom($this->upload_file->getClientOriginalName());
	}
	
	/**
	 * @ORM\PostLoad()
	 * @return string - code brut du fichier / null si aucun code
	 */
	public function getRawCodeFile(){
		if($this->rawCodeFile == null)
			$this->rawCodeFile = stream_get_contents($this->binaryFile);
		return $this->rawCodeFile;
	}

	/**
	 * Retourne le code en Base64 du fichier / null si aucun
	 * @return string
	 */
	public function getB64File(){
		if($this->rawCodeFile !== null) return base64_encode($this->getRawCodeFile());
			else return null;
	}

	/**
	 * Retourne un thumbnail du fichier / null si aucun
	 * @param integer $x - taille X
	 * @param integer $y - taille Y
	 * @param string $mode = 'cut'
	 * @return string
	 */
	public function getThumbnail($x = 256, $y = 256, $mode = 'cut') {
		$thumbnail = null;
		if($this->getFormat()->getType() == 'image') {
			$aeImages = new aeImages();
			// $codeImg = base64_decode($this->getB64File());
			$codeImg = $this->getRawCodeFile();
			$imageOrigin = @imagecreatefromstring($codeImg);
			if($imageOrigin != false) {
				// echo('Thumb file : '.$this->getId().'<br>');
				$image = $aeImages->thumb_image($imageOrigin, $x, $y, $mode);
				ob_start();
				imagepng($image);
				$thumbnail = ob_get_contents();
				ob_end_clean();
				imagedestroy($image);
			}
		}
		return $thumbnail;
	}

	/**
	 * Retourne un thumbnail en base64 du fichier / null si aucun
	 * @param integer $x - taille X
	 * @param integer $y - taille Y
	 * @param string $mode = 'cut'
	 * @return string
	 */
	public function getThumbnailB64($x = 256, $y = 256, $mode = 'cut') {
		$b64 = $this->getThumbnail($x, $y, $mode);
		return $b64 === null ? null : base64_encode($b64);
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
		return $this->getRawCodeFile();
	}

	/**
	 * Set pagewebBackground
	 * @param pageweb $pagewebBackground
	 * @return Version
	 */
	public function setPagewebBackground(pageweb $pagewebBackground = null) {
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
	 * Set nom
	 * @param string $nom
	 * @return Version
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
	 * @return Version
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
	public function setFormat(fileFormat $format = null) {
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
	 * Get first part of content-type (type)
	 * @return string
	 */
	public function getType(){
		return $this->getFormat()->getType();
	}

	/**
	 * is version an IMAGE type ?
	 * @return boolean
	 */
	public function isImage(){
		return $this->getFormat()->isImage();
	}

	/**
	 * is version a screenable IMAGE type ?
	 * @return boolean
	 */
	public function isScreenableImage(){
		return ($this->isImage() && ($this->getRawCodeFile() !== null));
	}

	/**
	 * is version a PDF type ?
	 * @return boolean
	 */
	public function isPdf(){
		return $this->getFormat()->isPdf();
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
