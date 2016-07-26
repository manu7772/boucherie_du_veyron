<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\adminBundle\services\aeSubentity;

use site\adminBundle\Entity\baseEntity;
use site\adminBundle\Entity\nested;
use site\adminBundle\Entity\nestedposition;

// call in controller with $this->get('aetools.aeNested');
class aeNested extends aeSubentity {

	const NAME                  = 'aeNested';        // nom du service
	const CALL_NAME             = 'aetools.aeNested'; // comment appeler le service depuis le controller/container
	const CLASS_ENTITY          = 'site\adminBundle\Entity\nested';
	const CLASS_SHORT_ENTITY    = 'nested';

	public function __construct(ContainerInterface $container = null, $em = null) {
	    parent::__construct($container, $em);
	    $this->defineEntity(self::CLASS_ENTITY);
	    return $this;
	}

	/**
	 * Check entity after change (edit…)
	 * @param baseEntity $entity
	 * @return aeNested
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


	// /**
	//  * Sort children of entity
	//  * @param array $data
	//  * @return array
	//  */
	// public function sortChildren($data) {
	// 	$entityClassname = $this->getEntityClassName($data['entity'][0]);
	// 	$entity = $this->getRepo($entityClassname)->find($data['entity'][1]);
	// 	$i = 0;
	// 	if($entity instanceOf nested) {
	// 		foreach($data['children'] as $key => $item) {
	// 			$item[0] = $this->getEntityClassName($item[0]);
	// 			$itemEntity = $this->getRepo($item[0])->find($item[1]);
	// 			$itemEntity->setNestedPosition_position($entity, $data['group'], $i++);
	// 		}
	// 		$this->getEm()->flush();
	// 		$children = array();
	// 		foreach($entity->getNestedpositionChilds() as $link) if($link->isParentGroup($entity, $data['group'])) {
	// 			$children[] = array(
	// 				array('classe_name' => $link->getChild()->getClassname()),
	// 				array('id' => $link->getChild()->getId()),
	// 				array('position' => $link->getChild()->getPositionFromHisParent($entity, $data['group'])),
	// 				);
	// 		}
	// 		return array(
	// 			'entity' => array(
	// 				array('classe_name' => $entity->getClassname()),
	// 				array('id' => $entity->getId()),
	// 				array('group' => $data['group']),
	// 				),
	// 			'children' => $children,
	// 			);
	// 	}
	// 	return false;
	// }

}