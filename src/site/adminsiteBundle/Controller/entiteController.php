<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Labo\Bundle\AdminBundle\Controller\entiteController as extendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * entiteController
 * @Security("has_role('ROLE_EDITOR')")
 */
class entiteController extends extendController {


}
