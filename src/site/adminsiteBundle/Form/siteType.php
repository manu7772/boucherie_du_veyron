<?php

namespace site\adminsiteBundle\Form;

use Labo\Bundle\AdminBundle\Form\baseType;
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

use site\adminsiteBundle\Entity\image;
use site\adminsiteBundle\Form\imageType;

class siteType extends baseType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        // ajout de action si défini
        $this->initBuilder($builder);
        $this->imagesData = array(
            'image' => array(
                'owner' => 'site:image'
                ),
            'logo' => array(
                'owner' => 'site:logo'
                ),
            'favicon' => array(
                'owner' => 'site:favicon'
                ),
            'adminLogo' => array(
                'owner' => 'site:adminLogo'
                ),
            );
        // Builder…
        $builder
            ->add('nom', 'text', array(
                'label' => 'fields.nom',
                'translation_domain' => 'site',
                'required' => true,
                ))
            ->add('accroche', 'text', array(
                'label' => 'fields.accroche',
                'translation_domain' => 'site',
                'required' => false,
                ))
            ->add('descriptif', 'insRichtext', array(
                'label' => 'fields.descriptif',
                'translation_domain' => 'site',
                'required' => false,
                'attr' => array(
                    'data-height' => 140,
                    )
                ))
            ->add('couleur', 'insColorpicker', array(
                'label'     => 'fields.couleur',
                'translation_domain' => 'site',
                'required'  => true,
                ))
            ->add('menuNav', 'entity', array(
                "label"     => 'fields.menuNav',
                'translation_domain' => 'site',
                'class'     => 'siteadminsiteBundle:categorie',
                'property'  => 'nom',
                'multiple'  => false,
                'required' => false,
                'group_by' => 'parent.nom',
                "query_builder" => function($repo) {
                    if(method_exists($repo, 'getElementsByTypeButRoot'))
                        return $repo->getElementsByTypeButRoot(array("menu"));
                        else return $repo->findAllClosure();
                    },
                'placeholder'   => 'form.select',
                'attr'      => array(
                    'class'         => 'select2',
                    ),
                ))
            ->add('menuArticle', 'entity', array(
                "label"     => 'fields.menuArticle',
                'translation_domain' => 'site',
                'class'     => 'siteadminsiteBundle:categorie',
                'property'  => 'nom',
                'multiple'  => false,
                'required' => false,
                'group_by' => 'parent.nom',
                "query_builder" => function($repo) {
                    if(method_exists($repo, 'getElementsBySubTypeButRoot'))
                        return $repo->getElementsBySubTypeButRoot(array('article'));
                        else return $repo->findAllClosure();
                    },
                'placeholder'   => 'form.select',
                'attr'      => array(
                    'class'         => 'select2',
                    ),
                ))
            ->add('diaporamaintro', 'entity', array(
                "label"     => 'fields.diaporamaintro',
                'translation_domain' => 'site',
                'class'     => 'siteadminsiteBundle:categorie',
                'property'  => 'nom',
                'multiple'  => false,
                'required' => false,
                'group_by' => 'parent.nom',
                "query_builder" => function($repo) {
                    if(method_exists($repo, 'getElementsBySubTypeButRoot'))
                        return $repo->getElementsBySubTypeButRoot(array('article', 'pageweb', 'fiche'));
                        else return $repo->findAllClosure();
                    },
                'placeholder'   => 'form.select',
                'attr'      => array(
                    'class'         => 'select2',
                    ),
                ))
            ->add('categorieArticles', 'entity', array(
                "label"     => 'fields.categorieArticles',
                'translation_domain' => 'site',
                'class'     => 'siteadminsiteBundle:categorie',
                'property'  => 'nom',
                'multiple'  => true,
                'required' => false,
                'group_by' => 'parent.nom',
                "query_builder" => function($repo) {
                    if(method_exists($repo, 'getElementsBySubTypeButRoot'))
                        return $repo->getElementsBySubTypeButRoot(array('article', 'fiche'));
                        else return $repo->findAllClosure();
                    },
                'placeholder'   => 'form.select',
                'attr'      => array(
                    'class'         => 'select2',
                    ),
                ))
            ->add('categorieFooters', 'entity', array(
                "label"     => 'fields.categorieFooters',
                'translation_domain' => 'site',
                'class'     => 'siteadminsiteBundle:categorie',
                'property'  => 'nom',
                'multiple'  => true,
                'required' => false,
                'group_by' => 'parent.nom',
                "query_builder" => function($repo) {
                    if(method_exists($repo, 'getElementsBySubTypeButRoot'))
                        return $repo->getElementsBySubTypeButRoot(array('article', 'pageweb', 'fiche'));
                        else return $repo->findAllClosure();
                    },
                'placeholder'   => 'form.select',
                'attr'      => array(
                    'class'         => 'select2',
                    ),
                ))
            ->add('boutiques', 'entity', array(
                "label"     => 'fields.boutiques',
                'translation_domain' => 'site',
                'class'     => 'siteadminsiteBundle:boutique',
                'property'  => 'nom',
                'multiple'  => true,
                'required' => false,
                'placeholder'   => 'form.select',
                'attr'      => array(
                    'class'         => 'select2',
                    ),
                ))
            ->add('collaborateurs', 'entity', array(
                "label"     => 'fields.collaborateurs',
                'translation_domain' => 'site',
                'class'     => 'siteUserBundle:User',
                'property'  => 'username',
                'multiple'  => true,
                'required' => false,
                'group_by' => 'bestRole',
                'placeholder'   => 'form.select',
                'attr'      => array(
                    'class'         => 'select2',
                    ),
                ))
            ->add('image', new cropperType($this->controller, array('image' => $this->imagesData['image'])), array(
                'label' => 'fields.image',
                'translation_domain' => 'site',
                'required' => false,
                ))
            ->add('logo', new cropperType($this->controller, array('image' => $this->imagesData['logo'])), array(
                'label' => 'fields.logo',
                'translation_domain' => 'site',
                'required' => false,
                ))
            ->add('favicon', new cropperType($this->controller, array('image' => $this->imagesData['favicon'])), array(
                'label' => 'fields.favicon',
                'translation_domain' => 'site',
                'required' => false,
                ))
            ->add('adminLogo', new cropperType($this->controller, array('image' => $this->imagesData['adminLogo'])), array(
                'label' => 'fields.adminLogo',
                'translation_domain' => 'site',
                'required' => false,
                ))
        ;
        // $builder->addEventListener(
        //  FormEvents::PRE_SET_DATA, function (FormEvent $event) {
        //      $data = $event->getData();
        //      $form = $event->getForm();
        //      // à conserver !! ci-dessous
        //      if(null === $data) return;

        //  }
        // );

        // ajoute les valeurs hidden, passés en paramètre
        $this->addHiddenValues($builder, true);
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'site\adminsiteBundle\Entity\site'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'site_adminsitebundle_site';
    }
}
