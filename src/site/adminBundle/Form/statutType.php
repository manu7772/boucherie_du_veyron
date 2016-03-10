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
use site\adminBundle\Entity\statut;

class statutType extends baseType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		$this->initBuilder($builder);
		$statut = new statut();
		// Builder…
		$builder
			->add('nom', 'text', array(
				'label' => 'fields.nom',
				'translation_domain' => 'statut',
				'required' => true,
				))
			->add('descriptif', 'insRichtext', array(
				'label' => 'fields.descriptif',
				'translation_domain' => 'statut',
				'required' => false,
				))
            ->add('couleur', 'insColorpicker', array(
            	'label' => 'fields.couleur',
            	'translation_domain' => 'statut',
                'required'  => true,
            	))
            ->add('niveau', 'choice', array(
            	'label' => 'fields.niveau',
            	'translation_domain' => 'statut',
            	'required'	=> true,
            	'multiple'	=> false,
            	"choices"   => $statut->getRoleChoices($this->user),
            	))
            ->add('bundles', 'choice', array(
            	'label' => 'fields.bundles',
            	'translation_domain' => 'statut',
            	'required'	=> true,
            	'multiple'	=> true,
            	"choices"   => $statut->getBundleChoices(),
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
			'data_class' => 'site\adminBundle\Entity\statut'
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminbundle_statut';
	}
}
