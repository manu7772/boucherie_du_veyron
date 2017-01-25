<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Labo\Bundle\AdminBundle\Controller\factureController as extendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * factureController
 * @Security("has_role('ROLE_EDITOR')")
 */
class factureController extends extendController {


}
