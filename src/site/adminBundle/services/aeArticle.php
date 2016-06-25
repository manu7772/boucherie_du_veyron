<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use site\adminBundle\services\aeItem;

use site\adminBundle\Entity\article;
use site\adminBundle\Entity\baseEntity;
use site\adminBundle\Entity\articleposition;

// call in controller with $this->get('aetools.aeArticle');
class aeArticle extends aeItem {

	const NAME                  = 'aeArticle';        // nom du service
	const CALL_NAME             = 'aetools.aeArticle'; // comment appeler le service depuis le controller/container
	const CLASS_ENTITY          = 'site\adminBundle\Entity\article';
	const CLASS_SHORT_ENTITY    = 'article';

	public function __construct(ContainerInterface $container = null, $em = null) {
		parent::__construct($container, $em);
		$this->defineEntity(self::CLASS_ENTITY);
		return $this;
	}

	/**
	 * Check entity after change (edit…)
	 * @param baseEntity $entity
	 * @return aeArticle
	 */
	public function checkAfterChange(&$entity, $butEntities = []) {
		// saving…
		// $parents = $entity->getArticleParents();
		// $childs = $entity->getArticleChilds();
		// // $oldChilds = $entity->getOldValues('articlepositionChilds');
		// // $oldParents = $entity->getOldValues('articlepositionParents');
		// // nested childs
		// echo('<h2 style="color:magenta;margin:4px 0px;">Ajouts enfants Articles '.json_encode(get_class($entity)).'</h2>');
		// $this->affData($entity, $parents, $childs);
		// foreach ($childs as $child) {
		// 	$exists = $this->getRepo('site\adminBundle\Entity\articleposition')->existsJointure($entity, $child);
		// 	if(!$exists) {
		// 		$newPosition = new articleposition();
		// 		$newPosition->setParentChild($entity, $child);
		// 		$this->getEm()->persist($newPosition);
		// 		echo('<p style="color:green;margin:1px 40px;">Création parent#'.$entity.' = child#'.$child.'</p>');
		// 	} else {
		// 		echo('<p style="color:orange;margin:1px 40px;">Existant parent#'.$entity.' = child#'.$child.'</p>');
		// 	}
		// }
		// // nested parents
		// echo('<h2 style="color:magenta;margin:4px 0px;">Ajouts parents Articles '.json_encode(get_class($entity)).'</h2>');
		// $this->affData($entity, $parents, $childs);
		// foreach ($parents as $parent) {
		// 	$exists = $this->getRepo('site\adminBundle\Entity\articleposition')->existsJointure($parent, $entity);
		// 	if(!$exists) {
		// 		$newPosition = new articleposition();
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
	 * Persist and flush a article
	 * @param baseEntity $entity
	 * @return aeReponse
	 */
	// public function save(baseEntity &$entity, $flush = true) {
	//  return parent::save($entity, $flush);
	// }

	// /**
	//  * Sort children of entity
	//  * @param array $data
	//  * @return array
	//  */
	// public function sortChildren($data) {
	// 	if($data['entity'][0] == self::CLASS_SHORT_ENTITY) {
	// 		$entity = $this->getRepo(self::CLASS_ENTITY)->find($data['entity'][1]);
	// 		$sorted = array_reverse($data['children']);
	// 		foreach ($sorted as $key => $item) {
	// 			$itemEntity = $this->getRepo(self::CLASS_ENTITY)->find($item[1]);
	// 			$itemEntity->setArticlePosition_first($entity);
	// 			$this->save($itemEntity, false);
	// 		}
	// 		$this->getEm()->flush();
	// 		// $entity = $this->getRepo(self::CLASS_ENTITY)->find($data['entity'][1]);
	// 		$children = array();
	// 		foreach ($entity->getArticleChilds() as $child) {
	// 			$children[] = array(
	// 				array('classe_name' => $child->getClassname()),
	// 				array('id' => $child->getId()),
	// 				array('position' => $child->getArticlePosition($entity)),
	// 				array('ParentInfo' => $child->getArticleParentInfo()),
	// 				array('ChildInfo' => $child->getArticleChildrenInfo()),
	// 				);
	// 		}
	// 		return array(
	// 			'entity' => array(
	// 				array('classe_name' => $entity->getClassname()),
	// 				array('id' => $entity->getId()),
	// 				array('ParentInfo' => $entity->getArticleParentInfo()),
	// 				array('ChildInfo' => $entity->getArticleChildrenInfo()),
	// 				),
	// 			'children' => $children,
	// 			);
	// 	}
	// 	return parent::sortChildren($data);
	// }


}

