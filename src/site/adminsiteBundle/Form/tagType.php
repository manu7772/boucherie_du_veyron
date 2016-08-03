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
            //     'choice_label'  => 'nom',
            //     'class'     => 'LaboAdminBundle:pageweb',
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
            //     'choice_label'  => 'nom',
            //     'class'     => 'LaboAdminBundle:article',
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
            //     'choice_label'  => 'nom',
            //     'class'     => 'LaboAdminBundle:fiche',
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
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'site\adminsiteBundle\Entity\tag'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'site_adminsitebundle_tag';
    }
}
