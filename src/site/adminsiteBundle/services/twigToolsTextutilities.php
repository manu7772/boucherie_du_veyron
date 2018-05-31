<?php
namespace site\adminsiteBundle\services;

use Labo\Bundle\AdminBundle\services\aeData;
use Labo\Bundle\AdminBundle\services\aeImages;
use JMS\Serializer\SerializationContext;

use Symfony\Component\DependencyInjection\ContainerInterface;
use \Twig_Extension;
use \Twig_SimpleFilter;
use \Twig_SimpleFunction;
use Labo\Bundle\AdminBundle\services\twigToolsTextutilities as baseTwigToolsTextutilities;

use site\adminsiteBundle\Entity\article;

use \DateTime;

class twigToolsTextutilities extends baseTwigToolsTextutilities {

    // const NAME                  = 'twigToolsTextutilities';			// nom du service
    // const CALL_NAME             = 'aetools.twigToolsTextutilities';	// comment appeler le service depuis le controller/container

	// const PATH_CUT				= 'src/';			// découpage path sur /src

	private $container;
	private $trans;
	private $session;
	private $authorization_checker;

	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
		$this->container = $container;
		$this->trans = $this->container->get('translator');
		$this->session = $this->container->get('request')->getSession();
		$this->authorization_checker = $this->container->get('security.authorization_checker');
	}

	public function getFunctions() {
		return array_merge(parent::getFunctions(), array(
			new Twig_SimpleFunction('sitedata', array($this, 'sitedata')),
			new Twig_SimpleFunction('isScreenable', array($this, 'isScreenable')),
			));
	}

	public function getFilters() {
		return array_merge(parent::getFilters(), array(
			// new Twig_SimpleFilter('base64_encode', array($this, 'base64_encode')),
			));
	}





	public function sitedata() {
		return $this->container->get('aetools.aeServiceSite')->getSiteData();
	}

	/**
	 * Un item est affichable ou non (ROLE, etc.)
	 * @param Object $item
	 * @return boolean
	 */
	public function isScreenable($item) {
		if(is_object($item)) {
			// Statut
			if(method_exists($item, 'getStatut')) {
				$role = $item->getStatut()->getNiveau();
				if(!$this->authorization_checker->isGranted($role)) return false;
			}
			// articles
			if($item instanceOf article) {
				$sitedata = $this->container->get('aetools.aeServiceSite')->getSiteData();
				if(isset($sitedata['optionArticlePhotosOnly'])) {
					// article without image
					if($sitedata['optionArticlePhotosOnly'] === true && $item->getImage() === null) return false;
				}
				if(isset($sitedata['optionArticlePriceOnly'])) {
					// article without price
					if($sitedata['optionArticlePriceOnly'] === true && (integer)$item->getPrix() === 0 && $item->getSurdevis() !== true) return false;
				}
			}
		}
		return true;
	}

	/**
	 * renvoie un tableau randomisé (avec max éléments si $max non null)
	 * @param string $t - texte
	 * @param intger $n - nombre d'espaces à supprimer (à partir de 2, par défaut)
	 * @return string
	 */
	public function randomArray($array, $max = null) {
		$array = (array)$array;
		foreach ($array as $key => $item) {
			if(!$this->isScreenable($item)) unset($array[$key]);
		}
		if(count($array) < 1) return $array;
		shuffle($array);
		return (integer)$max > 0 ? array_slice($array, 0, (integer)$max) : $array;
	}


}








