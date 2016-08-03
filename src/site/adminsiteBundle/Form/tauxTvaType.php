<?php

namespace site\adminsiteBundle\Form;

use Labo\Bundle\AdminBundle\Form\baseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Transformer
use Symfony\Component\Form\CallbackTransformer;
// User
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage as SecurityContext;
// Paramétrage de formulaire
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class tauxTvaType extends baseType {

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
                'label' => 'fields.nom',
                'translation_domain' => 'tauxTva',
                'required' => true,
                ))
            ->add('taux', 'text', array(
                'label' => 'fields.taux',
                'translation_domain' => 'tauxTva',
                'required' => true,
                ))
            // ->add('descriptif', 'textarea', array(
            ->add('descriptif', 'insRichtext', array(
                'label' => 'fields.descriptif',
                'translation_domain' => 'tauxTva',
                'required' => false,
                'attr' => array(
                    // 'rows' => '12',
                    'data-height' => 140,
                    )
                ))
            // ->add('creation')
            // ->add('ip')
        ;
        // ajoute les valeurs hidden, passés en paramètre
        $this->addHiddenValues($builder, true);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'site\adminsiteBundle\Entity\tauxTva'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'site_adminsitebundle_tauxTva';
    }
}
