<?php
namespace site\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\services\aeItem;

use site\adminBundle\Entity\fiche;
use site\adminBundle\Entity\ficheRepository;
use site\adminBundle\Entity\item;
use site\adminBundle\Entity\itemRepository;

// call in controller with $this->get('aetools.aeFiche');
class aeFiche extends aeItem {

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->repo = $this->_em->getRepository('siteadminBundle:fiche');
    }

    /**
     * Check entity after change (edit…)
     * @param item $entity
     */
    public function checkAfterChange(item &$entity) {
        parent::checkAfterChange($entity);
    }


}