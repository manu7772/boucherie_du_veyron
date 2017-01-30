<?php
namespace site\adminsiteBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
// use Doctrine\ORM\Event\PreUpdateEventArgs;
// use Doctrine\ORM\Event\LifecycleEventArgs;

use Labo\Bundle\AdminBundle\services\aeServiceNested;

use site\adminsiteBundle\Entity\categorie;
use Labo\Bundle\AdminBundle\Entity\baseEntity;
use Labo\Bundle\AdminBundle\Entity\nestedposition;

// call in controller with $this->get('aetools.aeServiceCategorie');
class aeServiceCategorie extends aeServiceNested {

    const NAME                  = 'aeServiceCategorie';        // nom du service
    const CALL_NAME             = 'aetools.aeServiceCategorie'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminsiteBundle\Entity\categorie';
    const CLASS_SHORT_ENTITY    = 'categorie';

    const TRASH_NAME            = 'corbeille';
    protected $trash;

    public function __construct(ContainerInterface $container, EntityManager $EntityManager = null) {
        parent::__construct($container, $EntityManager);
        $this->defineEntity(self::CLASS_ENTITY);
        $this->trash = false;
        return $this;
    }

    // /**
    //  * Check entity after change (edit…)
    //  * @param baseEntity $entity
    //  * @return aeServiceCategorie
    //  */
    // public function checkAfterChange(&$entity, $butEntities = []) {
    //     parent::checkAfterChange($entity, $butEntities);
    //     return $this;
    // }

    public function getNom() {
        return self::NAME;
    }

    public function callName() {
        return self::CALL_NAME;
    }


    /**
     * Check entity integrity in context
     * @param categorie $entity
     * @param string $context ('new', 'PostLoad', 'PrePersist', 'PostPersist', 'PreUpdate', 'PostUpdate', 'PreRemove', 'PostRemove')
     * @param $eventArgs = null
     * @return aeServiceCategorie
     */
    public function checkIntegrity(&$entity, $context = null, $eventArgs = null) {
        parent::checkIntegrity($entity, $context, $eventArgs);
        // if($entity instanceOf categorie) {
            switch(strtolower($context)) {
                case 'edit':
                    $this->setTypesDescription($entity);
                    break;
                case 'new':
                    $this->setTypesDescription($entity);
                    // echo('<h3 style="color:green;">New entity on '.json_encode($entity->getType()).'</h3>');
                    break;
                case 'postload':
                    $this->setTypesDescription($entity);
                    // echo('<h3 style="color:green;">PostLoad on '.json_encode($entity->getNom()).'</h3>');
                    // $this->setTypesDescription($entity);
                    break;
                case 'prepersist':
                    break;
                case 'postpersist':
                    break;
                case 'preupdate':
                    break;
                case 'postupdate':
                    break;
                case 'preremove':
                    break;
                case 'postremove':
                    break;
                default:
                    break;
            }
        // }
        return $this;
    }

    public function setTypesDescription(&$entity) {
        // echo('<pre><h3>setTypesDescription on '.json_encode($entity->getNom()).'</h3>');var_dump($this->container->getParameter('info_entites')[self::CLASS_SHORT_ENTITY]['types_descrition']);echo('</pre>');
        $entity->setTypesDescription($this->container->getParameter('info_entites')[self::CLASS_SHORT_ENTITY]['types_descrition']);
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

    /**
     * Check types of categories (use this function if changes in labo_paremeters.yml)
     * @return aeReponse
     */
    public function checkCategoriesTypes() {
        $report = array();
        $aeReponse = $this->container->get('aetools.aeReponse');
        $categories = $this->getRepo()->findByLvl(0);
        foreach ($categories as $categorie) {
            $report[$categorie->getSlug()] = array();
            $report[$categorie->getSlug()]['old']['type'] = $categorie->getType();
            $report[$categorie->getSlug()]['old']['accepts'] = $categorie->getAccepts();
            $categorie->checkType();
            $report[$categorie->getSlug()]['new']['type'] = $categorie->getType();
            $report[$categorie->getSlug()]['new']['accepts'] = $categorie->getAccepts();
        }
        $this->getEm()->flush();
        $aeReponse->setResult(true);
        $aeReponse->setMessage('Toutes les catégories ont été checkées. Veuillez recharger la page, s.v.p.');
        $aeReponse->setData($report);
        return $aeReponse;
    }

    public function controlTrashCategorie() {
        if($this->trash == false) {
            $trash = $this->getRepo()->findByType('trash');
            if(count($trash) < 1) {
                $trash = $this->getNewEntity(self::CLASS_ENTITY);
                $trash->setNom(self::TRASH_NAME);
                $trash->setType('trash');
                $trash->setIcon('fa-trash');
                if($trash->getStatut() != null) {
                    $this->getEm()->persist($trash);
                    $result = $this->getEm()->flush();
                    if($result) $this->trash = $trash;
                    return $result;
                } else {
                    return false;
                }
            }
        }
        return true;
    }

}