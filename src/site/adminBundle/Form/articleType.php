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

use site\adminBundle\Entity\image;
use site\adminBundle\Form\imageType;

class articleType extends baseType {

	protected $imagesData;

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		$this->initBuilder($builder);
		$this->imagesData = array(
			'image' => array(
				'owner' => 'article:image'
				),
			);
		// Builder…
		$builder
			->add('nom', 'text', array(
				'label' => 'fields.nom',
				'translation_domain' => 'article',
				'required' => true,
				))
			->add('accroche', 'text', array(
				'label' => 'fields.accroche',
				'translation_domain' => 'article',
				'required' => false,
				))
			->add('descriptif', 'insRichtext', array(
				'label' => 'fields.descriptif',
				'translation_domain' => 'article',
				'required' => false,
				))
			// ->add('dateCreation')
			// ->add('dateMaj')
			->add('refFabricant', 'text', array(
				'label' => 'fields.refFabricant',
				'translation_domain' => 'article',
				'required' => false,
				))
			->add('prix', 'money', array(
				'label'		=> 'fields.prixTTC',
				'translation_domain' => 'article',
				"required"  => false,
				))
			->add('prixHT', 'money', array(
				'label'		=> 'fields.prixHT',
				'translation_domain' => 'article',
				"required"  => false,
				))
			->add('tauxTva', 'entity', array(
				"label"     => 'name',
				'translation_domain' => 'tauxTva',
				'class'     => 'siteadminBundle:tauxTva',
				'property'  => 'nomlong',
				'multiple'  => false,
				"query_builder" => function($repo) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities);
						else return $repo->findAllClosure();
					},
				))
			->add('statut', 'entity', array(
				"label"     => 'name',
				'translation_domain' => 'statut',
				'class'     => 'siteadminBundle:statut',
				'property'  => 'nom',
				'multiple'  => false,
				"query_builder" => function($repo) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities);
						else return $repo->findAllClosure();
					},
				))
			->add('marque', 'entity', array(
				"label"     => 'name',
				'translation_domain' => 'marque',
				'class'     => 'siteadminBundle:marque',
				'property'  => 'nom',
				'multiple'  => false,
				'required' => false,
				"query_builder" => function($repo) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities);
						else return $repo->findAllClosure();
					},
				))
			->add('reseaus', 'entity', array(
				"label"     => 'name_s',
				'translation_domain' => 'reseau',
				'class'     => 'siteadminBundle:reseau',
				'property'  => 'nom',
				'multiple'  => true,
				'required' => false,
				"query_builder" => function($repo) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities);
						else return $repo->findAllClosure();
					},
				'attr'		=> array(
					'class'			=> 'chosen-select chosen-select-width chosen-select-no-results',
					'placeholder'	=> 'form.select',
					),
				))
			// 1 image :
			->add('image', new cropperType($this->controller, array('image' => $this->imagesData['image'])), array(
				'label' => 'fields.image',
				'translation_domain' => 'article',
				'required' => false,
				))
			// Images collection :
			// ->add('images', 'multiCollection', array(
			// 	'label' => 'table.col.visuel',
			// 	'translation_domain' => 'article',
			// 	'required' => false,
			// 	'type' => new imageType($this->controller),
			// 	'allow_add' => true,
			// 	'allow_delete' => true,
			// 	'by_reference'  => false,
			// 	'attr'          => array(
			// 		'data-columns'      => "0,2",
			// 		),
			// 	))
			// ->add('fichierPdf')
			// ->add('ficheTechniquePdf')
			// ->add('categories')
			// ->add('fiches')
			->add('fiches', 'entity', array(
				"label"		=> 'name_s',
				'translation_domain' => 'fiche',
				'property'	=> 'nom',
				'class'		=> 'siteadminBundle:fiche',
				'multiple'	=> true,
				'expanded'	=> false,
				"required"	=> false,
				'attr'		=> array(
					'class'			=> 'chosen-select chosen-select-width chosen-select-no-results',
					'placeholder'	=> 'form.select',
					),
				"query_builder" => function($repo) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities);
						else return $repo->findAllClosure();
					},
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
				"query_builder" => function($repo) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities);
						else return $repo->findAllClosure();
					},
				))
			// ->add('tags', 'multiCollection', array(
			// 	'label' => 'name_s',
			// 	'translation_domain' => 'tag',
			// 	'required' => false,
			// 	'type' => new tagType($this->controller),
			// 	'allow_add' => true,
			// 	'allow_delete' => true,
			// 	'by_reference'  => false,
			// 	'attr'          => array(
			// 		'data-columns'      => "0",
			// 		),
			// 	))
			// ->add('articlesParents')
			->add('articlesLies', 'entity', array(
				"label"		=> 'fields.artlink',
				'translation_domain' => 'article',
				'property'	=> 'nom',
				'class'		=> 'siteadminBundle:article',
				'multiple'	=> true,
				'expanded'	=> false,
				"required"	=> false,
				'attr'		=> array(
					'class'			=> 'chosen-select chosen-select-width chosen-select-no-results',
					'placeholder'	=> 'form.select',
					),
				"query_builder" => function($repo) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities);
						else return $repo->findAllClosure();
					},
				))
		;

		// $builder->addEventListener(
		// 	FormEvents::PRE_SET_DATA, function (FormEvent $event) {
		// 		$data = $event->getData();
		// 		$form = $event->getForm();
		// 		// à conserver !! ci-dessous
		// 		if(null === $data) return;

		// 	}
		// );

		// ajoute les valeurs hidden, passés en paramètre
		$this->addHiddenValues($builder, true);
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'site\adminBundle\Entity\article'
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminbundle_article';
	}
}
