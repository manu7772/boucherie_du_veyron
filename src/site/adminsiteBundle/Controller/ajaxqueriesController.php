<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Labo\Bundle\AdminBundle\Controller\ajaxqueriesController as extendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * ajaxqueriesController
 * @Security("has_role('ROLE_EDITOR')")
 */
class ajaxqueriesController extends extendController {


}
