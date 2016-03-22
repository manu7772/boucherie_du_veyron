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
		$entity->setNom($entity->getNom().'+');
		if(isset($infoForPersist['rawfiles']['actual'])) {
			$rawfile = $this->_em->getRepository('site\adminBundle\Entity\rawfile')->find(intval($infoForPersist['rawfiles']['actual']));
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
			else {
				// echo('<p>RAWFILE introuvable : '.$infoForPersist['rawfiles']['actual'].' ???</p>');
				// echo('<pre>');
				// var_dump($rawfile);
				// echo('</pre>');
			}
		}
		// else echo('<p>RAWFILE de rawfiles / actual : non renseigné !???</p>');
		parent::checkAfterChange($entity);
		return $this;
	}

	/**
	 * Persist and flush a image
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	// public function save(baseEntity &$entity, $flush = true) {
	// 	return parent::save($entity, $flush);
	// }


}