<?php
namespace site\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\services\aeMedia;

use site\adminBundle\Entity\image;
use site\adminBundle\Entity\imageRepository;
use site\adminBundle\Entity\media;
use site\adminBundle\Entity\mediaRepository;

// call in controller with $this->get('aetools.aeImage');
class aeImage extends aeMedia {

	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
		$this->repo = $this->em->getRepository('siteadminBundle:image');
	}

	/**
	 * Check entity after change (edit…)
	 * @param media $entity
	 */
	public function checkAfterChange(media &$entity) {
		parent::checkAfterChange($entity);
	}


}