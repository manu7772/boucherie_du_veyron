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
use site\adminsiteBundle\Entity\rawfile;

class rawfileType extends baseType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		$this->initBuilder($builder);
		// $rawfile = new rawfile();
		// Builder…
		$builder
			->add('nom', 'text', array(
				'label' => 'fields.nom',
				'translation_domain' => 'rawfile',
				'required' => true,
				))
			->add('originalnom', 'text', array(
				'label' => 'fields.originalnom',
				'translation_domain' => 'rawfile',
				'required' => true,
				))
			->add('format', 'text', array(
				'label' => 'fields.format',
				'translation_domain' => 'rawfile',
				'required' => true,
				))
			->add('extension', 'text', array(
				'label' => 'fields.extension',
				'translation_domain' => 'rawfile',
				'required' => true,
				))
			->add('width', 'text', array(
				'label' => 'fields.width',
				'translation_domain' => 'rawfile',
				'required' => true,
				))
			->add('height', 'text', array(
				'label' => 'fields.height',
				'translation_domain' => 'rawfile',
				'required' => true,
				))
			->add('fileSize', 'text', array(
				'label' => 'fields.fileSize',
				'translation_domain' => 'rawfile',
				'required' => true,
				))
			->add('descriptif', 'insRichtext', array(
				'label' => 'fields.descriptif',
				'translation_domain' => 'rawfile',
				'required' => false,
				'attr' => array(
					'data-height' => 140,
					)
				))
			->add('created', 'insDatepicker', array(
				'label'		=> 'fields.created',
				'translation_domain' => 'fiche',
				"required"  => false,
				))
			// ->add('updated', 'insDatepicker', array(
			// 	'label'		=> 'fields.updated',
			// 	'translation_domain' => 'fiche',
			// 	"required"  => false,
			// 	))
		;
		// ajoute les valeurs hidden, passés en paramètre
		$this->addHiddenValues($builder, true);
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'site\adminsiteBundle\Entity\rawfile'
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminsitebundle_rawfile';
	}
}
