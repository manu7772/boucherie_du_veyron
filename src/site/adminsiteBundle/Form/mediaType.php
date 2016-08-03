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

class mediaType extends baseType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		$this->initBuilder($builder);
    	// Builder…
    	$builder->getData() == null ? $deletable = true : $deletable = false;
    	// nom à ne pas mettre si imbriqué
    	if(!($builder->getData() == null))
		$builder
			->add('nom', 'text', array(
				'label'         => 'form.nom',
				'translation_domain' => 'messages',
				'disabled'      => false,
				'required'		=> false,
				))
			;
		$builder
			->add('originalnom', 'hidden', array(
				'required'		=> false,
				'translation_domain' => 'messages',
				))
			->add('infoForPersist', 'hidden', array(
				'required'		=> false,
				'translation_domain' => 'messages',
				))
			->add('binaryFile', 'filecropper', array(
				'label' => 'form.telechargement',
				'translation_domain' => 'messages',
				'required'		=> false,
				'cropper' => array(
					'options' => array(
						"flipable" => true,
						"zoomable" => true,
						"rotatable" => true,
						),
					'deletable' => $deletable,
					'format' => array('x' => 800, 'y' => 600),
					),
				'attr' => array(
					'cropper-formats' => json_encode(array(
						array("width" => 800, "height" => 600),
						// array("width" => 600, "height" => 800),
						)
					),
					'cropper-options' => json_encode(array(
						"rotatable" => true,
						)
					),
					'filename-copy' => 'originalnom',
					'cropper-accept' => ".jpeg,.jpg,.png,.gif",
					'deletable' => $deletable,
					),
				));
		;

		// ajoute les valeurs hidden, passés en paramètre
		$this->addHiddenValues($builder, true);
		// $this->addSubmit($builder);
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'site\adminsiteBundle\Entity\media'
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'site_adminsitebundle_media';
	}
}
