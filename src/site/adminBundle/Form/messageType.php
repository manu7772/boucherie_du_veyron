<?php

namespace site\adminBundle\Form;

use site\adminBundle\Form\baseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Transformer
use Symfony\Component\Form\CallbackTransformer;
// User
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage as SecurityContext;
// Paramétrage de formulaire
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class messageType extends baseType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        // ajout de action si défini
        $this->initBuilder($builder);
        // Builder…
        $builder
            ->add('nom', 'text', array(
                'label' => 'form.nom',
                'translation_domain' => 'messages',
                'required' => false,
                ))
            ->add('email', 'email', array(
                'label' => 'form.email',
                'translation_domain' => 'messages',
                'required' => true,
                ))
            ->add('objet', 'text', array(
                'label' => 'form.objet',
                'translation_domain' => 'messages',
                'required' => false,
                ))
            ->add('message', 'textarea', array(
                'label' => 'form.message',
                'translation_domain' => 'messages',
                'required' => true,
                'attr' => array(
                    'rows' => '12',
                    )
                ))
            // ->add('creation')
            // ->add('ip')
        ;
        // ajoute les valeurs hidden, passés en paramètre
        $this->addHiddenValues($builder, true);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'site\adminBundle\Entity\message'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'site_adminbundle_message';
    }
}
