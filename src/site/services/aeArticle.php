<?php
namespace site\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\services\aeItem;

use site\adminBundle\Entity\article;
use site\adminBundle\Entity\articleRepository;
use site\adminBundle\Entity\item;
use site\adminBundle\Entity\itemRepository;

// call in controller with $this->get('aetools.aeArticle');
class aeArticle extends aeItem {

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->repo = $this->em->getRepository('siteadminBundle:article');
    }

    /**
     * Check entity after change (edit…)
     * @param item $entity
     */
    public function checkAfterChange(item &$entity) {
        parent::checkAfterChange($entity);
    }


}