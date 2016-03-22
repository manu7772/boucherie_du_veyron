<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeMedia;

use site\adminBundle\Entity\pdf;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aePdf');
class aePdf extends aeMedia {

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->defineEntity('site\adminBundle\Entity\pdf');
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aePdf
     */
    public function checkAfterChange(baseEntity &$entity) {
        parent::checkAfterChange($entity);
        return $this;
    }

    /**
     * Persist and flush a pdf
     * @param baseEntity $entity
     * @return aeReponse
     */
    // public function save(baseEntity &$entity, $flush = true) {
    //  return parent::save($entity, $flush);
    // }


}