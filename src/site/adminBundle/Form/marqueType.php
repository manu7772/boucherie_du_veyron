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

class marqueType extends baseType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		$this->initBuilder($builder);
		// Builder…
		$builder
			->add('nom', 'text', array(
				'label' => 'marque.name',
				'translation_domain' => 'messages',
				'required' => true,
				))
			->add('descriptif', 'insRichtext', array(
				'label' => 'form.descriptif',
				'translation_domain' => 'messages',
				'required' => false,
				))
			->add('statut', 'entity', array(
				'class'     => 'siteadminBundle:statut',
				'property'  => 'nom',
				'multiple'  => false,
				"label"     => 'Statut'
				))
			->add('logo', new mediaType($this->controller), array(
				'label' => 'table.col.visuel',
				'translation_domain' => 'messages',
				'required' => false,
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
			'data_class' => 'site\adminBundle\Entity\marque'
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminbundle_marque';
	}
}
