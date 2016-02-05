<?php

namespace site\adminBundle\Form;

use Symfony\Component\Form\AbstractType;
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

use site\adminBundle\Form\mediaType;
use site\adminBundle\Entity\fiche;

class ficheType extends AbstractType {

	private $controller;
	private $securityContext;
	private $parametres;
	
	public function __construct(Controller $controller, $parametres = null) {
		$this->controller = $controller;
		$this->securityContext = $controller->get('security.context');
		if($parametres === null) $parametres = array();
		$this->parametres = $parametres;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		if(isset($this->parametres['form_action'])) $builder->setAction($this->parametres['form_action']);
		// Builder…
		$fiche = new fiche();
		$builder
			->add('nom', 'text', array(
				'label' => 'form.nom',
				'translation_domain' => 'messages',
				'required' => true,
				))
			->add('accroche', 'text', array(
				'label' => 'form.accroche',
				'translation_domain' => 'messages',
				'required' => false,
				))
			->add('descriptif', 'insRichtext', array(
				'label' => 'form.descriptif',
				'translation_domain' => 'messages',
				'required' => false,
				))
			->add('niveau', 'choice', array(
				"required"  => true,
				"label"     => 'Niveau',
				'multiple'  => false,
				'expanded'  => true,
				"choices"   => $fiche->getListeNiveaux(),
				))
			->add('duree', 'choice', array(
				"required"  => true,
				"label"     => 'Temps de réalisation',
				'multiple'  => false,
				'expanded'  => false,
				"choices"   => $fiche->getDurees(),
				))
			// ->add('dateCreation')
			// ->add('dateMaj')
			// ->add('slug')
			->add('statut', 'entity', array(
				'class'     => 'siteadminBundle:statut',
				'property'  => 'nom',
				'multiple'  => false,
				"label"     => 'Statut'
				))
			->add('image', new mediaType($this->controller), array(
				'label' => 'table.col.visuel',
				'translation_domain' => 'messages',
				'required' => false,
				))
			->add('tags', 'entity', array(
				'label'		=> 'tag.name_s',
				'translation_domain' => 'messages',
				'property'	=> 'nom',
				'class'		=> 'siteadminBundle:tag',
				'multiple'	=> true,
				'required'	=> false,
				'attr'		=> array(
					'class'			=> 'chosen-select chosen-select-width chosen-select-no-results',
					'placeholder'	=> 'form.select',
					),
				))
			->add('articles', 'entity', array(
				'label'		=> 'article.name_s',
				'translation_domain' => 'messages',
				'property'	=> 'nom',
				'class'		=> 'siteadminBundle:article',
				'multiple'	=> true,
				'required'	=> false,
				'attr'		=> array(
					'class'			=> 'chosen-select chosen-select-width chosen-select-no-results',
					'placeholder'	=> 'form.select',
					),
				))
			->add('datePublication', 'insDatepicker', array(
				"required"  => false,
				))
			->add('dateExpiration', 'insDatepicker', array(
				"required"  => false,
				))
		;
		// ajoute les valeurs hidden, passés en paramètre
		$builder = $this->addHiddenValues($builder);

		// AJOUT SUBMIT
		$builder->add('submit', 'submit', array(
			'label' => 'form.enregistrer',
			'translation_domain' => 'messages',
			'attr' => array(
				'class' => "btn btn-md btn-block btn-info",
				),
			))
		;
	}
	
	/**
	 * addHiddenValues
	 * @param FormBuilderInterface $builder
	 * @return FormBuilderInterface
	 */
	public function addHiddenValues(FormBuilderInterface $builder) {
		$data = array();
		$nom = 'hiddenData';
		foreach($this->parametres as $key => $value) {
			if(is_string($value) || is_array($value) || is_bool($value)) {
				$data[$key] = $value;
			}
		}
		if($builder->has($nom)) $builder->remove($nom);
		$builder->add($nom, 'hidden', array(
			'data' => urlencode(json_encode($data, true)),
			'mapped' => false,
		));
		// }
		return $builder;
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'site\adminBundle\Entity\fiche'
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'site_adminbundle_fiche';
	}
}
