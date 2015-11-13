<?php

namespace site\graphicsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('sitegraphicsBundle:Default:index.html.twig', array('name' => $name));
    }
}
