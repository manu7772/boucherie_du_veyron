<?php

namespace site\adminBundle\Entity;

use site\adminBundle\Entity\EntityBaseRepository;
// use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
// use Gedmo\Tree\Traits\Repository\ORM\NestedTreeRepositoryTrait;
use Gedmo\Tree\Traits\MaterializedPath;
use Gedmo\Tree\Traits\NestedSet;
use Gedmo\Tree\Traits\NestedSetEntity;

// use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
// use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
// use Gedmo\Tree\Entity\Repository\ClosureTreeRepository;
// use Gedmo\Tree\Entity\Repository\AbstractTreeRepository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * categorieRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class categorieRepository extends EntityBaseRepository {

	// https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/tree.md
	use NestedSetEntity;
	// use NestedTreeRepository;
	// use MaterializedPathRepository;
	// use ClosureTreeRepository;
	// use AbstractTreeRepository;

	public function __construct(EntityManager $em, ClassMetadata $class) {
		parent::__construct($em, $class);
		$this->initializeTreeRepository($em, $class);
	}



}
