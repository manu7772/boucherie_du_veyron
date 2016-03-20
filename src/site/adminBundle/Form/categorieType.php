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

class categorieType extends baseType {

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
                'translation_domain' => 'categorie',
                'required' => true,
                ))
            ->add('parent', 'entity', array(
                "label"     => 'fields.parent',
                'translation_domain' => 'categorie',
                'class'     => 'siteadminBundle:categorie',
                'property'  => 'nom',
                'required' => false,
                ))
            ->add('descriptif', 'insRichtext', array(
                'label' => 'fields.descriptif',
                'translation_domain' => 'categorie',
                'required' => false,
                ))
            ->add('couleur', 'insColorpicker', array(
                'label'     => 'fields.couleur',
                'translation_domain' => 'categorie',
                'required'  => true,
                ))
            ->add('pageweb', 'entity', array(
                "label"     => 'fields.pageweb',
                'translation_domain' => 'categorie',
                'class'     => 'siteadminBundle:pageweb',
                'property'  => 'nom',
                'required' => false,
                ))
            ->add('subEntitys', 'entity', array(
                "label"     => 'fields.subEntitys',
                'translation_domain' => 'categorie',
                'class'     => 'siteadminBundle:baseSubEntity',
                'property'  => 'nom',
                'multiple'  => true,
                'required' => false,
                ))
            ->add('url', 'text', array(
                'label' => 'fields.url',
                'translation_domain' => 'categorie',
                'required' => false,
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
            'data_class' => 'site\adminBundle\Entity\categorie'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'site_adminbundle_categorie';
    }
}
