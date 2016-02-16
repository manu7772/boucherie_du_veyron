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
use site\adminBundle\Entity\fiche;

class ficheType extends baseType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		$this->initBuilder($builder);
		// Builder…
		$fiche = new fiche();
		$builder
			->add('nom', 'text', array(
				'label' => 'form.nom',
				'translation_domain' => 'messages',
				'required' => true,
				))
			->add('accroche', 'text', array(
				'label' => 'form.accroche',
				'translation_domain' => 'messages',
				'required' => false,
				))
			->add('descriptif', 'insRichtext', array(
				'label' => 'form.descriptif',
				'translation_domain' => 'messages',
				'required' => false,
				))
			->add('niveau', 'choice', array(
				"required"  => true,
				"label"     => 'Niveau',
				'multiple'  => false,
				'expanded'  => true,
				"choices"   => $fiche->getListeNiveaux(),
				))
			->add('duree', 'choice', array(
				"required"  => true,
				"label"     => 'Temps de réalisation',
				'multiple'  => false,
				'expanded'  => false,
				"choices"   => $fiche->getDurees(),
				))
			// ->add('dateCreation')
			// ->add('dateMaj')
			// ->add('slug')
			->add('statut', 'entity', array(
				'class'     => 'siteadminBundle:statut',
				'property'  => 'nom',
				'multiple'  => false,
				"label"     => 'Statut'
				))
			->add('image', new imageType($this->controller), array(
				'label' => 'table.col.visuel',
				'translation_domain' => 'messages',
				'required' => false,
				))
			->add('tags', 'entity', array(
				'label'		=> 'tag.name_s',
				'translation_domain' => 'messages',
				'property'	=> 'nom',
				'class'		=> 'siteadminBundle:tag',
				'multiple'	=> true,
				'required'	=> false,
				'attr'		=> array(
					'class'			=> 'chosen-select chosen-select-width chosen-select-no-results',
					'placeholder'	=> 'form.select',
					),
				))
			->add('articles', 'entity', array(
				'label'		=> 'article.name_s',
				'translation_domain' => 'messages',
				'property'	=> 'nom',
				'class'		=> 'siteadminBundle:article',
				'multiple'	=> true,
				'required'	=> false,
				'attr'		=> array(
					'class'			=> 'chosen-select chosen-select-width chosen-select-no-results',
					'placeholder'	=> 'form.select',
					),
				))
			->add('datePublication', 'insDatepicker', array(
				"required"  => false,
				))
			->add('dateExpiration', 'insDatepicker', array(
				"required"  => false,
				))
		;
		// ajoute les valeurs hidden, passés en paramètre
		$this->addHiddenValues($builder, true);
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'site\adminBundle\Entity\fiche'
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'site_adminbundle_fiche';
	}
}
