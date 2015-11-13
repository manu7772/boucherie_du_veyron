<?php
// src/site/UserBundle/Form/Type/RegistrationFormType.php

namespace site\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

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
            ->add('email', 'email', array(
                'label' => 'fields.email',
                'translation_domain' => 'siteUserBundle',
                'attr'      => array(
                    'class'         => 'input-sm form-full',
                    'placeholder'   => 'fields.email',
                    ),
                ))
            ->add('username', null, array(
                'label' => 'fields.username',
                'translation_domain' => 'siteUserBundle',
                'attr'      => array(
                    'class'         => 'input-sm form-full',
                    'placeholder'   => 'fields.username',
                    ),
                ))
            ->add('nom', 'text', array(
                'label'     => 'fields.nom',
                'translation_domain' => 'siteUserBundle',
                'required'  => false,
                'attr'      => array(
                    'class'         => 'input-sm form-full',
                    'placeholder'   => 'fields.nom',
                    ),
                ))
            ->add('prenom', 'text', array(
                'label'     => 'fields.prenom',
                'translation_domain' => 'siteUserBundle',
                'required'  => false,
                'attr'      => array(
                    'class'         => 'input-sm form-full',
                    'placeholder'   => 'fields.prenom',
                    ),
                ))
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'options' => array('translation_domain' => 'siteUserBundle'),
                'first_options' => array(
                    'label' => 'form.password',
                    'attr'  => array('class' => 'input-sm form-full'),
                    ),
                'second_options' => array(
                    'label' => 'form.password_confirmation',
                    'attr'  => array('class' => 'input-sm form-full'),
                    ),
                'invalid_message' => 'fos_user.password.mismatch',
                ))
        ;
	}

	public function getName() {
		return 'site_user_registration';
	}

}