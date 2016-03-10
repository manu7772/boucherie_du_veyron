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
		// $this->imagesData = array(
		// 	'image' => array(
		// 		'owner' => 'fiche:image'
		// 		),
		// 	)
		// ;
		// Builder…
		$fiche = new fiche();
		$builder
			->add('nom', 'text', array(
				'label' => 'fields.nom',
				'translation_domain' => 'fiche',
				'required' => true,
				))
			->add('accroche', 'text', array(
				'label' => 'fields.accroche',
				'translation_domain' => 'fiche',
				'required' => false,
				))
			->add('descriptif', 'insRichtext', array(
				'label' => 'fields.descriptif',
				'translation_domain' => 'fiche',
				'required' => false,
				))
			->add('niveau', 'choice', array(
				"required"  => true,
				"label"     => 'fields.niveau',
				'translation_domain' => 'fiche',
				'multiple'  => false,
				// 'expanded'  => true,
				"choices"   => $fiche->getListeNiveaux(),
				))
			->add('duree', 'choice', array(
				"required"  => true,
				"label"     => 'fields.duree',
				'translation_domain' => 'fiche',
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
				"label"     => 'name',
				'translation_domain' => 'statut',
				"query_builder" => function($repo) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities);
						else return $repo->findAllClosure();
					},
				))
			->add('image', new cropperType($this->controller, array('image' => array('owner' => 'fiche:image'))), array(
				'label' => 'fields.image',
				'translation_domain' => 'fiche',
				'required' => false,
				))
			->add('tags', 'entity', array(
				'label'		=> 'name_s',
				'translation_domain' => 'tag',
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
				'label'		=> 'name_s',
				'translation_domain' => 'article',
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
				'label'		=> 'fields.datePublication',
				'translation_domain' => 'fiche',
				"required"  => false,
				))
			->add('dateExpiration', 'insDatepicker', array(
				'label'		=> 'fields.dateExpiration',
				'translation_domain' => 'fiche',
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
