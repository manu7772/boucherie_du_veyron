<?php
namespace site\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\services\aeMedia;

use site\adminBundle\Entity\pdf;
use site\adminBundle\Entity\pdfRepository;
use site\adminBundle\Entity\media;
use site\adminBundle\Entity\mediaRepository;

// call in controller with $this->get('aetools.aePdf');
class aePdf extends aeMedia {

	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
		$this->repo = $this->_em->getRepository('siteadminBundle:pdf');
	}

	/**
	 * Check entity after change (edit…)
	 * @param media $entity
	 */
	public function checkAfterChange(media &$entity) {
		parent::checkAfterChange($entity);
	}


}