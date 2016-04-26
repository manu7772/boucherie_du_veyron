<?php

namespace site\adminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
// Slug
use Gedmo\Mapping\Annotation as Gedmo;

use site\adminBundle\Entity\baseSubEntity;

use site\adminBundle\services\aetools;
use site\adminBundle\services\aeDebug;
use \Exception;
use \DateTime;

/**
 * categorie
 *
 * @ORM\Entity
 * @ORM\Table(name="categorie")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="site\adminBundle\Entity\categorieRepository")
 */
class categorie extends baseSubEntity {

	const CLASS_CATEGORIE = 'categorie';

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(name="nom", type="string", length=64)
	 * @Assert\NotBlank(message = "Vous devez donner un nom à la catégorie.")
	 * @Assert\Length(
	 *      min = "2",
	 *      max = "64",
	 *      minMessage = "Le nom doit comporter au moins {{ limit }} lettres.",
	 *      maxMessage = "Le nom doit comporter au maximum {{ limit }} lettres."
	 * )
	 */
	protected $nom;

	/**
	 * @var integer
	 * @ORM\Column(name="lvl", type="integer", nullable=false, unique=false)
	 */
	protected $lvl;

	/**
	 * @ORM\ManyToOne(targetEntity="site\adminBundle\Entity\categorie", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true, unique=false, onDelete="SET NULL")
	 */
	protected $parent;

	/**
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\baseSubEntity", inversedBy="parents", cascade={"persist"})
	 * @ORM\JoinTable(name="parent_cat_child")
	 */
	protected $childrens;

	/**
	 * @ORM\ManyToMany(targetEntity="site\adminBundle\Entity\baseSubEntity", cascade={"persist"})
	 * @ORM\JoinTable(name="parent_cat_histchild")
	 */
	protected $historySubEntitys;

	/**
	 * type de catégorie
	 * @var string
	 * @ORM\Column(name="type", type="string", length=64, nullable=false, unique=false)
	 */
	protected $type;

	/**
	 * Classes d'entités accceptées pour subEntitys
	 * @var string
	 * @ORM\Column(name="accept", type="text", nullable=true, unique=false)
	 */
	protected $accepts;

	/**
	 * @var boolean
	 * @ORM\Column(name="open", type="boolean", nullable=false, unique=false)
	 */
	protected $open;

	// Liste des termes valides pour accept
	protected $accept_list;
	protected $type_description;
	protected $type_list;
	// propriétés calculées
	protected $subEntitys;

	public function __construct() {
		parent::__construct();
		$this->childrens = new ArrayCollection();
		$this->historySubEntitys = new ArrayCollection();
		$this->subEntitys = new ArrayCollection();
		$this->parent = null;
		$this->lvl = 0;
		$this->open = false;
		// init
		$this->type_list = null;
		$this->accept_list = null;
		$this->type_description = null;
		$this->init();
		$this->setType(array_keys($this->type_list)[0]);
	}

	/**
	 * @ORM\PostLoad
	 * 
	 * mémorise subEntitys
	 * @return categorie
	 */
	public function PostLoad() {
		$this->subEntitys = new ArrayCollection();
		foreach ($this->getChildrensOfAllTypes() as $child) {
			if(in_array($child->getClassName(), $this->getAccepts())) $this->addSubEntity($child);
			// if($child->getClassName() != self::CLASS_CATEGORIE) $this->addSubEntity($child);
		}
		return $this;
	}

	public function init() {
		// Description selon parameters (labo_parameters.yml)
		if(!is_array($this->accept_list) || !is_array($this->type_description) || !is_array($this->type_list)) {
			$aetools = new aetools();
			$description = $aetools->getLaboParam(self::CLASS_CATEGORIE);
			// variables…
			$this->accept_list = $description['descrition']['defaults']['accepts'];
			$this->type_description = $description['descrition']['types'];
			$this->type_list = array();
			foreach ($this->type_description as $key => $value) {
				$this->type_list[$key] = $value['nom'];
			}
		}
	}

	/**
	 * @Assert\IsTrue(message="La catégorie n'est pas conforme.")
	 */
	public function isCategorieValid() {
		$result = true;
		// if($this->getType() == null) $result = false;
		return $result;
	}

	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 * 
	 * Check categorie
	 * @return array
	 */
	public function check() {
		// $this->init();
		// parent
		parent::check();
	}

	/**
	 * Un élément par défaut dans la table est-il obligatoire ?
	 * @return boolean
	 */
	public function isDefaultNullable() {
		return true;
	}

	/**
	 * Set icon
	 * @param string $icon
	 * @return baseEntity
	 */
	public function setIcon($icon = null) {
		if($this->getLvl() > 0) {
			if(trim($icon) == '') $icon = null;
			$this->icon = trim($icon);
		}
		return $this;
	}

	/**
	 * Add parent 
	 * @param categorie $parent
	 * @return categorie
	 */
	public function addParent(categorie $parent) {
		if($this->getParent() == null) $this->setParent($parent);
		parent::addParent($parent);
		return $this;
	}

	/**
	 * Get parents
	 * @return array
	 */
	public function getParents($includeThis = false) {
		$parents = array();
		if($includeThis) $parents = array($this);
		if($this->parent != null) $parents = array_merge($parents, $this->parent->getParents(true));
		return $parents;
	}

	/**
	 * Renvoie les parents récursifs de l'entité en tableau inversé (contraire de getParents())
	 * Inclut ou non l'entité elle-même dans la liste. Le premier élément du tableau est le parent root, et ainsi de suite. Le dernier élément est le premier parent direct.
	 * @param boolean $includeThis = false
	 * @return array
	 */
	public function getParentsInverse($includeThis = false) {
		return array_reverse($this->getParents($includeThis));
	}

	/**
	 * Renvoie le parent ROOT ($level = 0) ou de niveau $level. 
	 * NULL si aucun parent n'a été trouvé. 
	 * @param integer $level = 0
	 * @return categorie
	 */
	public function getRootParent($level = 0) {
		$parents = $this->getParentsInverse(false);
		if(count($parents) >= ($level + 1)) return $parents[$level];
		return null;
	}

	/**
	 * Remove parent
	 * @param categorie $parent
	 * @return boolean
	 */
	public function removeParent(categorie $parent) {
		if($parent === $this->getParent()) $this->setParent(null);
		return parent::removeParent($parent);
	}

	/**
	 * @ORM\PreRemove
	 * Remove all parents
	 * @return categorie
	 */
	public function removeParents() {
		$this->setParent(null);
		return parent::removeParents();
	}

	/**
	 * Set parent
	 * @param categorie $categorie = null
	 * @return categorie
	 */
	public function setParent(categorie $categorie = null) {
		if($this->parent != $categorie) {
			if($this->parent != null) $this->parent->removeChildren($this);
			if($categorie != null) $categorie->addChildren($this);
			$this->parent = $categorie;
			if($categorie != null) {
				if($this->getType() != $categorie->getType()) $this->setType($categorie->getType());
			}
			$this->setLvl();
		}
		return $this;
	}

	/**
	 * Get parent
	 * @return categorie
	 */
	public function getParent() {
		return $this->parent;
	}

	public function getChildrens() {
		$childrens = array();
		$allChildren = $this->childrens->toArray();
		foreach($allChildren as $children) {
			if($children->getClassName() == self::CLASS_CATEGORIE) $childrens[] = $children;
		}
		return $childrens;
	}

	public function getChildrensOfAllTypes() {
		return $this->childrens;
	}

	public function getAllChildrens() {
		$allChildren = $this->getChildrens();
		foreach($allChildren as $children) {
			$allChildren = array_merge($allChildren, $children->getAllChildrens());
		}
		return array_unique($allChildren);
	}

	public function getAllChildrensOfAllTypes() {
		$allChildren = $this->getChildrensOfAllTypes()->toArray();
		foreach($allChildren as $children) {
			if(method_exists($children, 'getAllChildrensOfAllTypes')) $allChildren = array_merge($allChildren, $children->getAllChildrensOfAllTypes());
		}
		return array_unique($allChildren);
	}

	public function addChildren(baseSubEntity $children) {
		if(!$this->childrens->contains($children)) $this->childrens->add($children);
		$this->addSubEntity($children, false);
		return $this;
	}

	public function removeChildren(baseSubEntity $children) {
		$remove = $this->childrens->removeElement($children);
		if($remove) {
			$this->subEntitys->removeElement($children);
			$this->removeHistorySubEntity($children);
		}
		return $remove;
	}




	// public function setSubEntitys($array) {
	// 	$this->subEntitys = new ArrayCollection($array);
	// 	return $this;
	// }

	/**
	 * Get only baseSubEntity class children (but category class). 
	 * Can define witch classes in $classes array of shortnames (or one class in a string shortname). 
	 * @param mixed $classes = []
	 * @return array
	 */
	public function getSubEntitys($classes = []) {
		if(is_string($classes)) $classes = array($classes);
		if(!is_array($classes)) $classes = array();
		if(count($classes) < 1) $classes = $this->getAccepts();
		if(in_array(self::CLASS_CATEGORIE, $classes)) unset($classes[self::CLASS_CATEGORIE]);
		$subs = array();
		foreach ($this->subEntitys->toArray() as $key => $sub) {
			if(in_array($sub->getClassName(), $classes)) $subs[] = $sub;
		}
		// return array_unique($subs);
		return array_unique($subs);
	}

	public function getAllSubEntitys($classes = []) {
		$allSubEntitys = $this->getSubEntitys($classes);
		$children = $this->getChildrens();
		if(is_array($children)) foreach ($children as $child) {
			$allSubEntitys = array_merge($allSubEntitys, $child->getAllSubEntitys($classes));
		}
		// return array_unique($allSubEntitys);
		return array_unique($allSubEntitys);
	}

	public function addSubEntity(baseSubEntity $subEntity, $addAsChildren = true) {
		if(in_array($subEntity->getClassName(), $this->getAccepts())) {
			if(!$this->subEntitys->contains($subEntity)) $this->subEntitys->add($subEntity);
			if($addAsChildren) $this->addChildren($subEntity);
			$this->addHistorySubEntity($subEntity);
		}
		return $this;
	}

	public function removeSubEntity(baseSubEntity $subEntity) {
		return $this->removeChildren($subEntity);
	}





	public function getHistorySubEntitys() {
		return $this->historySubEntitys;
	}

	public function addHistorySubEntity(baseSubEntity $subEntity) {
		if(!$this->historySubEntitys->contains($subEntity)) $this->historySubEntitys->add($subEntity);
	}

	public function removeHistorySubEntity(baseSubEntity $subEntity) {
		$remove = false;
		if(in_array($subEntity->getClassName(), $this->getAccepts())) {
			$remove = $this->historySubEntitys->removeElement($subEntity);
		}
		return $remove;
	}

	public function retrieveHistorySubEntitys() {
		foreach ($this->getHistorySubEntitys() as $subEntity) {
			if(in_array($subEntity->getClassName(), $this->getAccepts())) {
				$this->addSubEntity($subEntity);
			}
		}
	}




	/**
	 * Set Level
	 * @return categorie
	 */
	public function setLvl() {
		$debug = new aeDebug();
		$db[$this->getSlug()]['Entity']['id'] = $this->getId();
		if($this->getParent() != null) $db[$this->getSlug()]['Entity']['parent'] = $this->getParent()->getSlug();
			else $db[$this->getSlug()]['Entity']['parent'] = json_encode(null);
		$db[$this->getSlug()]['Entity']['parents']['count'] = count($this->getParents());
		$db[$this->getSlug()]['Entity']['parents']['names'] = implode(' > ', $this->getParentsInverse());
		$db[$this->getSlug()]['Level']['old'] = json_encode($this->lvl);
		$this->lvl = count($this->getParents());
		$db[$this->getSlug()]['Level']['new'] = json_encode($this->lvl);
		if($this->lvl == 0) $this->open();
		// ajoute le type à ses categories enfants
		foreach ($this->getAllChildrens() as $child) {
			$child->setLvl();
		}
		$debug->debugNamedFile('testLvl', $db);
		return $this;
	}

	/**
	 * Get Level
	 * @return integer
	 */
	public function getLvl() {
		return $this->lvl;
	}

	public function getAcceptsList() {
		if(!is_array($this->accept_list)) $this->init();
		return $this->accept_list;
	}

	/**
	 * Renvoie la liste des types de contenu de la catégorie disponibles
	 * @return array
	 */
	public function getTypeList() {
		if(!is_array($this->type_list)) $this->init();
		return $this->type_list;
	}

	/**
	 * Renvoie le type de contenu de la catégorie
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Set type de contenu de la catégorie
	 * @param string $type
	 * @return categorie
	 */
	public function setType($type) {
		// ajoute le type du parent en priorité
		if($this->getParent() != null) {
			$this->type = $this->getParent()->getType();
		} else if(array_key_exists($type, $this->getTypeList())) {
			$this->type = $type;
		} else {
			throw new Exception("Ce type n'existe pas : ".json_encode($type), 1);
		}
		// ajoute le type à ses categories enfants
		foreach ($this->getAllChildrens() as $child) {
			$child->setType($this->type);
		}
		// refresh accepts
		$this->setAccepts();
		// suppression des subEntitys hors type / hors accept
		foreach($this->getSubEntitys($this->getNotAccepts()) as $subEntity) {
			$subEntity->removeParent($this);
		}
		// retrouve les éléments depuis historySubEntitys
		$this->retrieveHistorySubEntitys();
		return $this;
	}

	/**
	 * set accepts
	 * @param json/array $accepts = null
	 * @return categorie
	 */
	public function setAccepts() {
		if(!is_array($this->type_description)) $this->init();
		if($this->getType() != null) $this->accepts = json_encode($this->type_description[$this->getType()]['accepts']);
			else $this->accepts = json_encode(array());
		return $this;
	}

	/**
	 * has accept
	 * @return boolean
	 */
	public function hasAccept($accept) {
		$accepts = $this->getAccepts();
		return in_array($accept, $accepts);
	}

	/**
	 * get accepts
	 * @return array
	 */
	public function getAccepts() {
		if(!is_string($this->accepts)) return array();
		return json_decode($this->accepts);
	}

	/**
	 * get not accepts
	 * @return array
	 */
	public function getNotAccepts() {
		return array_diff($this->getAcceptsList(), $this->getAccepts());
	}

	/**
	 * Set open
	 * @return categorie 
	 */
	public function setOpen($open = true) {
		$this->open = (bool) $open;
		return $this;
	}

	/**
	 * Get open
	 * @return boolean 
	 */
	public function getOpen() {
		return $this->open;
	}

	/**
	 * Get open as text for JStree
	 * @return string
	 */
	public function getOpenText() {
		return $this->open ? 'open' : 'closed';
	}

	/**
	 * Toggle open
	 * @return boolean 
	 */
	public function toggleOpen() {
		$this->open = !$this->open;
		return $this->open;
	}

	/**
	 * Set open to TRUE
	 * @return categorie 
	 */
	public function open() {
		$this->open = true;
		return $this;
	}

	/**
	 * Set open to FALSE
	 * @return categorie 
	 */
	public function close() {
		$this->open = false;
		return $this;
	}


}
