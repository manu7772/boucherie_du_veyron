<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeSubEntity;

use site\adminBundle\Entity\categorie;
use site\adminBundle\Entity\baseEntity;
use site\adminBundle\Entity\categorieposition;

// call in controller with $this->get('aetools.aeCategorie');
class aeCategorie extends aeSubEntity {


    const NAME                  = 'aePanier';        // nom du service
    const CALL_NAME             = 'aetools.aePanier'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminBundle\Entity\categorie';

    public function __construct(ContainerInterface $container = null, $em = null) {
        parent::__construct($container, $em);
        $this->defineEntity(self::CLASS_ENTITY);
        return $this;
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeCategorie
     */
    public function checkAfterChange(&$entity, $butEntities = []) {
        // elements subEntitys ajoutés // inverses
        if($entity->getPageweb() == null) {
            // pageweb par défaut
            $servicePageweb = $this->container->get('aetools.aePageweb');
            $pw = $servicePageweb->getRepo()->findByNom('liste_'.$entity->getType().'s');
            if(count($pw) > 0) {
                $entity->setPageweb(reset($pw));
            }
        }
        // ajout baseSubEntity
        $addedSubEntitys = $entity->getAddedSubEntitys()->toArray();
        if(count($addedSubEntitys > 0)) {
            foreach ($addedSubEntitys as $key => $added) {
                if(!$added->hasChildrensOfAllTypes($entity) && !$added->hasHistorySubEntitys($entity) && $entity != $added) {
                    $cp = new categorieposition();
                    $cp->setCategorie($added)->setSubEntity($entity);
                    $this->getEm()->persist($cp);
                }
            }
            $entity->clearAddedSubEntitys();
        }
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
     * Persist and flush a categorie
     * @param baseEntity $entity
     * @return aeReponse
     */
    // public function save(baseEntity &$entity, $flush = true) {
    //  return parent::save($entity, $flush);
    // }

    /**
     * Get list of types of $cagetories = categorie or array of categorie
     * @param array $categories
     * @return array
     */
    public function getTypesOfCategories($categories) {
        if(is_object($categories)) $categories = array($categories);
        $types = array();
        if(is_array($categories)) {
            foreach ($categories as $categorie) {
                if($categorie instanceOf categorie) $types[] = $categorie->getType();
            }
        }
        return array_unique($types);
    }

}