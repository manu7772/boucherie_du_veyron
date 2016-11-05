<?php

namespace site\adminsiteBundle\Form;

use Labo\Bundle\AdminBundle\Form\baseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Transformer
use Symfony\Component\Form\CallbackTransformer;
// User
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage as SecurityContext;
// Paramétrage de formulaire
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class categorieRootType extends baseType {

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
		$aeEntities = $this->aeEntities;
		// Builder…
		$builder
			->add('nom', 'text', array(
				'label' => 'fields.nom',
				'translation_domain' => 'categorie',
				'required' => true,
				))
			->add('descriptif', 'insRichtext', array(
				'label' => 'fields.descriptif',
				'translation_domain' => 'categorie',
				'required' => false,
				'attr' => array(
					'data-height' => 140,
					)
				))
            ->add('icon', 'choice', array(
             "required"  => false,
             "label"     => 'fields.icon',
             'translation_domain' => 'categorie',
             'multiple'  => false,
             "choices"   => $categorie->getListIcons(),
             'placeholder'   => 'form.select',
             'attr'      => array(
                 'class'         => 'select2',
                 'data-format'   => 'formatState',
                 ),
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

		// ajoute les valeurs hidden, passés en paramètre
		$this->addHiddenValues($builder, true);
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'site\adminsiteBundle\Entity\categorie'
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminsitebundle_categorieRoot';
	}
}
