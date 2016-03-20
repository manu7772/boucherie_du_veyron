<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeMedia;

use site\adminBundle\Entity\image;
use site\adminBundle\Entity\baseEntity;

class aeImage extends aeMedia {

	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
		$this->defineEntity('site\adminBundle\Entity\image');
	}

	/**
	 * Check entity after change (edit…)
	 * @param baseEntity $entity
	 * @return aeImage
	 */
	public function checkAfterChange(baseEntity &$entity) {
		$infoForPersist = $entity->getInfoForPersist();
		if(isset($infoForPersist['rawfiles']['actual'])) {
			if($infoForPersist['removeImage'] == true) {
				// DELETE MEDIA
			} else {
				$rawfile = $this->container->get('aetools.aeRawfile')->getRepo()->find($infoForPersist['rawfiles']['actual']);
				if(is_object($rawfile)) {
					$oldrawfile = $entity->getRawfile();
					if($oldrawfile != null) {
						// normalement inutile : orphanremoval
						$this->container->get('aetools.aeStatut')->setDeleted($oldrawfile);
						// $oldrawfile->setMedia(null);
					}
					$this->container->get('aetools.aeStatut')->setWebmaster($rawfile);
					$entity->setRawfile($rawfile);
					// echo('<p>- new RAWFILE in '.get_class($this).' : '.$entity->getRawfile().'</p>');
				}
			}
		}
		parent::checkAfterChange($entity);
		return $this;
	}

	/**
	 * Persist and flush a image
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	// public function save(baseEntity &$entity) {
	// 	return parent::save($entity);
	// }


}