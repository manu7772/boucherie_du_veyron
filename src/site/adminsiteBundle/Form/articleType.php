<?php

namespace site\adminsiteBundle\Form;

use Labo\Bundle\AdminBundle\Form\baseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// ChoiceList
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
// use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
// Transformer
// use Symfony\Component\Form\CallbackTransformer;
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
				'required' => true,
				))
			->add('accroche', 'text', array(
				'label' => 'fields.accroche',
				'required' => false,
				))
			->add('descriptif', 'insRichtext', array(
				'label' => 'fields.descriptif',
				'required' => false,
				'attr' => array(
					'data-height' => 140,
					)
				))
			// ->add('icon', 'choice', array(
			// 	"required"  => false,
			// 	"label"     => 'fields.icon',
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
			// 	'required' => false,
			// 	))
			->add('vendable', 'checkbox', array(
				'label'		=> 'fields.vendable',
				"required"  => false,
				))
			->add('surdevis', 'checkbox', array(
				'label'		=> 'fields.surdevis',
				"required"  => false,
				))
			->add('prix', 'money', array(
				'label'		=> 'fields.prixTTC',
				"required"  => false,
				))
			->add('prixHT', 'money', array(
				'label'		=> 'fields.prixHT',
				"required"  => false,
				))
			->add('tauxTva', 'entity', array(
				"label"     => 'name',
				'translation_domain' => 'tauxTva',
				'class'     => 'siteadminsiteBundle:tauxTva',
				'choice_label'  => 'nomlong',
				'multiple'  => false,
				'required'	=> true,
				"query_builder" => function($repo) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->controller);
						else return $repo->findAllClosure();
					},
				'placeholder'   => 'form.select',
				'attr'		=> array(
					'class'			=> 'select2',
					),
				))
			->add('unitprix', 'choice', array(
				"label"     => 'fields.unitprix',
				// 'translation_domain' => 'units',
				'multiple'  => false,
				"required"  => false,
				"choice_list"   => $data->getChoiceListOfUnits(),
				'placeholder'   => 'form.select',
				'attr'		=> array(
					'class'			=> 'select2',
					),
				))
			->add('unit', 'choice', array(
				"label"     => 'fields.unit',
				// 'translation_domain' => 'units',
				'multiple'  => false,
				"required"  => false,
				"choice_list"   => $data->getChoiceListOfUnits(),
				'placeholder'   => 'form.select',
				'attr'		=> array(
					'class'			=> 'select2',
					),
				))
			->add('defaultquantity', 'number', array(
				'label' => 'fields.defaultquantity',
				'required' => true,
				))
			->add('maxquantity', 'number', array(
				'label' => 'fields.maxquantity',
				'required' => false,
				))
			->add('minquantity', 'number', array(
				'label' => 'fields.minquantity',
				'required' => true,
				))
			->add('increment', 'number', array(
				'label' => 'fields.increment',
				'required' => true,
				))
			// ->add('statut', 'entity', array(
			// 	"label"     => 'name',
			// 	'translation_domain' => 'statut',
			// 	'class'     => 'LaboAdminBundle:statut',
			// 	'choice_label'  => 'nom',
			// 	'multiple'  => false,
			// 	"query_builder" => function($repo) {
			// 		if(method_exists($repo, 'defaultValsListClosure'))
			// 			return $repo->defaultValsListClosure($this->controller);
			// 			else return $repo->findAllClosure();
			// 		},
			// 	))
			// ->add('marque', 'entity', array(
			// 	"label"     => 'name',
			// 	'translation_domain' => 'marque',
			// 	'class'     => 'siteadminsiteBundle:marque',
			// 	'choice_label'  => 'nom',
			// 	'multiple'  => false,
			// 	'required' => false,
			// 	"query_builder" => function($repo) {
			// 		if(method_exists($repo, 'defaultValsListClosure'))
			// 			return $repo->defaultValsListClosure($this->controller);
			// 			else return $repo->findAllClosure();
			// 		},
			// 	'placeholder'   => 'form.select',
			// 	'attr'		=> array(
			// 		'class'			=> 'select2',
			// 		),
			// 	))
			// ->add('group_articles_reseausChilds', 'entity', array(
			// 	'by_reference' => false,
			// 	"label"		=> 'fields.reseaus',
			// 	'choice_label'	=> 'nom',
			// 	'class'		=> 'LaboAdminBundle:nested',
			// 	'multiple'	=> true,
			// 	'expanded'	=> false,
			// 	"required"	=> $nestedAttributesParameters['articles_reseaus']['required'],
			// 	'placeholder'   => 'form.select',
			// 	'attr'		=> array(
			// 		'class'			=> 'select2',
			// 		'data-limit'	=> $nestedAttributesParameters['articles_reseaus']['data-limit'],
			// 		),
			// 	// 'group_by' => 'class_name',
			// 	"query_builder" => function($repo) use ($data, $nestedAttributesParameters) {
			// 		if(method_exists($repo, 'defaultValsListClosure'))
			// 			return $repo->defaultValsListClosure($this->controller, $nestedAttributesParameters['articles_reseaus']['class']);
			// 			else return $repo->findAllClosure();
			// 		},
			// 	))
			// 1 image :
			->add('image', new cropperType($this->controller, $this->imagesData), array(
				'label' => 'fields.image',
				'required' => false,
				))
			// autres images :
			->add('group_imagesChilds', 'entity', array(
				'by_reference' => false,
				"label"		=> 'fields.group_imagesChilds',
				'choice_label'	=> 'nom',
				'class'		=> 'LaboAdminBundle:nested',
				'multiple'	=> function() use ($nestedAttributesParameters) { return $nestedAttributesParameters['nesteds']['data-limit'] > 1; },
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
						return $repo->defaultValsListClosure($this->controller, $nestedAttributesParameters['images']['class']);
						else return $repo->findAllClosure();
					},
				))
			// Images collection :
			// ->add('images', 'multiCollection', array(
			// 	'label' => 'table.col.visuel',
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
			// 	'choice_label'	=> 'nom',
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
			// 			return $repo->defaultValsListClosure($this->controller);
			// 			else return $repo->findAllClosure();
			// 		},
			// 	))
			->add('tags', 'entity', array(
				'label'		=> 'name_s',
				'translation_domain' => 'tag',
				'choice_label'	=> 'nom',
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
						return $repo->defaultValsListClosure($this->controller);
						else return $repo->findAllClosure();
					},
				))
			->add('group_article_ficherecetteChilds', 'entity', array(
				'by_reference' => false,
				"label"		=> 'fields.group_article_ficherecetteChilds',
				'choice_label'	=> 'nom',
				'class'		=> 'LaboAdminBundle:nested',
				'multiple'	=> function() use ($nestedAttributesParameters) { return $nestedAttributesParameters['nesteds']['data-limit'] > 1; },
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
						return $repo->defaultValsListClosure($this->controller, $nestedAttributesParameters['article_ficherecette']['class']);
						else return $repo->findAllClosure();
					},
				))
			->add('group_article_ficheboissonChilds', 'entity', array(
				'by_reference' => false,
				"label"		=> 'fields.group_article_ficheboissonChilds',
				'choice_label'	=> 'nom',
				'class'		=> 'LaboAdminBundle:nested',
				'multiple'	=> function() use ($nestedAttributesParameters) { return $nestedAttributesParameters['nesteds']['data-limit'] > 1; },
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
						return $repo->defaultValsListClosure($this->controller, $nestedAttributesParameters['article_ficheboisson']['class']);
						else return $repo->findAllClosure();
					},
				))
			->add('group_articlesChilds', 'entity', array(
				'by_reference' => false,
				"label"		=> 'fields.group_articlesChilds',
				'choice_label'	=> 'nom',
				'class'		=> 'LaboAdminBundle:nested',
				'multiple'	=> function() use ($nestedAttributesParameters) { return $nestedAttributesParameters['nesteds']['data-limit'] > 1; },
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
						return $repo->defaultValsListClosure($this->controller, $nestedAttributesParameters['articles']['class']);
						else return $repo->findAllClosure();
					},
				))
		;

		// $choices = $this->_em->getRepository('site\adminsiteBundle\Entity\categorie')->defaultValsListClosure($this->controller, $nestedAttributesParameters['nesteds']['class']);
		// $choices = $choices->select('element.id')->addSelect('element.nom')->addSelect('element.type')->getQuery()->getArrayResult();
		// // echo('<pre><h3>Nested choices :</h3>');var_dump($choices);die('</pre>');
		// $index = array();
		// $label = array();
		// foreach ($choices as $item) {
		// 	if(!isset($index[$item['type']])) $index[$item['type']] = array();
		// 	if(!isset($label[$item['type']])) $label[$item['type']] = array();
		// 	$index[$item['type']][] = $item['id'];
		// 	$label[$item['type']][] = $item['nom'];
		// }

		$builder->add('group_nestedsParents', 'entity', array(
                'by_reference' => false,
                "label"     => 'fields.group_nestedsParents',
                'translation_domain' => 'article',
                'choice_label'  => 'nom',
                'class'     => 'LaboAdminBundle:nested',
                // 'class'     => 'siteadminsiteBundle:categorie',
                'multiple'  => function() use ($nestedAttributesParameters) { return $nestedAttributesParameters['nesteds']['data-limit'] > 1; },
                'expanded'  => false,
                "required"  => $nestedAttributesParameters['nesteds']['required'],
                'placeholder'   => 'form.select',
                'attr'      => array(
                    'class'         => 'select2',
                    'data-limit'    => $nestedAttributesParameters['nesteds']['data-limit'],
                    ),
                'group_by' => 'class_name',
                "query_builder" => function($repo) use ($nestedAttributesParameters) {
                    if(method_exists($repo, 'defaultValsListClosure'))
                        return $repo->defaultValsListClosure($this->controller, $nestedAttributesParameters['nesteds']['class']);
                        else return $repo->findAllClosure($aeEntities);
                    },
                // "choice_list"   => new ChoiceList($label, $index),
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
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'site\adminsiteBundle\Entity\article',
			'translation_domain' => 'article',
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminsitebundle_article';
	}
}
