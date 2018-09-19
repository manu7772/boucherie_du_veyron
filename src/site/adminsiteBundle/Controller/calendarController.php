<?php

namespace site\adminsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Labo\Bundle\AdminBundle\Controller\calendarController as extendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * calendarController
 * @Security("has_role('ROLE_TRANSLATOR')")
 */
class calendarController extends extendController {
	

}
