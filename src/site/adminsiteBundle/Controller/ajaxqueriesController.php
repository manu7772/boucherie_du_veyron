<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use site\adminBundle\Controller\ajaxqueriesController as extendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * ajaxqueriesController
 * @Security("has_role('ROLE_EDITOR')")
 */
class ajaxqueriesController extends extendController {


}
