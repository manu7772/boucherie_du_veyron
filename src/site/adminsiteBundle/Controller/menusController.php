<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Labo\Bundle\AdminBundle\Controller\menusController as extendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * menusController
 * @Security("has_role('ROLE_ADMIN')")
 */
class menusController extends extendController {


}
