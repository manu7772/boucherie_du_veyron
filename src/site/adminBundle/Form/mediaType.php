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

class mediaType extends AbstractType {

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
    	// if(isset($this->parametres['form_action'])) $builder->setAction($this->parametres['form_action']);
    	// Builder…
		$builder
			->add('nom', 'text', array(
				'label'         => 'form.nom',
				'disabled'      => false,
				'required'		=> true,
				))
			->add('upload_file', 'file', array(
				'label' => 'form.telechargement',
				));
		;
        // ajoute les valeurs hidden, passés en paramètre
        // $builder = $this->addHiddenValues($builder);

		// $factory = $builder->getFormFactory();
		// $builder->addEventListener(
		// 	FormEvents::PRE_SET_DATA,
		// 	function(FormEvent $event) use ($factory) {
		// 		$data = $event->getData();
		// 		// important : GARDER CETTE CONDITION CI-DESSOUS (toujours !!!)
		// 		if(null === $data) return;
		// 		if(null === $data->getId()) {
		// 			// rien, on laisse dans le champ
		// 			$event->getForm()
		// 				->add('upload_file', 'file', array(
		// 					'label' => 'form.telechargement',
		// 					));
		// 			// $event->getForm()->add(
		// 			//     $factory->createNamed('upload_file', 'file', null, array('label' => 'Fichier à télécharger'))
		// 			// );
		// 		} else {
		// 			// $event->getForm()->remove('upload_file');
		// 		}
		// 	}
		// );

        // AJOUT SUBMIT
        // $builder->add('submit', 'submit', array(
        //     'label' => 'form.enregistrer',
        //     'attr' => array(
        //         'class' => "btn btn-md btn-block btn-info",
        //         ),
        //     ))
        // ;
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
			'data_class' => 'site\adminBundle\Entity\media'
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'site_adminbundle_media';
	}
}
