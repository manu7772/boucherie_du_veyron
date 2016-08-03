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

class boutiqueType extends baseType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		$this->initBuilder($builder);
		$this->imagesData = array(
			'image' => array(
				'owner' => 'boutique:image'
				),
			'logo' => array(
				'owner' => 'boutique:logo'
				),
			);
		// Builder…
		$builder
			->add('nom', 'text', array(
				'label' => 'fields.nom',
				'translation_domain' => 'boutique',
				'required' => true,
				))
			->add('telfixe', 'text', array(
				'label' => 'fields.telfixe',
				'translation_domain' => 'boutique',
				'required' => false,
				))
			->add('mobile', 'text', array(
				'label' => 'fields.mobile',
				'translation_domain' => 'boutique',
				'required' => false,
				))
			->add('email', 'text', array(
				'label' => 'fields.email',
				'translation_domain' => 'boutique',
				'required' => false,
				))
			->add('adresse', new adresseType($this->controller), array(
				'label' => 'fields.adresse',
				'translation_domain' => 'boutique',
				'required' => false,
				))
			->add('image', new cropperType($this->controller, array('image' => $this->imagesData['image'])), array(
				'label' => 'fields.image',
				'translation_domain' => 'boutique',
				'required' => false,
				))
			->add('logo', new cropperType($this->controller, array('image' => $this->imagesData['logo'])), array(
				'label' => 'fields.logo',
				'translation_domain' => 'boutique',
				'required' => false,
				))
			->add('descriptif', 'insRichtext', array(
				'label' => 'fields.descriptif',
				'translation_domain' => 'boutique',
				'required' => false,
				'attr' => array(
					'data-height' => 140,
					)
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
			'data_class' => 'site\adminsiteBundle\Entity\boutique'
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminsitebundle_boutique';
	}
}
