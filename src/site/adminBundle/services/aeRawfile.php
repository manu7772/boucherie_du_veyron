<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeEntity;

use site\adminBundle\Entity\rawfile;
use site\adminBundle\Entity\baseEntity;

class aeRawfile extends aeEntity {

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->defineEntity('site\adminBundle\Entity\rawfile');
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeRawfile
     */
    public function checkAfterChange(baseEntity &$entity, $butEntities = []) {
        parent::checkAfterChange($entity, $butEntities);
        return $this;
    }

    /**
     * Persist and flush a rawfile
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