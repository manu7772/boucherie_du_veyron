<?php
namespace site\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

use site\adminBundle\Entity\article;
use site\UserBundle\Entity\User;
use site\adminBundle\Entity\message;

use site\services\aeReponse;

class aeMessage {

    protected $container;
    protected $_em;
    protected $_repo;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->_em = $this->container->get('doctrine')->getManager();
        $this->_repo = $this->_em->getRepository('site\adminBundle\Entity\message');
    }

    public function getEm() {
        return $this->_em;
    }

    public function getRepo() {
        return $this->_repo;
    }

    /**
     * Check message after change (edit…)
     * @param message $message
     * 
     */
    public function checkAfterChange($message) {
        $this->container->get('aetools.aeEntity')->checkInversedLinks($message, false);
    }


}