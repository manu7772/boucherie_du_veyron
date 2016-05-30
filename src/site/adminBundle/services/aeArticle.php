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
		// fiche inverse
		// foreach($entity->getFiches() as $fiche) {
		//     $fiche->addArticle($entity);
		//     $service = $this->container->get('aetools.aeEntity')->getEntityService($fiche);
		//     $service->checkAfterChange($fiche);
		//     $service->save($fiche, false);
		// }
		// saving…
		$childs = $entity->getArticleChilds();
		$parents = $entity->getArticleParents();
		$oldChilds = $entity->getOldValues('articlepositionChilds');
		$oldParents = $entity->getOldValues('articlepositionParents');
		// nested childs
		echo('<h2 style="color:magenta;margin:4px 0px;">Ajouts enfants…</h2>');
		$this->affData($entity);
		foreach ($childs as $child) {
			$exists = $this->getRepo('site\adminBundle\Entity\articleposition')->existsJointure($entity, $child);
			if(!$exists) {
				$newPosition = new articleposition();
				$newPosition->setParentEnfant($entity, $child);
				$this->getEm()->persist($newPosition);
				echo('<p style="color:green;margin:1px 40px;">Création parent#'.$entity.' = child#'.$child.'</p>');
			} else {
				echo('<p style="color:orange;margin:1px 40px;">Existant parent#'.$entity.' = child#'.$child.'</p>');
			}
		}
		// nested parents
		echo('<h2 style="color:magenta;margin:4px 0px;">Ajouts parents</h2>');
		$this->affData($entity);
		foreach ($parents as $parent) {
			$exists = $this->getRepo('site\adminBundle\Entity\articleposition')->existsJointure($parent, $entity);
			if(!$exists) {
				$newPosition = new articleposition();
				$newPosition->setParentEnfant($parent, $entity);
				$this->getEm()->persist($newPosition);
				echo('<p style="color:green;margin:1px 40px;">Création parent#'.$parent.' = child#'.$entity.'</p>');
			} else {
				echo('<p style="color:orange;margin:1px 40px;">Existant parent#'.$parent.' = child#'.$entity.'</p>');
			}
		}
		// effacement olds
		echo('<h2 style="color:magenta;margin:4px 0px;">Suppressions enfants…</h2>');
		$this->affData($entity);
		foreach ($oldChilds as $name => $old) {
			if(!$childs->contains($old->getChild())) {
				echo('<p style="color:red;margin:1px 40px;">Suppression enfant#'.$old->getParent().' = child#'.$old->getChild().'</p>');
				$this->getEm()->remove($old);
			} else {
				echo('<p style="color:grey;margin:1px 40px;"><small><i>Concervation enfant#'.$old->getParent().' = child#'.$old->getChild().'</i></small></p>');
			}
		}
		echo('<h2 style="color:magenta;margin:4px 0px;">Suppressions parents…</h2>');
		$this->affData($entity);
		foreach ($oldParents as $name => $old) {
			if(!$parents->contains($old->getParent())) {
				echo('<p style="color:red;margin:1px 40px;">Suppression parent#'.$old->getParent().' = child#'.$old->getChild().'</p>');
				$this->getEm()->remove($old);
			} else {
				echo('<p style="color:grey;margin:1px 40px;"><small><i>Concervation parent#'.$old->getParent().' = child#'.$old->getChild().'</i></small></p>');
			}
		}
		// die();
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

}