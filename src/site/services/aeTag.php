<?php
namespace site\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

use site\adminBundle\Entity\article;
use site\UserBundle\Entity\User;
use site\adminBundle\Entity\tag;

use site\services\aeReponse;

class aeTag {

    protected $container;
    protected $_em;
    protected $_repo;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->_em = $this->container->get('doctrine')->getManager();
        $this->_repo = $this->_em->getRepository('site\adminBundle\Entity\tag');
    }

    public function getEm() {
        return $this->_em;
    }

    public function getRepo() {
        return $this->_repo;
    }

    /**
     * Check tag after change (edit…)
     * @param tag $tag
     * 
     */
    public function checkAfterChange($tag) {
        // inverses
        // $inverses = array(
        //     'pagewebs'      => 'addTag',
        //     'articles'      => 'addTag',
        //     'categories'    => 'addTag',
        //     'fiches'        => 'addTag',
        //     'medias'        => 'addTag',
        //     );
        // foreach ($inverses as $inverse => $method) {
        //     $get = "get".ucfirst($inverse);
        //     $links = 
        // }
        $this->container->get('aetools.aeEntities')->checkInversedLinks($tag, false);
    }

}