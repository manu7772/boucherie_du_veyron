<?php
namespace site\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\services\aeEntities;

use site\adminBundle\Entity\media;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.media');
class aeMedia extends aeEntities {

	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
		$this->defineEntity('site\adminBundle\Entity\media');
	}

	/**
	 * Check entity after change (edit…)
	 * @param baseEntity $entity
	 * @return aeMedia
	 */
	public function checkAfterChange(baseEntity &$entity) {
        // $item = $entity->getItem();
        // if($item != null)
        //     echo('<p>Item : '.$item->getClassName().' / '.$item->getNom().' / '.$item->getId().'</p>');
        // else echo('<p>Item : null</p>');
        // echo('<pre>');
        // var_dump($entity->getInfoForPersist());
        // die('<pre>');

        // echo('<p>End aeMedia::checkAfterChange()</p>');
	    parent::checkAfterChange($entity);
	    return $this;
	}

	/**
	 * Persist en flush a media
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	public function save(baseEntity &$entity) {
        return parent::save($entity);
	}

}