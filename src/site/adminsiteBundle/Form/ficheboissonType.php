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

use site\adminsiteBundle\Form\cropperType;
use site\adminsiteBundle\Form\imageType;
use site\adminsiteBundle\Entity\ficheboisson;

class ficheboissonType extends baseType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		$this->initBuilder($builder);
		$data = $builder->getData();
		$nestedAttributesParameters = $data->getNestedAttributesParameters();
		// $this->imagesData = array(
		// 	'image' => array(
		// 		'owner' => 'ficheboisson:image'
		// 		),
		// 	)
		// ;
		// Builder…
		$ficheboisson = new ficheboisson();
		$builder
			->add('nom', 'text', array(
				'label' => 'fields.nom',
				'translation_domain' => 'ficheboisson',
				'required' => true,
				))
			->add('accroche', 'text', array(
				'label' => 'fields.accroche',
				'translation_domain' => 'ficheboisson',
				'required' => false,
				))
			->add('descriptif', 'insRichtext', array(
				'label' => 'fields.descriptif',
				'translation_domain' => 'ficheboisson',
				'required' => false,
				'attr' => array(
					'data-height' => 400,
					)
				))
			;
		if(count($data->getListeTypentites()) > 0) {
			$builder
				->add('typentite', 'choice', array(
					"required"  => true,
					"label"     => 'fields.typentite',
					'translation_domain' => 'ficheboisson',
					'multiple'  => false,
					// 'expanded'  => true,
					"choices"   => $ficheboisson->getListeTypentites(),
					))
				;
			}
		$builder
			->add('note', 'choice', array(
				"required"  => true,
				"label"     => 'fields.note',
				'translation_domain' => 'ficheboisson',
				'multiple'  => false,
				// 'expanded'  => true,
				"choices"   => $ficheboisson->getListenotes(),
				))
			// ->add('dateCreation')
			// ->add('dateMaj')
			// ->add('slug')
			// ->add('statut', 'entity', array(
			// 	'class'     => 'siteadminsiteBundle:statut',
			// 	'choice_label'  => 'nom',
			// 	'multiple'  => false,
			// 	"label"     => 'name',
			// 	'translation_domain' => 'statut',
			// 	"query_builder" => function($repo) {
			// 		if(method_exists($repo, 'defaultValsListClosure'))
			// 			return $repo->defaultValsListClosure($this->controller);
			// 			else return $repo->findAllClosure();
			// 		},
			// 	))
			->add('image', new cropperType($this->controller, array('image' => array('owner' => 'ficheboisson:image'))), array(
				'label' => 'fields.image',
				'translation_domain' => 'ficheboisson',
				'required' => false,
				))
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
					),
				))
			->add('group_article_ficheboissonParents', 'entity', array(
				'by_reference' => false,
				"label"		=> 'fields.group_article_ficheboissonParents',
				'translation_domain' => 'ficheboisson',
				'choice_label'	=> 'nom',
				'class'		=> 'LaboAdminBundle:nested',
				'multiple'	=> true,
				'expanded'	=> false,
				"required"	=> $nestedAttributesParameters['article_ficheboisson']['required'],
				'placeholder'   => 'form.select',
				'attr'		=> array(
					'class'			=> 'select2',
					'data-limit'	=> $nestedAttributesParameters['article_ficheboisson']['data-limit'],
					),
				'group_by' => 'shortName',
				"query_builder" => function($repo) use ($data, $nestedAttributesParameters) {
					if(method_exists($repo, 'defaultValsListClosure'))
						return $repo->defaultValsListClosure($this->controller, $nestedAttributesParameters['article_ficheboisson']['class']);
						else return $repo->findAllClosure();
					},
				))
			->add('datePublication', 'insDatepicker', array(
				'label'		=> 'fields.datePublication',
				'translation_domain' => 'ficheboisson',
				"required"  => false,
				))
			->add('dateExpiration', 'insDatepicker', array(
				'label'		=> 'fields.dateExpiration',
				'translation_domain' => 'ficheboisson',
				"required"  => false,
				))
		;
		// ajoute les valeurs hidden, passés en paramètre
		$this->addHiddenValues($builder, true);
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'site\adminsiteBundle\Entity\ficheboisson'
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'site_adminsitebundle_ficheboisson';
	}
}
