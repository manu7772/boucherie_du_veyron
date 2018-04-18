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

class evenementType extends baseType {

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
				'owner' => 'evenement:image'
				),
			);
		// Builder…
		$builder
			->add('nom', 'text', array(
				'label' => 'fields.nom',
				'translation_domain' => 'evenement',
				'required' => true,
				))
			->add('couleur', 'insColorpicker', array(
				'label'     => 'fields.couleur',
				'translation_domain' => 'evenement',
				'required'  => false,
				))
			->add('descriptif', 'insRichtext', array(
				'label' => 'fields.descriptif',
				'translation_domain' => 'evenement',
				'required' => false,
				'attr' => array(
					'data-height' => 360,
					)
				))
			->add('debut', 'insDatepicker', array(
				'label'		=> 'fields.debut',
				'translation_domain' => 'evenement',
				"required"  => false,
				))
			->add('fin', 'insDatepicker', array(
				'label'		=> 'fields.fin',
				'translation_domain' => 'evenement',
				"required"  => false,
				))
			->add('image', new cropperType($this->controller, array('image' => $this->imagesData['image'])), array(
				'label' => 'fields.image',
				'translation_domain' => 'evenement',
				'required' => false,
				))
			->add('telfixe', 'text', array(
				'label' => 'fields.telfixe',
				'translation_domain' => 'evenement',
				'required' => false,
				))
			->add('mobile', 'text', array(
				'label' => 'fields.mobile',
				'translation_domain' => 'evenement',
				'required' => false,
				))
			->add('email', 'text', array(
				'label' => 'fields.email',
				'translation_domain' => 'evenement',
				'required' => false,
				))
			->add('adresse', new adresseType($this->controller), array(
				'label' => 'fields.adresse',
				'translation_domain' => 'evenement',
				'required' => false,
				))
			->add('group_evenementsChilds', 'entity', array(
				'by_reference' => false,
				"label"		=> 'fields.group_evenementsChilds',
				'translation_domain' => 'evenement',
				'choice_label'	=> 'nom',
				'class'		=> 'LaboAdminBundle:nested',
				'multiple'	=> function() use ($nestedAttributesParameters) { return $nestedAttributesParameters['nesteds']['data-limit'] > 1; },
				'expanded'	=> false,
				"required"	=> $nestedAttributesParameters['evenements']['required'],
				'placeholder'   => 'form.select',
				'attr'		=> array(
					'class'			=> 'select2',
					'data-limit'	=> $nestedAttributesParameters['evenements']['data-limit'],
					),
				// 'group_by' => 'shortName',
				"query_builder" => function($repo) use ($data, $nestedAttributesParameters) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->controller, $nestedAttributesParameters['evenements']['class']);
						else return $repo->findAllClosure();
					},
				))
			->add('group_nestedsParents', 'entity', array(
                'by_reference' => false,
                "label"     => 'fields.group_nestedsParents',
                'translation_domain' => 'evenement',
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
                'group_by' => 'categorieParent.nom',
                "query_builder" => function($repo) use ($nestedAttributesParameters) {
                    if(method_exists($repo, 'defaultValsListClosure'))
                        return $repo->defaultValsListClosure($this->controller, $nestedAttributesParameters['nesteds']['class']);
                        else return $repo->findAllClosure($aeEntities);
                    },
                // "choice_list"   => new ChoiceList($label, $index),
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
			'data_class' => 'site\adminsiteBundle\Entity\evenement'
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminsitebundle_evenement';
	}
}
