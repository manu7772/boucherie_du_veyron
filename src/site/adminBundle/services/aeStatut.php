<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeEntity;

use site\adminBundle\Entity\statut;
use site\adminBundle\Entity\baseEntity;
use site\adminBundle\Entity\subentity;

// call in controller with $this->get('aetools.aeStatut');
class aeStatut extends aeEntity {

    const NAME                  = 'aeStatut';        // nom du service
    const CALL_NAME             = 'aetools.aeStatut'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminBundle\Entity\statut';

    public function __construct(ContainerInterface $container = null, $em = null) {
        parent::__construct($container, $em);
        $this->defineEntity(self::CLASS_ENTITY);
        return $this;
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeArticle
     */
    public function checkAfterChange(&$entity, $butEntities = []) {
        parent::checkAfterChange($entity, $butEntities);
        return $this;
    }

    public function getNom() {
        return self::NAME;
    }

    public function callName() {
        return self::CALL_NAME;
    }

    /**
     * Persist and flush a statut
     * @dev désactivée
     * @param baseEntity $entity
     * @return aeReponse
     */
    // public function save(baseEntity &$entity, $flush = true) {
    //  return parent::save($entity, $flush);
    // }

    /**
     * Passe le subentity en mode temps
     * @param subentity &$entity
     * @param string $method = 'setStatut'
     * @return aeStatut
     */
    public function setTemp(subentity &$entity, $method = 'setStatut') {
        if(method_exists($entity, $method)) {
            $temp = $this->getRepo()->findTemp();
            $entity->$method($temp);
        }
        return $this;
    }

    /**
     * Passe le subentity en mode actif
     * @param subentity &$entity
     * @param string $method = 'setStatut'
     * @return aeStatut
     */
    public function setActif(subentity &$entity, $method = 'setStatut') {
        if(method_exists($entity, $method)) {
            $actif = $this->getRepo()->findActif();
            $entity->$method($actif);
        }
        return $this;
    }

    /**
     * Passe le subentity en mode inactif
     * @param subentity &$entity
     * @param string $method = 'setStatut'
     * @return aeStatut
     */
    public function setInactif(subentity &$entity, $method = 'setStatut') {
        if(method_exists($entity, $method)) {
            $inactif = $this->getRepo()->findInactif();
            $entity->$method($inactif);
        }
        return $this;
    }

    /**
     * Passe le subentity en mode expired
     * @param subentity &$entity
     * @param string $method = 'setStatut'
     * @return aeStatut
     */
    public function setExpired(subentity &$entity, $method = 'setStatut') {
        if(method_exists($entity, $method)) {
            $expired = $this->getRepo()->findExpired();
            $entity->$method($expired);
        }
        return $this;
    }

    /**
     * Passe le subentity en mode webmaster
     * @param subentity &$entity
     * @param string $method = 'setStatut'
     * @return aeStatut
     */
    public function setWebmaster(subentity &$entity, $method = 'setStatut') {
        if(method_exists($entity, $method)) {
            $webmaster = $this->getRepo()->findWebmaster();
            $entity->$method($webmaster);
        }
        return $this;
    }

    /**
     * Passe le subentity en mode deleted
     * @param subentity &$entity
     * @param string $method = 'setStatut'
     * @return aeStatut
     */
    public function setDeleted(subentity &$entity, $method = 'setStatut') {
        if(method_exists($entity, $method)) {
            $deleted = $this->getRepo()->findDeleted();
            $entity->$method($deleted);
        }
        return $this;
    }


}