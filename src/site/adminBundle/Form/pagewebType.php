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
		// Builder…
		$builder
			->add('nom', 'text', array(
				'label' => 'form.nom',
				'translation_domain' => 'messages',
				'required' => true,
				))
			->add('code', 'insRichtext', array(
				'label' => 'form.code',
				'translation_domain' => 'messages',
				'required' => false,
				))
			->add('title', 'text', array(
				'label' => 'table.col.title',
				'translation_domain' => 'messages',
				'required' => true,
				))
			->add('titreh1', 'text', array(
				'label' => 'table.col.titreh1',
				'translation_domain' => 'messages',
				'required' => true,
				))
			// ->add('keywords', 'text', array(
			// 	'label' => 'table.col.keywords',
			//	'translation_domain' => 'messages',
			// 	'required' => false,
			// 	))
			->add('metadescription', 'text', array(
				'label' => 'table.col.metadescription',
				'translation_domain' => 'messages',
				'required' => false,
				))
			->add('modele', 'choice', array(
				'label' => 'table.col.modele',
				'translation_domain' => 'messages',
				'required' => true,
				'choice_list' => $this->pageweb->getPagewebChoices(),
				))
			// 1 image :
			->add('image', new imageType($this->controller), array(
				'label' => 'form.background',
				'translation_domain' => 'messages',
				'required' => false,
				))
			->add('tags', 'entity', array(
				'label' => 'tag.name_s',
				'translation_domain' => 'messages',
				'property' => 'nom',
				'class' => 'site\adminBundle\Entity\tag',
				'multiple' => true,
				'required' => false,
				'attr' => array(
					'class' => 'chosen-select chosen-select-width chosen-select-no-results',
					'placeholder' => 'form.select',
					),
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
