<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Labo\Bundle\AdminBundle\Controller\DefaultController as extendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * DefaultController
 * @Security("has_role('ROLE_TRANSLATOR')")
 */
class DefaultController extends extendController {
	

}
