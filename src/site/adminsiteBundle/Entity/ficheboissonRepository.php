<?php

namespace site\adminsiteBundle\Entity;

use Labo\Bundle\AdminBundle\Entity\ficheRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * ficheboissonRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ficheboissonRepository extends ficheRepository {

	public function defaultVal() {
		return array();
	}

}
