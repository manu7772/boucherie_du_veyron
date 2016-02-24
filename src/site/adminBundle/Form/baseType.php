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
            $entity = $builder->getData();
            // $this->aeEntities->fillWithDefaultLinked($entity);


            $entities = $this->aeEntities->getAssociationNamesOfEntity($entity);
            foreach($entities as $shortname) if($shortname == 'statut') {
                $classname = $this->aeEntities->getEntityClassName($shortname);
                $set = $this->aeEntities->getMethodNameWith($shortname, 'set');
                $get = $this->aeEntities->getMethodNameWith($shortname, 'get');
                if(method_exists($entity, $set) && method_exists($entity, $get)) {
                    if($entity->$get() == null || (is_array($entity->$get()) && count($entity->$get()) == 0)) {
                        $default = $this->_em->getRepository($classname)->defaultVal();
                        if(is_array($default)) $default = reset($default);
                        if(is_object($default)) $entity->$set($default);
                    }
                    $builder
                        // ->add('statut', 'hidden', array(
                        ->add($shortname, 'entity', array(
                            'class'     => $classname,
                            'property'  => 'nom',
                            'multiple'  => false,
                            "label"     => $shortname.'.name',
                            "translation_domain" => $shortname,
                            "query_builder" => function($repo) {
                                if(method_exists($repo, 'defaultValsListClosure'))
                                    return $repo->defaultValsListClosure($this->user);
                                    else return $repo->findAllClosure();
                                },
                            ));
                }
            }
        }
        // return
        return $builder;
    }

}
