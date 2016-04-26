<?php

namespace site\adminBundle\Form;

use site\adminBundle\Form\baseType;
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
		$changeNom = false;
		// if($categorie != null) if($categorie->getId() != null) {
		//     if($categorie->getParent() == null) $changeNom = true;
		// }
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
			->add('image', new cropperType($this->controller, $this->imagesData), array(
				'label' => 'fields.image',
				'translation_domain' => 'categorie',
				'required' => false,
				))
			// ->add('subEntitys', 'entity', array(
			//     "label"     => 'fields.subEntitys',
			//     'translation_domain' => 'categorie',
			//     'class'     => 'siteadminBundle:baseSubEntity',
			//     'property'  => 'nom',
			//     'multiple'  => true,
			//     'required' => false,
			//     'group_by' => 'class_name',
			//     "query_builder" => function($repo) use ($categorie) {
			//         if(method_exists($repo, 'getElementsBySubType'))
			//             return $repo->getElementsBySubType($categorie);
			//             else return $repo->findAllClosure();
			//         },
			//     'attr'      => array(
			//         'class'         => 'select2',
			//         ),
			//     ))
		;
		if($categorie != null) {
			// type
			if($categorie->getParent() == null) {
				$builder
					->add('type', 'choice', array(
						"required"  => true,
						"label"     => 'fields.type',
						'translation_domain' => 'categorie',
						'multiple'  => false,
						"choices"   => $categorie->getTypeList(),
						'placeholder'   => 'form.select',
						'attr'      => array(
							'class'         => 'select2',
							),
						))
				;
			}
			// catégories
			if($categorie->getLvl() > 0) {
				$builder
					->add('icon', 'choice', array(
						"required"  => false,
						"label"     => 'fields.icon',
						'translation_domain' => 'categorie',
						'multiple'  => false,
						"choices"   => $categorie->getListIcons(),
						'placeholder'   => 'form.select',
						'attr'      => array(
							'class'         => 'select2',
							'data-format'	=> 'formatState',
							),
						))
					->add('subEntitys', 'entity', array(
						"label"     => 'fields.subEntitys',
						'translation_domain' => 'categorie',
						'class'     => 'siteadminBundle:baseSubEntity',
						'property'  => 'nom',
						'multiple'  => true,
						'required' => false,
						'group_by' => 'class_name',
						"query_builder" => function($repo) use ($categorie) {
								return $repo->getElementsBySubTypes($categorie);
							},
							'placeholder'   => 'form.select',
						'attr'      => array(
							'class'         => 'select2',
							),
						))
					->add('parent', 'entity', array(
						"label"     => 'fields.parent',
						'translation_domain' => 'categorie',
						'group_by'  => 'parent.nom',
						'class'     => 'siteadminBundle:categorie',
						'property'  => 'nom',
						'required'  => true,
						'multiple'  => false,
						'placeholder'   => 'form.select',
						'attr'      => array(
							'class'         => 'select2',
							),
						"query_builder" => function($repo) use ($categorie) {
								return $repo->getElementsButCategories($categorie);
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
			'data_class' => 'site\adminBundle\Entity\categorie'
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminbundle_categorie';
	}
}
