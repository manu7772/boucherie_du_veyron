<?php

namespace site\adminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Transformer
use Symfony\Component\Form\CallbackTransformer;
// User
use Symfony\Component\Security\Core\SecurityContext;
// Paramétrage de formulaire
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use site\services\aeEntities;
use site\adminBundle\Entity\statutRepository;

abstract class baseType extends AbstractType {

    protected $controller;
    protected $securityContext;
    protected $parametres;
    protected $_em;
    
    public function __construct(Controller $controller = null, $parametres = null) {
        $this->controller = $controller;
        $this->_em = $this->controller->get('doctrine')->getManager();
        // $this->aeEntities = new aeEntities($this->controller, $this->_em);
        $this->aeEntities = $this->controller->get('aetools.aeentities');
        $this->securityContext = $controller->get('security.context');
        $this->user = $this->securityContext->getToken()->getUser();
        if($parametres === null) $parametres = array();
        $this->parametres = $parametres;
    }


    protected function initBuilder(FormBuilderInterface &$builder) {
        // ajout de action si défini
        if(isset($this->parametres['form_action'])) $builder->setAction($this->parametres['form_action']);
    }

    protected function addSubmit(FormBuilderInterface &$builder) {
        // AJOUT SUBMIT
        if(!($builder->getData() == null)) {
            $builder->add('submit', 'submit', array(
                'label' => 'form.enregistrer',
                'translation_domain' => 'messages',
                'attr' => array(
                    'class' => "btn btn-md btn-block btn-info",
                    ),
                ))
            ;
        }
    }

    /**
     * addHiddenValues
     * @param FormBuilderInterface $builder
     * @return FormBuilderInterface
     */
    protected function addHiddenValues(FormBuilderInterface &$builder, $addSubmit = false) {
        if(!($builder->getData() == null)) {
            if($addSubmit == true) $this->addSubmit($builder);
            $data = array();
            $nom = 'hiddenData';
            foreach($this->parametres as $key => $value) {
                if(is_string($value) || is_array($value) || is_bool($value)) {
                    $data[$key] = $value;
                }
            }
            if($builder->has($nom)) $builder->remove($nom);
            $builder->add($nom, 'hidden', array(
                'data' => urlencode(json_encode($data, true)),
                'mapped' => false,
            ));
            // entités liées par défaut
            // $entity = $builder->getData();
            // $this->aeEntities->fillWithDefaultLinked($entity);
            // $this->aeEntities->fillAllAssociatedFields($entity);
        }
        // return
        return $builder;
    }

}
