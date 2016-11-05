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

use site\adminsiteBundle\Form\imageType;

class pagewebType extends baseType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		$this->initBuilder($builder);
		$this->pageweb = $this->controller->get('aetools.aeServicePageweb');
		$this->imagesData = array(
			'image' => array(
				'owner' => 'pageweb:image'
				),
			);
		// Builder…
		$builder
			->add('nom', 'text', array(
				'label' => 'fields.nom',
				'translation_domain' => 'pageweb',
				'required' => true,
				))
			->add('code', 'insRichtext', array(
				'label' => 'fields.code',
				'translation_domain' => 'pageweb',
				'required' => false,
				'attr' => array(
					'data-height' => 300,
					)
				))
			->add('title', 'text', array(
				'label' => 'fields.title',
				'translation_domain' => 'pageweb',
				'required' => true,
				))
			->add('titreh1', 'text', array(
				'label' => 'fields.titreh1',
				'translation_domain' => 'pageweb',
				'required' => true,
				))
			// ->add('keywords', 'text', array(
			// 	'label' => 'fields.keywords',
			//	'translation_domain' => 'pageweb',
			// 	'required' => false,
			// 	))
			->add('metadescription', 'text', array(
				'label' => 'fields.metadescription',
				'translation_domain' => 'pageweb',
				'required' => false,
				))
			->add('modele', 'choice', array(
				'label' => 'fields.modele',
				'translation_domain' => 'pageweb',
				'required' => true,
				'choice_list' => $this->pageweb->getPagewebChoices($builder->getData()->getExtended()),
				))
			->add('extended', 'checkbox', array(
				'label'		=> 'fields.extended',
				'translation_domain' => 'pageweb',
				"required"  => false,
				))
			// 1 image :
			->add('image', new cropperType($this->controller, $this->imagesData), array(
				'label' => 'fields.image',
				'translation_domain' => 'pageweb',
				'required' => false,
				))
            ->add('diaporama', 'entity', array(
                "label"     => 'fields.diaporama',
                'translation_domain' => 'pageweb',
                'class'     => 'siteadminsiteBundle:categorie',
                'choice_label'  => 'nom',
                'multiple'  => false,
                'required' => false,
                'group_by' => 'categorieParent.nom',
                "query_builder" => function($repo) {
                    if(method_exists($repo, 'getDiaporamas'))
                        return $repo->getDiaporamas();
                        else return $repo->findAllClosure();
                    },
                'placeholder'   => 'form.select',
                'attr'      => array(
                    'class'         => 'select2',
                    ),
                ))
			->add('tags', 'entity', array(
				'label' => 'fields.tags',
				'translation_domain' => 'pageweb',
				'choice_label' => 'nom',
				'class' => 'LaboAdminBundle:tag',
				'multiple' => true,
				'required' => false,
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
			// ->add('parents', 'entity', array(
			// 	"label"     => 'name_s',
			// 	'translation_domain' => 'categorie',
			// 	'class'     => 'LaboAdminBundle:categorie',
			// 	'choice_label'  => 'nom',
			// 	'multiple'  => true,
			// 	'required' => false,
			// 	'group_by' => 'categorieParent.nom',
			// 	"query_builder" => function($repo) {
			// 		return $repo->getElementsBySubTypeButRoot(array('pageweb'));
			//     	},
			// 	'placeholder'   => 'form.select',
			// 	'attr'		=> array(
			// 		'class'			=> 'select2',
			// 		),
			// 	))
		;
		// ajoute les valeurs hidden, passés en paramètre
		$this->addHiddenValues($builder, true);
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'site\adminsiteBundle\Entity\pageweb'
		));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'site_adminsitebundle_pageweb';
	}
}
