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
		// // saving…
		// $parents = $entity->getNestedParents();
		// $childs = $entity->getNestedChilds();
		// // nested childs
		// echo('<h2 style="color:magenta;margin:4px 0px;">Ajouts enfants Nested '.json_encode(get_class($entity)).'</h2>');
		// $this->affData($entity, $parents, $childs);
		// foreach($childs as $child) {
		// 	// $exists = $this->getRepo('site\adminBundle\Entity\nestedposition')->existsJointure($entity, $child);
		// 	if(!$entity->hasNestedposition($entity, $child)) {
		// 		$newPosition = new nestedposition();
		// 		$newPosition->setParentChild($entity, $child);
		// 		$this->getEm()->persist($newPosition);
		// 		echo('<p style="color:green;margin:1px 40px;">Création parent#'.$entity.' = child#'.$child.'</p>');
		// 	} else {
		// 		echo('<p style="color:orange;margin:1px 40px;">Existant parent#'.$entity.' = child#'.$child.'</p>');
		// 	}
		// }
		// // nested parents
		// echo('<h2 style="color:magenta;margin:4px 0px;">Ajouts parents Nested '.json_encode(get_class($entity)).'</h2>');
		// $this->affData($entity, $parents, $childs);
		// foreach($parents as $parent) {
		// 	// $exists = $this->getRepo('site\adminBundle\Entity\nestedposition')->existsJointure($parent, $entity);
		// 	if(!$entity->hasNestedposition($parent, $entity)) {
		// 		$newPosition = new nestedposition();
		// 		$newPosition->setParentChild($parent, $entity);
		// 		$this->getEm()->persist($newPosition);
		// 		echo('<p style="color:green;margin:1px 40px;">Création parent#'.$parent.' = child#'.$entity.'</p>');
		// 	} else {
		// 		echo('<p style="color:orange;margin:1px 40px;">Existant parent#'.$parent.' = child#'.$entity.'</p>');
		// 	}
		// }

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
	 * Persist and flush a item
     * @dev désactivée
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	// public function save(baseEntity &$entity, $flush = true) {
	// 	return parent::save($entity, $flush);
	// }

	/**
	 * Sort children of entity
	 * @param array $data
	 * @return array
	 */
	public function sortChildren($data) {
		$entityClassname = $this->getEntityClassName($data['entity'][0]);
		$entity = $this->getRepo($entityClassname)->find($data['entity'][1]);
		$i = 0;
		if($entity instanceOf nested) {
			foreach($data['children'] as $key => $item) {
				$item[0] = $this->getEntityClassName($item[0]);
				$itemEntity = $this->getRepo($item[0])->find($item[1]);
				$itemEntity->setNestedPosition_position($entity, $data['group'], $i++);
			}
			$this->getEm()->flush();
			$children = array();
			foreach($entity->getNestedpositionChilds() as $link) if($link->isParentGroup($entity, $data['group'])) {
				$children[] = array(
					array('classe_name' => $link->getChild()->getClassname()),
					array('id' => $link->getChild()->getId()),
					array('position' => $link->getChild()->getPositionFromHisParent($entity, $data['group'])),
					);
			}
			return array(
				'entity' => array(
					array('classe_name' => $entity->getClassname()),
					array('id' => $entity->getId()),
					array('group' => $data['group']),
					),
				'children' => $children,
				);
		}
		return false;
	}

}