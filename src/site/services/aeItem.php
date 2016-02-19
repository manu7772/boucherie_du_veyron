<?php
namespace site\services;

use site\services\aeEntities;
use Symfony\Component\DependencyInjection\ContainerInterface;

use site\adminBundle\Entity\item;
use site\adminBundle\Entity\itemRepository;

// call in controller with $this->get('aetools.aeItem');
class aeItem extends aeEntities {

	protected $container;		// container
	protected $em;				// entity manager
	protected $repo;			// repository

	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
		$this->repo = $this->_em->getRepository('siteadminBundle:item');
	}

    /**
     * Check entity after change (edit…)
     * @param item $entity
     */
    public function checkAfterChange(item &$entity) {
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
	 * Persist en flush a item
	 * @param item $entity
	 * @return aeItem
	 */
	public function save(item &$entity) {
		$this->em->persist($entity);
		$this->em->flush();
		return $this;
	}

}