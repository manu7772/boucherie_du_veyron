<?php
namespace site\services;

use site\services\aeEntities;
use Symfony\Component\DependencyInjection\ContainerInterface;

use site\adminBundle\Entity\tier;
use site\adminBundle\Entity\tierRepository;

// call in controller with $this->get('aetools.aeTier');
class aeTier extends aeEntities {

	protected $container;		// container
	protected $em;				// entity manager
	protected $repo;			// repository

	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
		$this->repo = $this->_em->getRepository('siteadminBundle:tier');
	}

    /**
     * Check entity after change (edit…)
     * @param tier $entity
     */
    public function checkAfterChange(tier &$entity) {
	    // check image
		if($entity->getImage() !== null) {
			$image = $entity->getImage();
			$this->checkStatuts($image, false);
		}
	    // check entity
	    $this->checkStatuts($entity, false);
	    $this->checkInversedLinks($entity, false);
	}

	/**
	 * Persist en flush a tier
	 * @param tier $entity
	 * @return aeTier
	 */
	public function save(tier &$entity) {
		$this->em->persist($entity);
		$this->em->flush();
		return $this;
	}

}