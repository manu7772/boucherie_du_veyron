<?php
// src/site/UserBundle/Form/Type/ProfileFormType.php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace site\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraint\UserPassword as OldUserPassword;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
// Paramétrage de formulaire
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use site\UserBundle\Entity\User;
use Labo\Bundle\AdminBundle\Form\imageType;
use site\adminsiteBundle\Form\cropperType;

use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

class ProfileFormType extends BaseType {

	private $class;

	/**
	 * @param string $class The User class name
	 */
	public function __construct($class)
	{
		parent::__construct($class);
		$this->class = $class;
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		// if (class_exists('Symfony\Component\Security\Core\Validator\Constraints\UserPassword')) {
		//     $constraint = new UserPassword();
		// } else {
		//     // Symfony 2.1 support with the old constraint class
		//     $constraint = new OldUserPassword();
		// }

		$this->buildUserForm($builder, $options);
		$this->imagesData = array(
			'image' => array(
				'owner' => 'User:avatar'
				),
			)
		;

		$entity = new User();
		// $themesList = array_values($entity->getAdminskins());
		// $themesListKeys = array_keys($entity->getAdminskins());

		$builder
			// ->add('current_password', 'password', array(
			//     'label' => 'form.current_password',
			//     'translation_domain' => 'FOSUserBundle',
			//     'mapped' => false,
			//     'constraints' => $constraint,
			//     ))
			->add('nom', 'text', array(
				'translation_domain' => 'siteUserBundle',
				'label'     => 'fields.nom',
				'label_attr' => array('class' => 'text-muted'),
				'required'  => false,
				'attr' => array(
					'class' => 'input-sm form-control',
					),
				))
			->add('prenom', 'text', array(
				'translation_domain' => 'siteUserBundle',
				'label'     => 'fields.prenom',
				'label_attr' => array('class' => 'text-muted'),
				'required'  => false,
				'attr' => array(
					'class' => 'input-sm form-control',
					),
				))
			// ->add('langue', 'text', array(
			// 	'translation_domain' => 'siteUserBundle',
			// 	"required" => true,
			// 	"label" => "fields.lang",
			// 	'label_attr' => array('class' => 'text-muted'),
			// 	'attr' => array(
			// 		'class' => 'input-sm form-control',
			// 		),
			// 	))
			->add('avatar', new cropperType(null, array('image' => $this->imagesData['image'])), array(
				'translation_domain' => 'siteUserBundle',
				'label' => 'fields.avatar',
				'required' => false,
				))
		;

		$builder->addEventListener(
			FormEvents::PRE_SET_DATA, function(FormEvent $event) {
				$user = $event->getData();
				$form = $event->getForm();

				if(is_object($user)) {
					$roles = $user->getRoles();
					$validRoles = array("ROLE_ADMIN", "ROLE_SUPER_ADMIN", "ROLE_EDITOR", "ROLE_TRANSLATOR");
					if(count(array_intersect($roles, $validRoles)) > 0) {
						$form
							->add('adminhelp', 'insCheck', array(
								'translation_domain' => 'siteUserBundle',
								"required" => false,
								"label" => "fields.help",
								'label_attr' => array('class' => 'text-muted'),
								'attr' => array(
									'class' => 'input-sm form-control',
									),
								))
							->add('mail_sitemessages', 'insCheck', array(
								'translation_domain' => 'siteUserBundle',
								"required" => false,
								"label" => "fields.mail_sitemessages",
								'label_attr' => array('class' => 'text-muted'),
								'attr' => array(
									'class' => 'input-sm form-control',
									),
								))
							->add('admintheme', 'choice', array(
								'translation_domain' => 'siteUserBundle',
								"required" => true,
								"label" => "fields.theme",
								'label_attr' => array('class' => 'text-muted'),
								"choice_list" => new ChoiceList(
									array_keys($user->getAdminskins()),
									array_values($user->getAdminskins())
									),
								'attr' => array(
									'class' => 'input-sm form-control chosen-select chosen-select-width chosen-select-no-results',
									'placeholder' => 'form.select',
									),
								))
						;
					}
				}
			}
		);

		// $builder->add('submit', 'submit', array(
		// 	'label' => 'form.enregistrer',
		// 	'translation_domain' => 'messages',
		// 	'attr' => array(
		// 		'class' => "btn btn-md btn-block btn-info",
		// 		),
		// 	))
		// ;


	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'site\UserBundle\Entity\User',
			'intention'  => 'profile',
		));
	}

	public function getName()
	{
		return 'site_user_profile';
	}

	/**
	 * Builds the embedded form representing the user.
	 *
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	protected function buildUserForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('username', null, array(
				'label' => 'form.username',
				'label_attr' => array('class' => 'text-muted'),
				'translation_domain' => 'siteUserBundle',
				'attr' => array(
					'class' => 'input-sm form-control',
					),
				))
			->add('email', 'email', array(
				'label' => 'form.email',
				'label_attr' => array('class' => 'text-muted'),
				'translation_domain' => 'siteUserBundle',
				'attr' => array(
					'class' => 'input-sm form-control',
					),
				))
		;
	}
}


