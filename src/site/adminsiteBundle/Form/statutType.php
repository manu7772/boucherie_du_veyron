<?php

namespace site\adminsiteBundle\Form;

use Labo\Bundle\AdminBundle\Form\baseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Transformer
use Symfony\Component\Form\CallbackTransformer;
// User
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage as SecurityContext;
// Paramétrage de formulaire
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use site\adminsiteBundle\Entity\statut;

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
				'attr' => array(
					'data-height' => 140,
					)
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
            	'placeholder'   => 'form.select',
            	'attr'		=> array(
            		'class'			=> 'select2',
            		),
            	))
            ->add('bundles', 'choice', array(
            	'label' => 'fields.bundles',
            	'translation_domain' => 'statut',
            	'required'	=> true,
            	'multiple'	=> true,
            	"choices"   => $statut->getBundleChoices(),
            	'placeholder'   => 'form.select',
            	'attr'		=> array(
            		'class'			=> 'select2',
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
			'data_class' => 'site\adminsiteBundle\Entity\statut'
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminsitebundle_statut';
	}
}
