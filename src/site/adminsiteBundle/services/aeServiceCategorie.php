<?php
namespace site\adminsiteBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Labo\Bundle\AdminBundle\services\aeServiceNested;

use site\adminsiteBundle\Entity\categorie;
use Labo\Bundle\AdminBundle\Entity\baseEntity;
use Labo\Bundle\AdminBundle\Entity\nestedposition;

// call in controller with $this->get('aetools.aeCategorie');
class aeServiceCategorie extends aeServiceNested {

    const NAME                  = 'aeServiceCategorie';        // nom du service
    const CALL_NAME             = 'aetools.aeCategorie'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminsiteBundle\Entity\categorie';
    const CLASS_SHORT_ENTITY    = 'categorie';

    public function __construct(ContainerInterface $container = null, $em = null) {
        parent::__construct($container, $em);
        $this->defineEntity(self::CLASS_ENTITY);
        return $this;
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeServiceCategorie
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