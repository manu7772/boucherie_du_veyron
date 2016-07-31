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

class categorieType extends baseType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		$this->initBuilder($builder);
		$this->imagesData = array(
			'image' => array(
				'owner' => 'categorie:image'
				),
			);
		$categorie = $builder->getData();
		$nestedAttributesParameters = $categorie->getNestedAttributesParameters();
		$changeNom = false;
		// if($categorie != null) if($categorie->getId() != null) {
		//     if($categorie->getParent() == null) $changeNom = true;
		// }
		$aeEntities = $this->aeEntities;
		// Builder…
		$builder
			->add('nom', 'text', array(
				'label' => 'fields.nom',
				'translation_domain' => 'categorie',
				'required' => true,
				'disabled' => $changeNom,
				))
			->add('descriptif', 'insRichtext', array(
				'label' => 'fields.descriptif',
				'translation_domain' => 'categorie',
				'required' => false,
				'attr' => array(
					'data-height' => 140,
					)
				))
			->add('couleur', 'insColorpicker', array(
				'label'     => 'fields.couleur',
				'translation_domain' => 'categorie',
				'required'  => false,
				))
			// 1 image :
			// ->add('image', new cropperType($this->controller, $this->imagesData), array(
			// 	'label' => 'fields.image',
			// 	'translation_domain' => 'categorie',
			// 	'required' => false,
			// 	))
		;
		if($categorie != null) {
			// type / ROOTS
			if($categorie->getLvl() == 0) {
			// if(count($categorie->getGroup_categorie_parentParents()) == 0) {
				$builder
					->add('type', 'choice', array(
						"required"  => true,
						"label"     => 'fields.type',
						'translation_domain' => 'categorie',
						'multiple'  => false,
						"choices"   => $categorie->getTypeList(),
						'placeholder'   => 'form.select',
						'attr'      => array(
							'class'		=> 'select2',
							),
						))
				;
			}
			// catégories
			else {
				$isNew = $categorie->getId() == null ;
				$builder
				->add('group_pagewebsChilds', 'entity', array(
					'by_reference' => false,
					"label"		=> 'fields.group_pagewebsChilds',
					'translation_domain' => 'categorie',
					'property'	=> 'nom',
					'class'		=> 'LaboAdminBundle:nested',
					'multiple'	=> function() use ($nestedAttributesParameters) { return $nestedAttributesParameters['pagewebs']['data-limit'] > 1; },
					'expanded'	=> false,
					"required"	=> $nestedAttributesParameters['pagewebs']['required'],
					'placeholder'   => 'form.select',
					'attr'		=> array(
						'class'			=> 'select2',
						'data-limit'	=> $nestedAttributesParameters['pagewebs']['data-limit'],
						),
					'group_by' => 'class_name',
					"query_builder" => function($repo) use ($categorie, $nestedAttributesParameters, $aeEntities) {
						if(method_exists($repo, 'defaultValsListClosure'))
							return $repo->defaultValsListClosure($aeEntities, $nestedAttributesParameters['pagewebs']['class'], $categorie);
							else return $repo->findAllClosure($aeEntities);
						},
					))
					// ->add('icon', 'choice', array(
					// 	"required"  => false,
					// 	"label"     => 'fields.icon',
					// 	'translation_domain' => 'categorie',
					// 	'multiple'  => false,
					// 	"choices"   => $categorie->getListIcons(),
					// 	'placeholder'   => 'form.select',
					// 	'attr'      => array(
					// 		'class'         => 'select2',
					// 		'data-format'	=> 'formatState',
					// 		),
					// 	))
					->add('group_categorie_parentParents', 'entity', array(
						// 'disabled'	=> $isNew,
						'by_reference' => false,
						"label"		=> 'fields.group_categorie_parentParents',
						'translation_domain' => 'categorie',
						'property'	=> 'nom',
						'class'		=> 'LaboAdminBundle:nested',
						'multiple'	=> function() use ($nestedAttributesParameters) { return $nestedAttributesParameters['categorie_parent']['data-limit'] > 1; },
						'expanded'	=> false,
						"required"	=> $nestedAttributesParameters['categorie_parent']['required'],
						'placeholder'   => 'form.select',
						'attr'		=> array(
							'class'			=> 'select2',
							'data-limit'	=> $nestedAttributesParameters['categorie_parent']['data-limit'],
							),
						'group_by' => 'class_name',
						"query_builder" => function($repo) use ($categorie, $nestedAttributesParameters, $aeEntities) {
							if(method_exists($repo, 'defaultValsListClosure'))
								return $repo->defaultValsListClosure($aeEntities, $nestedAttributesParameters['categorie_parent']['class'], $categorie);
								else return $repo->findAllClosure($aeEntities);
							},
						))
					// ->add('type', 'choice', array(
					// 	'disabled'	=> true,
					// 	"required"  => true,
					// 	"label"     => 'fields.type',
					// 	'translation_domain' => 'categorie',
					// 	'multiple'  => false,
					// 	"choices"   => $categorie->getTypeList(),
					// 	'placeholder'   => 'form.select',
					// 	'attr'      => array(
					// 		'class'		=> 'select2',
					// 		),
					// 	))
					// ->add('lvl', null, array(
					// 	'disabled'	=> true,
					// 	))
					->add('group_nestedsChilds', 'entity', array(
						'by_reference' => false,
						"label"		=> 'fields.group_nestedsChilds',
						'translation_domain' => 'categorie',
						'property'	=> 'nom',
						'class'		=> 'LaboAdminBundle:nested',
						'multiple'	=> function() use ($nestedAttributesParameters) { return $nestedAttributesParameters['nesteds']['data-limit'] > 1; },
						'expanded'	=> false,
						"required"	=> $nestedAttributesParameters['nesteds']['required'],
						'placeholder'   => 'form.select',
						'attr'		=> array(
							'class'			=> 'select2',
							'data-limit'	=> $nestedAttributesParameters['nesteds']['data-limit'],
							),
						'group_by' => 'class_name',
						"query_builder" => function($repo) use ($categorie, $nestedAttributesParameters, $aeEntities) {
							if(method_exists($repo, 'defaultValsListClosure'))
								return $repo->defaultValsListClosure($aeEntities, $nestedAttributesParameters['nesteds']['class'], $categorie);
								else return $repo->findAllClosure($aeEntities);
							},
						))
				;
			}
		}
		// ajoute les valeurs hidden, passés en paramètre
		$this->addHiddenValues($builder, true);
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'site\adminsiteBundle\Entity\categorie'
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminsitebundle_categorie';
	}
}
