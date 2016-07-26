<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use site\adminBundle\Entity\subentity;
use site\adminBundle\Entity\categorie;
use site\adminBundle\Entity\boutique;
use site\adminBundle\Entity\image;
use site\UserBundle\Entity\User;

use \ReflectionClass;
use \DateTime;
use \Exception;

/**
 * site
 *
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\siteRepository")
 * @ORM\Table(name="site")
 * @ORM\HasLifecycleCallbacks
 */
class site extends subentity {

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(name="nom", type="string", length=128, nullable=false, unique=false)
	 * @Assert\NotBlank(message = "Vous devez remplir ce champ.")
	 * @Assert\Length(
	 *      min = "6",
	 *      max = "128",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;

	/**
	 * @var string
	 * @ORM\Column(name="accroche", type="string", length=60, nullable=true, unique=false)
	 * @Assert\Length(
	 *      max = "60",
	 *      maxMessage = "L'accroche doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $accroche;

	/**
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\categorie", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $menuNav;

	/**
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\categorie", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $menuArticle;

	/**
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\categorie", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $diaporamaintro;

	/**
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\categorie")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 * @ORM\JoinTable(name="site_cat_article")
	 */
	protected $categorieArticles;

	/**
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\categorie")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 * @ORM\JoinTable(name="site_cat_footer")
	 */
	protected $categorieFooters;

	/**
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\boutique", inversedBy="sites", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $boutiques;

	/**
	 * @ORM\ManyToMany(targetEntity="site\UserBundle\Entity\User", inversedBy="sites")
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $collaborateurs;

    /**
     * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\image", orphanRemoval=true, cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
     */
    protected $logo;

    /**
     * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\image", orphanRemoval=true, cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
     */
    protected $favicon;

    /**
     * @ORM\OneToOne(targetEntity="site\adminBundle\Entity\image", orphanRemoval=true, cascade={"all"})
	 * @ORM\JoinColumn(nullable=true, unique=true, onDelete="SET NULL")
     */
    protected $adminLogo;


	public function __construct() {
		parent::__construct();
		$this->accroche = null;
		$this->menuNav = null;
		$this->menuArticle = null;
		$this->diaporamaintro = null;
		$this->categorieArticles = new ArrayCollection();
		$this->categorieFooters = new ArrayCollection();
		$this->boutiques = new ArrayCollection();
		$this->collaborateurs = new ArrayCollection();
		$this->logo = null;
		$this->favicon = null;
		$this->adminLogo = null;
	}

	// public function memOldValues($addedfields = null) {
	// 	$fields = array('boutiques', 'collaborateurs');
	// 	if(count($addedfields) > 0 && is_array($addedfields)) $fields = array_unique(array_merge($fields, $addedfields));
	// 	parent::memOldValues($fields);
	// 	return $this;
	// }

    // abstract public function getClassName();
    public function getClassName() {
        return $this->getClass(true);
    }

	/**
	 * Renvoie l'image principale
	 * @return image
	 */
	public function getMainMedia() {
		if($this->getLogo() !== null) return $this->getLogo();
		if($this->getImage() !== null) return $this->getImage();
		if($this->getFavicon() !== null) return $this->getFavicon();
		return null;
	}

	/**
	 * Renvoie le nom de la classe (short name par défaut)
	 * @param boolean $short = false
	 * @return string
	 */
	public function getClass($short = false) {
		$class = new ReflectionClass(get_called_class());
		return $short ?
			$class->getShortName():
			$class->getName();
	}

	/**
	 * Set accroche
	 * @param string $accroche
	 * @return site
	 */
	public function setAccroche($accroche = null) {
		$this->accroche = $accroche;
		return $this;
	}

	/**
	 * Get accroche
	 * @return string 
	 */
	public function getAccroche() {
		return $this->accroche;
	}

	/**
	 * set menuNav
	 * @param categorie $menuNav
	 * @return site
	 */
	public function setMenuNav(categorie $menuNav = null) {
		$this->menuNav = $menuNav;
		return $this;
	}

	/**
	 * get menuNav
	 * @return categorie
	 */
	public function getMenuNav() {
		return $this->menuNav;
	}

	/**
	 * set menuArticle
	 * @param categorie $menuArticle
	 * @return site
	 */
	public function setMenuArticle(categorie $menuArticle = null) {
		$this->menuArticle = $menuArticle;
		return $this;
	}

	/**
	 * get menuArticle
	 * @return categorie
	 */
	public function getMenuArticle() {
		return $this->menuArticle;
	}

	/**
	 * set diaporamaintro
	 * @param categorie $diaporamaintro
	 * @return site
	 */
	public function setDiaporamaintro(categorie $diaporamaintro = null) {
		$this->diaporamaintro = $diaporamaintro;
		return $this;
	}

	/**
	 * get diaporamaintro
	 * @return categorie
	 */
	public function getDiaporamaintro() {
		return $this->diaporamaintro;
	}

	/**
	 * Set categorieArticles
	 * @param arrayCollection $categorieArticles
	 * @return subentity
	 */
	public function setCategorieArticles(ArrayCollection $categorieArticles) {
		// $this->categorieArticles->clear();
		// incorporation avec "add" et "remove" au cas où il y aurait des opérations (inverse notamment)
		foreach ($this->getCategorieArticles() as $categorieArticle) if(!$categorieArticles->contains($categorieArticle)) $this->removeCategorieArticle($categorieArticle); // remove
		foreach ($categorieArticles as $categorieArticle) $this->addCategorieArticle($categorieArticle); // add
		return $this;
	}

	/**
	 * Add categorieArticle
	 * @param categorie $categorieArticle
	 * @return site
	 */
	public function addCategorieArticle(categorie $categorieArticle) {
		$this->categorieArticles->add($categorieArticle);
		return $this;
	}

	/**
	 * Remove categorieArticle
	 * @param categorie $categorieArticle
	 * @return boolean
	 */
	public function removeCategorieArticle(categorie $categorieArticle) {
		return $this->categorieArticles->removeElement($categorieArticle);
	}

	/**
	 * Get categorieArticles
	 * @return ArrayCollection
	 */
	public function getCategorieArticles() {
		return $this->categorieArticles;
	}

	/**
	 * Set categorieFooters
	 * @param arrayCollection $categorieFooters
	 * @return subentity
	 */
	public function setCategorieFooters(ArrayCollection $categorieFooters) {
		// $this->categorieFooters->clear();
		// incorporation avec "add" et "remove" au cas où il y aurait des opérations (inverse notamment)
		foreach ($this->getCategorieFooters() as $categorieFooter) if(!$categorieFooters->contains($categorieFooter)) $this->removeCategorieFooter($categorieFooter); // remove
		foreach ($categorieFooters as $categorieFooter) $this->addCategorieFooter($categorieFooter); // add
		return $this;
	}

	/**
	 * Add categorieFooter
	 * @param categorie $categorieFooter
	 * @return site
	 */
	public function addCategorieFooter(categorie $categorieFooter) {
		$this->categorieFooters->add($categorieFooter);
		return $this;
	}

	/**
	 * Remove categorieFooter
	 * @param categorie $categorieFooter
	 * @return boolean
	 */
	public function removeCategorieFooter(categorie $categorieFooter) {
		return $this->categorieFooters->removeElement($categorieFooter);
	}

	/**
	 * Get categorieFooters
	 * @return ArrayCollection
	 */
	public function getCategorieFooters() {
		return $this->categorieFooters;
	}

	/**
	 * Set boutiques
	 * @param arrayCollection $boutiques
	 * @return subentity
	 */
	public function setBoutiques(ArrayCollection $boutiques) {
		// $this->boutiques->clear();
		// incorporation avec "add" et "remove" au cas où il y aurait des opérations (inverse notamment)
		foreach ($this->getBoutiques() as $boutique) if(!$boutiques->contains($boutique)) $this->removeBoutique($boutique); // remove
		foreach ($boutiques as $boutique) $this->addBoutique($boutique); // add
		return $this;
	}

	/**
	 * Add boutique
	 * @param boutique $boutique
	 * @return site
	 */
	public function addBoutique(boutique $boutique) {
		$this->boutiques->add($boutique);
		// $boutique->addSite($this);
		return $this;
	}

	/**
	 * Remove boutique
	 * @param boutique $boutique
	 * @return boolean
	 */
	public function removeBoutique(boutique $boutique) {
		// $boutique->removeSite($this);
		return $this->boutiques->removeElement($boutique);
	}

	/**
	 * Get boutiques
	 * @return ArrayCollection
	 */
	public function getBoutiques() {
		return $this->boutiques;
	}

	/**
	 * Set collaborateurs
	 * @param arrayCollection $collaborateurs
	 * @return subentity
	 */
	public function setCollaborateurs(ArrayCollection $collaborateurs) {
		// $this->collaborateurs->clear();
		// incorporation avec "add" et "remove" au cas où il y aurait des opérations (inverse notamment)
		foreach ($this->getCollaborateurs() as $collaborateur) if(!$collaborateurs->contains($collaborateur)) $this->removeCollaborateur($collaborateur); // remove
		foreach ($collaborateurs as $collaborateur) $this->addCollaborateur($collaborateur); // add
		return $this;
	}

	/**
	 * Add collaborateur
	 * @param User $collaborateur
	 * @return site
	 */
	public function addCollaborateur(User $collaborateur) {
		$this->collaborateurs->add($collaborateur);
		// $collaborateur->addSite($this);
		return $this;
	}

	/**
	 * Remove collaborateur
	 * @param User $collaborateur
	 * @return boolean
	 */
	public function removeCollaborateur(User $collaborateur) {
		// $collaborateur->removeSite($this);
		return $this->collaborateurs->removeElement($collaborateur);
	}

	/**
	 * Get collaborateurs
	 * @return ArrayCollection
	 */
	public function getCollaborateurs() {
		return $this->collaborateurs;
	}

	/**
	 * Set image
	 * @param image $image
	 * @return site
	 */
	public function setImage(image $image = null) {
		$this->image = $image;
		if($image != null) $image->setOwner('site:image');
		return $this;
	}

	/**
	 * Set logo
	 * @param image $logo
	 * @return site
	 */
	public function setLogo(image $logo = null) {
		$this->logo = $logo;
		if($logo != null) $logo->setOwner('site:logo');
		return $this;
	}

	/**
	 * Get logo
	 * @return image $logo
	 */
	public function getLogo() {
		return $this->logo;
	}

	/**
	 * Set favicon
	 * @param image $favicon
	 * @return site
	 */
	public function setFavicon(image $favicon = null) {
		$this->favicon = $favicon;
		if($favicon != null) $favicon->setOwner('site:favicon');
		return $this;
	}

	/**
	 * Get favicon
	 * @return image $favicon
	 */
	public function getFavicon() {
		return $this->favicon;
	}

	/**
	 * Set adminLogo
	 * @param image $adminLogo
	 * @return site
	 */
	public function setAdminLogo(image $adminLogo = null) {
		$this->adminLogo = $adminLogo;
		if($adminLogo != null) $adminLogo->setOwner('site:adminLogo');
		return $this;
	}

	/**
	 * Get adminLogo
	 * @return image $adminLogo
	 */
	public function getAdminLogo() {
		return $this->adminLogo;
	}




}
