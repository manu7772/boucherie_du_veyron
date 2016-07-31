<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Labo\Bundle\AdminBundle\Controller\generateController as extendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * generateController
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class generateController extends extendController {


}
