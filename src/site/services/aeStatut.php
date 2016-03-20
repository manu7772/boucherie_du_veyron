<?php
namespace site\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\services\aeEntity;

use site\adminBundle\Entity\statut;
use site\adminBundle\Entity\baseEntity;
use site\adminBundle\Entity\baseSubEntity;

// call in controller with $this->get('aetools.aeStatut');
class aeStatut extends aeEntity {

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->defineEntity('site\adminBundle\Entity\statut');
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeStatut
     */
    public function checkAfterChange(baseEntity &$entity) {
        parent::checkAfterChange($entity);
        return $this;
    }

    /**
     * Persist and flush a statut
     * @param baseEntity $entity
     * @return aeReponse
     */
    // public function save(baseEntity &$entity) {
    //     return parent::save($entity);
    // }

    /**
     * Passe le baseSubEntity en mode temps
     * @param baseSubEntity &$entity
     * @param string $method = 'setStatut'
     * @return aeStatut
     */
    public function setTemp(baseSubEntity &$entity, $method = 'setStatut') {
        if(method_exists($entity, $method)) {
            $temp = $this->getRepo()->findTemp();
            $entity->$method($temp);
        }
        return $this;
    }

    /**
     * Passe le baseSubEntity en mode actif
     * @param baseSubEntity &$entity
     * @param string $method = 'setStatut'
     * @return aeStatut
     */
    public function setActif(baseSubEntity &$entity, $method = 'setStatut') {
        if(method_exists($entity, $method)) {
            $actif = $this->getRepo()->findActif();
            $entity->$method($actif);
        }
        return $this;
    }

    /**
     * Passe le baseSubEntity en mode inactif
     * @param baseSubEntity &$entity
     * @param string $method = 'setStatut'
     * @return aeStatut
     */
    public function setInactif(baseSubEntity &$entity, $method = 'setStatut') {
        if(method_exists($entity, $method)) {
            $inactif = $this->getRepo()->findInactif();
            $entity->$method($inactif);
        }
        return $this;
    }

    /**
     * Passe le baseSubEntity en mode expired
     * @param baseSubEntity &$entity
     * @param string $method = 'setStatut'
     * @return aeStatut
     */
    public function setExpired(baseSubEntity &$entity, $method = 'setStatut') {
        if(method_exists($entity, $method)) {
            $expired = $this->getRepo()->findExpired();
            $entity->$method($expired);
        }
        return $this;
    }

    /**
     * Passe le baseSubEntity en mode webmaster
     * @param baseSubEntity &$entity
     * @param string $method = 'setStatut'
     * @return aeStatut
     */
    public function setWebmaster(baseSubEntity &$entity, $method = 'setStatut') {
        if(method_exists($entity, $method)) {
            $webmaster = $this->getRepo()->findWebmaster();
            $entity->$method($webmaster);
        }
        return $this;
    }

    /**
     * Passe le baseSubEntity en mode deleted
     * @param baseSubEntity &$entity
     * @param string $method = 'setStatut'
     * @return aeStatut
     */
    public function setDeleted(baseSubEntity &$entity, $method = 'setStatut') {
        if(method_exists($entity, $method)) {
            $deleted = $this->getRepo()->findDeleted();
            $entity->$method($deleted);
        }
        return $this;
    }


}