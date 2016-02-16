<?php
namespace site\services;

use Symfony\Component\DependencyInjection\ContainerInterface;

use site\adminBundle\Entity\item;
use site\adminBundle\Entity\itemRepository;

// call in controller with $this->get('aetools.aeItem');
class aeItem {

	protected $container;		// container
	protected $em;				// entity manager
	protected $repo;			// repository
	protected $entitiesService;	// service entities

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
		$this->init();
	}

	public function init() {
		$this->entitiesService = $this->container->get('aetools.aeEntities');
		$this->em = $this->container->get('doctrine')->getManager();
		$this->repo = $this->em->getRepository('siteadminBundle:item');
	}

    /**
     * Check entity after change (edit…)
     * @param item $entity
     */
    public function checkAfterChange(item &$entity) {
	    // check image
		if($entity->getImage() !== null) {
			$image = $entity->getImage();
			$this->entitiesService->checkStatuts($image, false);
		}
	    // check entity
	    $this->entitiesService->checkStatuts($entity, false);
	    $this->entitiesService->checkInversedLinks($entity, false);
	}

	/**
	 * Persist en flush a item
	 * @param item $entity
	 * @return aeItem
	 */
	public function saveItem(item &$entity) {
		$this->em->persist($entity);
		$this->em->flush();
		return $this;
	}

}