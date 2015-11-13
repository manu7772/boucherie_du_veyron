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

class pagewebType extends AbstractType {

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
		$builder
			->add('nom', 'text', array(
				'label' => 'form.nom',
				'required' => true,
				))
			->add('code', 'insRichtext', array(
				'label' => 'form.code',
				'required' => false,
				))
			->add('title', 'text', array(
				'label' => 'table.col.title',
				'required' => true,
				))
			->add('titreh1', 'text', array(
				'label' => 'table.col.titreh1',
				'required' => true,
				))
			->add('metatitle', 'text', array(
				'label' => 'table.col.metatitle',
				'required' => false,
				))
			->add('metadescription', 'text', array(
				'label' => 'table.col.metadescription',
				'required' => false,
				))
			->add('modele', 'text', array(
				'label' => 'table.col.modele',
				'required' => true,
				))
			->add('background', new mediaType($this->controller), array(
				'label' => 'form.background',
				'required' => false,
				))
			->add('tags', 'entity', array(
				'label' => 'tag.name_s',
				'property' => 'nom',
				'class' => 'site\adminBundle\Entity\tag',
				'multiple' => true,
				'required' => false,
				'attr' => array(
					'class' => 'chosen-select chosen-select-width chosen-select-no-results',
					'placeholder' => 'form.select',
					),
				))
		;
        // ajoute les valeurs hidden, passés en paramètre
        $builder = $this->addHiddenValues($builder);

        // AJOUT SUBMIT
        $builder->add('submit', 'submit', array(
            'label' => 'form.enregistrer',
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
			'data_class' => 'site\adminBundle\Entity\pageweb'
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'site_adminbundle_pageweb';
	}
}
