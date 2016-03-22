<?php

namespace site\adminBundle\Form;

use site\adminBundle\Form\baseType;
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

class adresseType extends baseType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        // ajout de action si défini
        $this->initBuilder($builder);
        // Builder…
        $builder
            // ->add('nom', 'text', array(
            //     'label' => 'fields.nom',
            //     'translation_domain' => 'adresse',
            //     'required' => true,
            //     ))
            ->add('adresse', 'textarea', array(
                'label' => 'fields.adresse',
                'translation_domain' => 'adresse',
                'required' => true,
                ))
            ->add('cp', 'insCpostal', array(
                "required"  => true,
                "label"     => 'fields.cp',
                'translation_domain' => 'adresse',
                // 'attr'      => array(
                //     "placeholder" => "00000",
                //     "data-mask" => "99999",
                //     ),
                ))
            ->add('ville', 'text', array(
                "required"  => true,
                "label"     => 'fields.ville',
                'translation_domain' => 'adresse',
                ))
            ->add('commentaire', 'textarea', array(
                "required"  => false,
                "label"     => 'fields.commentaire',
                'translation_domain' => 'adresse',
                ))
        ;
        // ajoute les valeurs hidden, passés en paramètre
        $this->addHiddenValues($builder, true);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'site\adminBundle\Entity\adresse'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'site_adminbundle_adresse';
    }
}