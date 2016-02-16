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

class tagType extends baseType {

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
                'label'         => 'form.nom',
                'translation_domain' => 'messages',
                'required'      => true,
                ))
            // ->add('pagewebs', 'entity', array(
            //     "label"     => 'pageweb.name_s',
            //     'translation_domain' => 'messages',
            //     'property'  => 'nom',
            //     'class'     => 'siteadminBundle:pageweb',
            //     'multiple'  => true,
            //     'expanded'  => false,
            //     "required"  => false,
            //     'attr'      => array(
            //         'class'         => 'chosen-select chosen-select-width chosen-select-no-results',
            //         'placeholder'   => 'form.select',
            //         ),
            //     ))
            // ->add('articles', 'entity', array(
            //     "label"     => 'article.name_s',
            //     'translation_domain' => 'messages',
            //     'property'  => 'nom',
            //     'class'     => 'siteadminBundle:article',
            //     'multiple'  => true,
            //     'expanded'  => false,
            //     "required"  => false,
            //     'attr'      => array(
            //         'class'         => 'chosen-select chosen-select-width chosen-select-no-results',
            //         'placeholder'   => 'form.select',
            //         ),
            //     ))
            // ->add('fiches', 'entity', array(
            //     "label"     => 'fiche.name_s',
            //     'translation_domain' => 'messages',
            //     'property'  => 'nom',
            //     'class'     => 'siteadminBundle:fiche',
            //     'multiple'  => true,
            //     'expanded'  => false,
            //     "required"  => false,
            //     'attr'      => array(
            //         'class'         => 'chosen-select chosen-select-width chosen-select-no-results',
            //         'placeholder'   => 'form.select',
            //         ),
            //     ))
        ;
        // ajoute les valeurs hidden, passés en paramètre
        $this->addHiddenValues($builder, true);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'site\adminBundle\Entity\tag'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'site_adminbundle_tag';
    }
}
