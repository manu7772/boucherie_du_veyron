<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Labo\Bundle\AdminBundle\Controller\DefaultController as extendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Labo\Bundle\AdminBundle\services\aeData;

/**
 * DefaultController
 * @Security("has_role('ROLE_TRANSLATOR')")
 */
class DefaultController extends extendController {
	


}
