<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeMedia;

use site\adminBundle\Entity\image;
use site\adminBundle\Entity\baseEntity;

class aeImage extends aeMedia {

    const NAME                  = 'aeImage';        // nom du service
    const CALL_NAME             = 'aetools.aeImage'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminBundle\Entity\image';

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
	 * @return aeImage
	 */
	public function checkAfterChange(&$entity, $butEntities = []) {
		$infoForPersist = $entity->getInfoForPersist();
		$entity->setNom($entity->getNom());
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
			}
		}
		// $entity->upLoad();
		parent::checkAfterChange($entity, $butEntities);
		return $this;
	}


}