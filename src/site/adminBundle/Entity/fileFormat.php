<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * fileFormat
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class fileFormat {
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
     * @ORM\Column(name="icon", type="string", length=24)
     */
    private $icon;
    
    /**
     * @var string
     * @ORM\Column(name="content_type", type="string", length=255)
     */
    private $contentType;

    /**
     * @var boolean
     * @ORM\Column(name="enabled", type="boolean", nullable=false, unique=false)
     */
    private $enabled;

    private $typeIcons;

    public function __construct() {
        // $this->getTypeIcons();
        $this->icon = $this->getDefaultIcon();
        $this->enabled = false;
    }
    
    public function __toString(){
        return $this->nom;
    }

    /**
     * Get id
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nom
     * @param string $nom
     * @return fileFormat
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
     * Set enabled
     * @param boolean $enabled
     * @return fileFormat
     */
    public function setEnabled($enabled = true) {
        if(!is_bool($enabled)) $enabled = false;
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * Get enabled
     * @return boolean
     */
    public function getEnabled() {
        return $this->enabled;
    }

    /**
     * Set icon
     * @param string $icon
     * @return fileFormat
     */
    public function setIcon($icon) {
        $icons = $this->getTypeIcons();
        if(array_key_exists($icon, $icons)) $this->icon = $icon;
        return $this;
    }

    /**
     * Get icon
     * @return string
     */
    public function getIcon() {
        return $this->icon;
    }

    /**
     * Get types of icons
     * @return array
     */
    public function getTypeIcons() {
        $typeIcons = array('fa-file-o', 'fa-file-text-o', 'fa-file-archive-o', 'fa-file-audio-o', 'fa-file-code-o', 'fa-file-excel-o', 'fa-file-image-o', 'fa-file-movie-o', 'fa-file-pdf-o', 'fa-file-photo-o', 'fa-file-picture-o', 'fa-file-powerpoint-o', 'fa-file-sound-o', 'fa-file-video-o', 'fa-file-word-o', 'fa-file-zip-o');
        foreach ($typeIcons as $key => $value) {
            $iconCode = '<i class="fa '.$value.' fa-2x"></i> ';
            $this->typeIcons[$value] = $iconCode.str_replace(array('fa-','-o'), '', $value);
        }
        return $this->typeIcons;
    }

    /**
     * Get types of icons
     * @return array
     */
    public function getDefaultIcon() {
        $icons = $this->getTypeIcons();
        reset($icons);
        return key($icons);
    }

    /**
     * Set contentType
     * @param string $contentType
     * @return fileFormat
     */
    public function setContentType($contentType) {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Get contentType
     * @return string
     */
    public function getContentType() {
        return $this->contentType;
    }
    
    /**
     * Get first part of content-type (type)
     * @return string
     */
    public function getType(){
        return explode('/', $this->getContentType())[0];
    }
    
    /**
     * is a IMAGE type ?
     * @return boolean
     */
    public function isImage(){
        return strtolower($this->getType()) == "image";
    }
 
    /**
     * is a PDF type ?
     * @return boolean
     */
    public function isPdf(){
        $isPdf = explode('/', $this->getContentType())[1];
        return strtolower($isPdf) == "pdf";
    }
    
}
