<?php
// src/site/UserBundle/Form/Type/RegistrationFormType.php

namespace site\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use site\adminsiteBundle\Form\adresseType;

class RegistrationFormType extends BaseType {

    private $class;

    public function __construct($class) {
    	parent::__construct($class);
        $this->class = $class;
    }

	public function buildForm(FormBuilderInterface $builder, array $options) {
		parent::buildForm($builder, $options);
		// add your custom field
		$builder
            ->add('username', null, array(
                'label' => 'fields.username',
                'label_attr' => array('class' => 'text-muted'),
                'translation_domain' => 'siteUserBundle',
                'attr'      => array(
                    'class'         => 'input-sm form-control',
                    'placeholder'   => 'fields.username',
                    ),
                ))
            ->add('email', 'email', array(
                'label' => 'fields.email',
                'label_attr' => array('class' => 'text-muted'),
                'translation_domain' => 'siteUserBundle',
                'attr'      => array(
                    'class'         => 'input-sm form-control',
                    'placeholder'   => 'fields.email',
                    ),
                ))
            ->add('nom', 'text', array(
                'label'     => 'fields.nom',
                'label_attr' => array('class' => 'text-muted'),
                'translation_domain' => 'siteUserBundle',
                'required'  => false,
                'attr'      => array(
                    'class'         => 'input-sm form-control',
                    'placeholder'   => 'fields.nom',
                    ),
                ))
            ->add('prenom', 'text', array(
                'label'     => 'fields.prenom',
                'label_attr' => array('class' => 'text-muted'),
                'translation_domain' => 'siteUserBundle',
                'required'  => false,
                'attr'      => array(
                    'class'         => 'input-sm form-control',
                    'placeholder'   => 'fields.prenom',
                    ),
                ))
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'options' => array('translation_domain' => 'siteUserBundle'),
                'first_options' => array(
                    'label' => 'form.password',
                    'label_attr' => array('class' => 'text-muted'),
                    'attr'  => array('class' => 'input-sm form-control'),
                    ),
                'second_options' => array(
                    'label' => 'form.password_confirmation',
                    'label_attr' => array('class' => 'text-muted'),
                    'attr'  => array('class' => 'input-sm form-control'),
                    ),
                'invalid_message' => 'fos_user.password.mismatch',
                ))
            ->add('telephone', 'text', array(
                'translation_domain' => 'siteUserBundle',
                'label'     => 'fields.telephone',
                'label_attr' => array('class' => 'text-muted'),
                'required'  => false,
                'attr' => array(
                    'class' => 'input-sm form-control',
                    'placeholder'   => 'fields.telephone',
                    ),
                ))
            // ->add('adresse', new adresseType(), array(
            //     'label' => 'Adresse',
            //     'required' => false,
            //     ))
            // ->add('adresseLivraison', new adresseType(), array(
            //     'label' => 'Adresse de livraison',
            //     'required' => false,
            //     ))
        ;
	}

	public function getName() {
		return 'site_user_registration';
	}

}