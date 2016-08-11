<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Labo\Bundle\AdminBundle\Controller\mediaController as extendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * mediaController
 * @Security("has_role('ROLE_USER')")
 */
class mediaController extends extendController {


}
