<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeEntity;

use site\adminBundle\Entity\rawfile;
use site\adminBundle\Entity\baseEntity;

// call in controller with $this->get('aetools.aeRawfile');
class aeRawfile extends aeEntity {

    const NAME                  = 'aeRawfile';        // nom du service
    const CALL_NAME             = 'aetools.aeRawfile'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminBundle\Entity\rawfile';

    public function __construct(ContainerInterface $container = null, $em = null) {
        parent::__construct($container, $em);
        $this->defineEntity(self::CLASS_ENTITY);
        return $this;
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeRawfile
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
     * Persist and flush a rawfile
     * @dev désactivée
     * @param baseEntity $entity
     * @return aeReponse
     */
    // public function save(baseEntity &$entity, $flush = true) {
    //  return parent::save($entity, $flush);
    // }

	/**
	 * Renvoie une nouvelle entité rawfile remplie avec les données de $data
	 * @param array $data
	 * @return rawfile
	 */
	public function getNewRawfileWithData($data) {
		$rawfile = $this->getNewEntity('site\adminBundle\Entity\rawfile');
		$rawfile->setOriginalnom($data['file']['name']);
		$rawfile->setNom($data['file']['name']);
		$rawfile->setFormat($data['file']['type']);
		$rawfile->setFileSize($data['file']['size']);
        $rawfile->setHeight($data['height']);
        $rawfile->setWidth($data['width']);
		$rawfile->setBinaryFile($data['raw']);
        $this->container->get('aetools.aeStatut')->setTemp($rawfile);
		return $rawfile;
	}


}