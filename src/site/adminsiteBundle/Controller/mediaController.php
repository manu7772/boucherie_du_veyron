<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use site\adminBundle\Controller\mediaController as extendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * mediaController
 * @Security("has_role('ROLE_EDITOR')")
 */
class mediaController extends extendController {


}
