<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

use site\adminBundle\services\aeEntity;

use site\adminBundle\Entity\baseEntity;
use site\adminBundle\Entity\article;
use site\UserBundle\Entity\User;
use site\adminBundle\Entity\message;

use site\adminBundle\services\aeReponse;

class aeMessage extends aeEntity {

    const NAME                  = 'aeMessage';        // nom du service
    const CALL_NAME             = 'aetools.aeMessage'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminBundle\Entity\message';

    public function __construct(ContainerInterface $container = null, $em = null) {
        parent::__construct($container, $em);
        $this->defineEntity(self::CLASS_ENTITY);
        return $this;
    }

    public function getNom() {
        return self::NAME;
    }

    public function callName() {
        return self::CALL_NAME;
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeArticle
     */
    public function checkAfterChange(baseEntity &$entity, $butEntities = []) {
        parent::checkAfterChange($entity, $butEntities);
        return $this;
    }


}