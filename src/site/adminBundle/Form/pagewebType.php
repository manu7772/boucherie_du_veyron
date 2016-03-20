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

use site\adminBundle\Form\imageType;

class pagewebType extends baseType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		$this->initBuilder($builder);
		$this->pageweb = $this->controller->get('aetools.aePageweb');
		$this->imagesData = array(
			'image' => array(
				'owner' => 'pageweb:image'
				),
			);
		// Builder…
		$builder
			->add('nom', 'text', array(
				'label' => 'fields.nom',
				'translation_domain' => 'pageweb',
				'required' => true,
				))
			->add('code', 'insRichtext', array(
				'label' => 'fields.code',
				'translation_domain' => 'pageweb',
				'required' => false,
				))
			->add('title', 'text', array(
				'label' => 'fields.title',
				'translation_domain' => 'pageweb',
				'required' => true,
				))
			->add('titreh1', 'text', array(
				'label' => 'fields.titreh1',
				'translation_domain' => 'pageweb',
				'required' => true,
				))
			// ->add('keywords', 'text', array(
			// 	'label' => 'fields.keywords',
			//	'translation_domain' => 'pageweb',
			// 	'required' => false,
			// 	))
			->add('metadescription', 'text', array(
				'label' => 'fields.metadescription',
				'translation_domain' => 'pageweb',
				'required' => false,
				))
			->add('modele', 'choice', array(
				'label' => 'fields.modele',
				'translation_domain' => 'pageweb',
				'required' => true,
				'choice_list' => $this->pageweb->getPagewebChoices(),
				))
			// 1 image :
			->add('image', new cropperType($this->controller, $this->imagesData), array(
				'label' => 'fields.image',
				'translation_domain' => 'pageweb',
				'required' => false,
				))
			->add('tags', 'entity', array(
				'label' => 'fields.tags',
				'translation_domain' => 'pageweb',
				'property' => 'nom',
				'class' => 'site\adminBundle\Entity\tag',
				'multiple' => true,
				'required' => false,
				'attr' => array(
					'class' => 'chosen-select chosen-select-width chosen-select-no-results',
					'placeholder' => 'form.select',
					),
				"query_builder" => function($repo) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities);
						else return $repo->findAllClosure();
					},
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
			'data_class' => 'site\adminBundle\Entity\pageweb'
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminbundle_pageweb';
	}
}
