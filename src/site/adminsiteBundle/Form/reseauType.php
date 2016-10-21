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

class reseauType extends baseType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		$this->initBuilder($builder);
		$this->imagesData = array(
			'image' => array(
				'owner' => 'reseau:image'
				),
			'logo' => array(
				'owner' => 'reseau:logo'
				),
			);
		// Builder…
		$builder
			->add('nom', 'text', array(
				'label' => 'fields.nom',
				'translation_domain' => 'reseau',
				'required' => true,
				))
            ->add('couleur', 'insColorpicker', array(
            	'label'		=> 'fields.couleur',
				'translation_domain' => 'reseau',
                'required'  => true,
            	))
			->add('adresse', new adresseType($this->controller), array(
				'label' => 'name',
				'translation_domain' => 'adresse',
				'required' => false,
				))
			// ->add('statut', 'entity', array(
			// 	'class'     => 'LaboAdminBundle:statut',
			// 	'choice_label'  => 'nom',
			// 	'multiple'  => false,
			// 	"label"     => 'name',
			// 	'translation_domain' => 'statut',
			// 	"query_builder" => function($repo) {
			// 		if(method_exists($repo, 'defaultValsListClosure'))
			// 			return $repo->defaultValsListClosure($this->controller);
			// 			else return $repo->findAllClosure();
			// 		},
			// 	))
			->add('descriptif', 'insRichtext', array(
				'label' => 'fields.descriptif',
				'translation_domain' => 'reseau',
				'required' => false,
				'attr' => array(
					'data-height' => 140,
					)
				))
			->add('image', new cropperType($this->controller, array('image' => $this->imagesData['image'])), array(
				'label' => 'fields.image',
				'translation_domain' => 'reseau',
				'required' => false,
				))
			->add('logo', new cropperType($this->controller, array('image' => $this->imagesData['logo'])), array(
				'label' => 'fields.logo',
				'translation_domain' => 'reseau',
				'required' => false,
				))

		;
		// ajoute les valeurs hidden, passés en paramètre
		$this->addHiddenValues($builder, true);
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'site\adminsiteBundle\Entity\reseau'
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminsitebundle_reseau';
	}
}
