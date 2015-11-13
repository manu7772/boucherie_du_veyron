<?php

namespace site\editionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('siteeditionBundle:Default:index.html.twig', array('name' => $name));
    }
}
