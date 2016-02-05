<?php
namespace site\services;

use site\services\aeImages;

use site\adminBundle\Entity\media;
use site\adminBundle\Entity\mediaRepository;

// call in controller with $this->get('aetools.media');
class aeMedia extends aeImages {

	protected $container;		// container
	protected $em;				// entity manager
	protected $repo;			// repository

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
		$this->init();
	}

	public function init() {
		$this->em = $this->container->get('doctrine')->getManager();
		$this->repo = $this->em->getRepository('siteadminBundle:media');
	}

	/**
	 * Do some operations and optimisation after getting media from a form
	 * @param media $media
	 * @return media
	 */
	public function computeMediaData(media &$media, $persist = false) {
		//
		echo('<h3>Enregistrement media : '.$media->getNom().'</h3>');
		if($persist === true) $this->saveMedia($media);
		return $media;
	}

	/**
	 * Persist en flush a media
	 * @param media $media
	 * @return aeMedia
	 */
	public function saveMedia(media $media) {
		$this->em->persist($media);
		$this->em->flush();
		return $this;
	}

}