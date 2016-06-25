<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use site\adminBundle\Controller\categorieController as extendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * categorieController
 * @Security("has_role('ROLE_EDITOR')")
 */
class categorieController extends extendController {


}
