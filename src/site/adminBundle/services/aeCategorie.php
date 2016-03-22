<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeEntity;

use site\adminBundle\Entity\categorie;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeCategorie');
class aeCategorie extends aeEntity {

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->defineEntity('site\adminBundle\Entity\categorie');
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeCategorie
     */
    public function checkAfterChange(baseEntity &$entity) {
        // check image
        $image = $entity->getImage();
        if(is_object($image)) {
            // if($image->isValid()) {
                $infoForPersist = $image->getInfoForPersist();
                $this->container->get('aetools.debug')->debugFile($infoForPersist);
                $service = $this->container->get('aetools.aeEntity')->getEntityService($image);
                $service->checkAfterChange($image);
                $service->checkStatuts($image);
                if($infoForPersist['removeImage'] === true || $infoForPersist['removeImage'] === 'true') {
                    // Supression de l'image
                    $entity->setImage(null);
                }
            // } else $entity->setImage(null);
        }
        // elements subEntitys ajoutés
        // echo('<pre>');
        // $list = $entity->getSubEntitys()->toArray();
        // echo(implode(', ', $list));
        // echo('</pre>');
        $hasElements = false;
        foreach($entity->getSubEntitys() as $subEntity) {
            $subEntity->addCategorie($entity);
            $service = $this->container->get('aetools.aeEntity')->getEntityService($subEntity);
            $service->save($subEntity, false);
            $hasElements = true;
        }
        // éléments subEntitys supprimés
        // echo('<pre>');
        // $list = $entity->getSubEntitysMem();
        // echo(implode(', ', $list));
        // echo('</pre>');
        $deletedElements = false;
        foreach($entity->getSubEntitysMem() as $removedSubEntity) {
            if(!$entity->getSubEntitys()->contains($removedSubEntity)) {
                $removedSubEntity->removeCategorie($entity);
                $service = $this->container->get('aetools.aeEntity')->getEntityService($removedSubEntity);
                $service->save($removedSubEntity, false);
                $deletedElements = true;
            }
        }
        // enfants
        $hasChildren = false;
        $accepts = $entity->getAccepts();
        foreach($entity->getAllChildren() as $child) {
            $child->setAccepts($accepts);
            $this->save($child, false);
            $hasChildren = true;
        }
        // save all
        // if($hasChildren || $hasElements || $deletedElements) $this->_em->flush();
        // die();
        parent::checkAfterChange($entity);
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