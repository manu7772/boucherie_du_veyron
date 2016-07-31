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

use site\adminsiteBundle\Entity\image;
use site\adminsiteBundle\Form\imageType;

class articleType extends baseType {

	protected $imagesData;

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		$this->initBuilder($builder);
		$data = $builder->getData();
		$nestedAttributesParameters = $data->getNestedAttributesParameters();
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
				'attr' => array(
					'data-height' => 140,
					)
				))
			// ->add('icon', 'choice', array(
			// 	"required"  => false,
			// 	"label"     => 'fields.icon',
			// 	'translation_domain' => 'article',
			// 	'multiple'  => false,
			// 	"choices"   => $data->getListIcons(),
			// 	'placeholder'   => 'form.select',
			// 	'attr'      => array(
			// 		'class'         => 'select2',
			// 		'data-format'	=> 'formatState',
			// 		),
			// 	))
			// ->add('refFabricant', 'text', array(
			// 	'label' => 'fields.refFabricant',
			// 	'translation_domain' => 'article',
			// 	'required' => false,
			// 	))
			->add('vendable', 'checkbox', array(
				'label'		=> 'fields.vendable',
				'translation_domain' => 'article',
				"required"  => false,
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
				'class'     => 'siteadminsiteBundle:tauxTva',
				'property'  => 'nomlong',
				'multiple'  => false,
				'required'	=> true,
				"query_builder" => function($repo) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities);
						else return $repo->findAllClosure();
					},
				'placeholder'   => 'form.select',
				'attr'		=> array(
					'class'			=> 'select2',
					),
				))
			// ->add('statut', 'entity', array(
			// 	"label"     => 'name',
			// 	'translation_domain' => 'statut',
			// 	'class'     => 'LaboAdminBundle:statut',
			// 	'property'  => 'nom',
			// 	'multiple'  => false,
			// 	"query_builder" => function($repo) {
			// 		if(method_exists($repo, 'defaultValsListClosure'))
			// 			return $repo->defaultValsListClosure($this->aeEntities);
			// 			else return $repo->findAllClosure();
			// 		},
			// 	))
			->add('marque', 'entity', array(
				"label"     => 'name',
				'translation_domain' => 'marque',
				'class'     => 'siteadminsiteBundle:marque',
				'property'  => 'nom',
				'multiple'  => false,
				'required' => false,
				"query_builder" => function($repo) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities);
						else return $repo->findAllClosure();
					},
				'placeholder'   => 'form.select',
				'attr'		=> array(
					'class'			=> 'select2',
					),
				))
			->add('group_articles_reseausChilds', 'entity', array(
				'by_reference' => false,
				"label"		=> 'fields.reseaus',
				'translation_domain' => 'article',
				'property'	=> 'nom',
				'class'		=> 'LaboAdminBundle:nested',
				'multiple'	=> true,
				'expanded'	=> false,
				"required"	=> $nestedAttributesParameters['articles_reseaus']['required'],
				'placeholder'   => 'form.select',
				'attr'		=> array(
					'class'			=> 'select2',
					'data-limit'	=> $nestedAttributesParameters['articles_reseaus']['data-limit'],
					),
				// 'group_by' => 'class_name',
				"query_builder" => function($repo) use ($data, $nestedAttributesParameters) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities, $nestedAttributesParameters['articles_reseaus']['class']);
						else return $repo->findAllClosure();
					},
				))
			// 1 image :
			->add('image', new cropperType($this->controller, $this->imagesData), array(
				'label' => 'fields.image',
				'translation_domain' => 'article',
				'required' => false,
				))
			// autres images :
			->add('group_imagesChilds', 'entity', array(
				'by_reference' => false,
				"label"		=> 'fields.group_imagesChilds',
				'translation_domain' => 'article',
				'property'	=> 'nom',
				'class'		=> 'LaboAdminBundle:nested',
				'multiple'	=> true,
				'expanded'	=> false,
				"required"	=> $nestedAttributesParameters['images']['required'],
				'placeholder'   => 'form.select',
				'attr'		=> array(
					'class'			=> 'select2',
					'data-limit'	=> $nestedAttributesParameters['images']['data-limit'],
					),
				'group_by' => 'class_name',
				"query_builder" => function($repo) use ($data, $nestedAttributesParameters) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities, $nestedAttributesParameters['images']['class']);
						else return $repo->findAllClosure();
					},
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
			// ->add('fiches', 'entity', array(
			// 	"label"		=> 'name_s',
			// 	'translation_domain' => 'fiche',
			// 	'property'	=> 'nom',
			// 	'class'		=> 'siteadminsiteBundle:fiche',
			// 	'multiple'	=> true,
			// 	'expanded'	=> false,
			// 	"required"	=> false,
			// 	'placeholder'   => 'form.select',
			// 	'attr'		=> array(
			// 		'class'			=> 'select2',
			// 		),
			// 	"query_builder" => function($repo) {
			// 		if(method_exists($repo, 'defaultValsListClosure'))
			// 			return $repo->defaultValsListClosure($this->aeEntities);
			// 			else return $repo->findAllClosure();
			// 		},
			// 	))
			->add('tags', 'entity', array(
				'label'		=> 'name_s',
				'translation_domain' => 'tag',
				'property'	=> 'nom',
				'class'		=> 'LaboAdminBundle:tag',
				'multiple'	=> true,
				'required'	=> false,
				'placeholder'   => 'form.select',
				'attr'		=> array(
					'class'			=> 'select2',
					'data-limit'	=> 8,
					),
				"query_builder" => function($repo) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities);
						else return $repo->findAllClosure();
					},
				))
			->add('group_article_ficherecetteChilds', 'entity', array(
				'by_reference' => false,
				"label"		=> 'fields.group_article_ficherecetteChilds',
				'translation_domain' => 'article',
				'property'	=> 'nom',
				'class'		=> 'LaboAdminBundle:nested',
				'multiple'	=> true,
				'expanded'	=> false,
				"required"	=> $nestedAttributesParameters['article_ficherecette']['required'],
				'placeholder'   => 'form.select',
				'attr'		=> array(
					'class'			=> 'select2',
					'data-limit'	=> $nestedAttributesParameters['article_ficherecette']['data-limit'],
					),
				'group_by' => 'class_name',
				"query_builder" => function($repo) use ($data, $nestedAttributesParameters) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities, $nestedAttributesParameters['article_ficherecette']['class']);
						else return $repo->findAllClosure();
					},
				))
			->add('group_article_ficheboissonChilds', 'entity', array(
				'by_reference' => false,
				"label"		=> 'fields.group_article_ficheboissonChilds',
				'translation_domain' => 'article',
				'property'	=> 'nom',
				'class'		=> 'LaboAdminBundle:nested',
				'multiple'	=> true,
				'expanded'	=> false,
				"required"	=> $nestedAttributesParameters['article_ficheboisson']['required'],
				'placeholder'   => 'form.select',
				'attr'		=> array(
					'class'			=> 'select2',
					'data-limit'	=> $nestedAttributesParameters['article_ficheboisson']['data-limit'],
					),
				'group_by' => 'class_name',
				"query_builder" => function($repo) use ($data, $nestedAttributesParameters) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities, $nestedAttributesParameters['article_ficheboisson']['class']);
						else return $repo->findAllClosure();
					},
				))
			->add('group_articlesChilds', 'entity', array(
				'by_reference' => false,
				"label"		=> 'fields.group_articlesChilds',
				'translation_domain' => 'article',
				'property'	=> 'nom',
				'class'		=> 'LaboAdminBundle:nested',
				'multiple'	=> true,
				'expanded'	=> false,
				"required"	=> $nestedAttributesParameters['articles']['required'],
				'placeholder'   => 'form.select',
				'attr'		=> array(
					'class'			=> 'select2',
					'data-limit'	=> $nestedAttributesParameters['articles']['data-limit'],
					),
				'group_by' => 'class_name',
				"query_builder" => function($repo) use ($data, $nestedAttributesParameters) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->aeEntities, $nestedAttributesParameters['articles']['class']);
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
			'data_class' => 'site\adminsiteBundle\Entity\article'
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminsitebundle_article';
	}
}
