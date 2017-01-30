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
            ->add('group_pagewebsChilds', 'entity', array(
                'by_reference' => false,
                "label"     => 'fields.group_pagewebsChilds',
                'translation_domain' => 'categorie',
                'choice_label'  => 'nom',
                'class'     => 'LaboAdminBundle:nested',
                'multiple'  => function() use ($nestedAttributesParameters) { return $nestedAttributesParameters['pagewebs']['data-limit'] > 1; },
                'expanded'  => false,
                "required"  => $nestedAttributesParameters['pagewebs']['required'],
                'placeholder'   => 'form.select',
                'attr'      => array(
                    'class'         => 'select2',
                    'data-limit'    => $nestedAttributesParameters['pagewebs']['data-limit'],
                    ),
                'group_by' => 'shortName',
                "query_builder" => function($repo) use ($categorie, $nestedAttributesParameters) {
                    if(method_exists($repo, 'defaultValsListClosure'))
                        return $repo->defaultValsListClosure($this->controller, $nestedAttributesParameters['pagewebs']['class'], $categorie);
                        else return $repo->findAllClosure($this->aeEntities);
                    },
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
                // ->add('type', 'choice', array(
                //  'disabled'  => true,
                //  "required"  => true,
                //  "label"     => 'fields.type',
                //  'translation_domain' => 'categorie',
                //  'multiple'  => false,
                //  "choices"   => $categorie->getTypeList(),
                //  'placeholder'   => 'form.select',
                //  'attr'      => array(
                //      'class'     => 'select2',
                //      ),
                //  ))
                // ->add('lvl', null, array(
                //  'disabled'  => true,
                //  ))
                ->add('group_nestedsChilds', 'entity', array(
                    'by_reference' => false,
                    "label"     => 'fields.group_nestedsChilds',
                    'translation_domain' => 'categorie',
                    'choice_label'  => 'nom',
                    'class'     => 'LaboAdminBundle:nested',
                    'multiple'  => function() use ($nestedAttributesParameters) { return $nestedAttributesParameters['nesteds']['data-limit'] > 1; },
                    'expanded'  => false,
                    "required"  => $nestedAttributesParameters['nesteds']['required'],
                    'placeholder'   => 'form.select',
                    'attr'      => array(
                        'class'         => 'select2',
                        'data-limit'    => $nestedAttributesParameters['nesteds']['data-limit'],
                        ),
                    'group_by' => 'shortName',
                    "query_builder" => function($repo) use ($categorie, $nestedAttributesParameters) {
                        if(method_exists($repo, 'defaultValsListClosure'))
                            return $repo->defaultValsListClosure($this->controller, $nestedAttributesParameters['nesteds']['class'], $categorie);
                            else return $repo->findAllClosure($this->aeEntities);
                        },
                    ))
                ->add('group_categorie_nestedChilds', 'entity', array(
                    'by_reference' => false,
                    "label"     => 'fields.group_categorie_nestedChilds',
                    'translation_domain' => 'categorie',
                    'choice_label'  => 'nom',
                    'class'     => 'LaboAdminBundle:nested',
                    'multiple'  => true,
                    'expanded'  => false,
                    "required"  => $nestedAttributesParameters['categorie_nested']['required'],
                    'placeholder'   => 'form.select',
                    'attr'      => array(
                        'class'         => 'select2',
                        // 'data-limit'    => $nestedAttributesParameters['categorie_nested']['data-limit'],
                        ),
                    'group_by' => 'categorieParent.shortName',
                    "query_builder" => function($repo) use ($categorie, $nestedAttributesParameters) {
                        if(method_exists($repo, 'defaultValsListClosure'))
                            return $repo->defaultValsListClosure($this->controller, $nestedAttributesParameters['categorie_nested']['class'], $categorie);
                            else return $repo->findAllClosure($this->aeEntities);
                        },
                    ))
		;

		$user = $this->user;
		$builder->addEventListener(
			FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($user, $nestedAttributesParameters) {
				$categorie = $event->getData();
				$form = $event->getForm();

                if(is_object($categorie) && method_exists($categorie, "getId")) {
                    if($categorie->getId() === null) {
                        // L'entité n'existe pas : visible but disabled !!!
                        $form->add('categorieParent', 'entity', array(
                            'disabled'  => true,
                            "label"     => 'fields.parent',
                            'translation_domain' => 'categorie',
                            'choice_label'  => 'nom',
                            'class'     => 'LaboAdminBundle:nested',
                            'multiple'  => false,
                            'expanded'  => false,
                            "required"  => true,
                            'placeholder'   => 'form.select',
                            'attr'      => array(
                                'class'         => 'select2',
                                ),
                            "query_builder" => function($repo) use ($categorie, $nestedAttributesParameters) {
                                if(method_exists($repo, 'defaultValsListClosure'))
                                    return $repo->defaultValsListClosure($this->controller, $nestedAttributesParameters['categorie_nested']['class'], $categorie);
                                    else return $repo->findAllClosure($this->aeEntities);
                                },
                            ))
                        ;
                    } else {
                        // L'entité existe : visble and enabled
                        $form->add('categorieParent', 'entity', array(
                            'disabled'  => false,
                            "label"     => 'fields.parent',
                            'translation_domain' => 'categorie',
                            'choice_label'  => 'nom',
                            'class'     => 'LaboAdminBundle:nested',
                            'multiple'  => false,
                            'expanded'  => false,
                            "required"  => true,
                            'placeholder'   => 'form.select',
                            'attr'      => array(
                                'class'         => 'select2',
                                ),
                            "query_builder" => function($repo) use ($categorie, $nestedAttributesParameters) {
                                if(method_exists($repo, 'defaultValsListClosure'))
                                    return $repo->defaultValsListClosure($this->controller, $nestedAttributesParameters['categorie_nested']['class'], $categorie);
                                    else return $repo->findAllClosure($this->aeEntities);
                                },
                            ))
                        ;
                    }
                }
			}
		);


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
		return 'site_adminsitebundle_categorie';
	}
}
