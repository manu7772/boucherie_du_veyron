<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeSubEntity;

use site\adminBundle\Entity\categorie;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeCategorie');
class aeCategorie extends aeSubEntity {

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->defineEntity('site\adminBundle\Entity\categorie');
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeCategorie
     */
    public function checkAfterChange(baseEntity &$entity, $butEntities = []) {
        // elements subEntitys ajoutés // inverses
        // echo('<pre>');
        // $list = $entity->getSubEntitys()->toArray();
        // echo(implode(', ', $list));
        // echo('</pre>');
        // echo('<pre><h4>All children of all types <i>('.$entity.' #'.$entity->getId().')</i> : </h4>');
        // echo('<p>'.implode(', ', $entity->getChildrensOfAllTypes()->toArray()).'</p>');
        // echo('</pre>');

        // foreach($entity->getChildrensOfAllTypes() as $child) {
        //     $child->addParent($entity);
        //     $service = $this->container->get('aetools.aeEntity')->getEntityService($child);
        //     $service->checkAfterChange($child);
        //     $service->save($child, false);
        // }
        parent::checkAfterChange($entity, $butEntities);
        return $this;
    }

    /**
     * Persist and flush a categorie
     * @param baseEntity $entity
     * @return aeReponse
     */
    // public function save(baseEntity &$entity, $flush = true) {
    //  return parent::save($entity, $flush);
    // }

}