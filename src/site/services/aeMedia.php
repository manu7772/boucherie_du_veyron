<?php
namespace site\services;

use Symfony\Component\DependencyInjection\ContainerInterface;

use site\services\aeImages;

use site\adminBundle\Entity\media;
use site\adminBundle\Entity\mediaRepository;

// call in controller with $this->get('aetools.media');
class aeMedia extends aeImages {

	protected $container;		// container
	protected $_em;				// entity manager
	protected $repo;			// repository
	protected $entitiesService;	// service entities

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
		$this->init();
	}

	public function init() {
		$this->entitiesService = $this->container->get('aetools.aeEntities');
		$this->_em = $this->container->get('doctrine')->getManager();
		$this->repo = $this->_em->getRepository('siteadminBundle:media');
	}

	/**
	 * Check entity after change (edit…)
	 * @param media $entity
	 */
	public function checkAfterChange(media &$entity) {
	    // 
	    $this->entitiesService->checkStatuts($entity, false);
	    $this->entitiesService->checkInversedLinks($entity, false);
	}

	/**
	 * Persist en flush a media
	 * @param media $media
	 * @return aeMedia
	 */
	public function saveMedia(media &$entity) {
		$this->_em->persist($entity);
		$this->_em->flush();
		return $this;
	}

}