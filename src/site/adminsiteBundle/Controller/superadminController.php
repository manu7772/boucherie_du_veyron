<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use site\adminBundle\Controller\superadminController as extendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * superadminController
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class superadminController extends extendController {


}
