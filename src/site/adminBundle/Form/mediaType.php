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

    private $controller = null;
    private $securityContext = null;
    private $parametres;
    
    public function __construct(Controller $controller = null, $parametres = null) {
    	if(null !== $controller) {
	        $this->controller = $controller;
	        $this->securityContext = $controller->get('security.context');
	    }
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
    	$builder->getData() == null ? $deletable = true : $deletable = false;
		$builder
			->add('nom', 'text', array(
				'label'         => 'form.nom',
				'translation_domain' => 'messages',
				'disabled'      => false,
				'required'		=> false,
				))
			->add('originalnom', 'hidden', array(
				'required'		=> false,
				'translation_domain' => 'messages',
				))
			->add('infoForPersist', 'hidden', array(
				'required'		=> false,
				'translation_domain' => 'messages',
				))
			->add('binaryFile', 'filecropper', array(
				'label' => 'form.telechargement',
				'translation_domain' => 'messages',
				'required'		=> false,
				'cropper' => array(
					'options' => array(
						"flipable" => true,
						"zoomable" => true,
						"rotatable" => true,
						),
					'deletable' => $deletable,
					'format' => array('x' => 800, 'y' => 600),
					),
				'attr' => array(
					'cropper-formats' => json_encode(array(
						array("width" => 800, "height" => 600),
						// array("width" => 600, "height" => 800),
						)
					),
					'cropper-options' => json_encode(array(
						"rotatable" => true,
						)
					),
					'filename-copy' => 'originalnom',
					'cropper-accept' => ".jpeg,.jpg,.png,.gif",
					'deletable' => $deletable,
					),
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

        // AJOUT SUBMIT seulement si pas de parent
        if(!($builder->getData() == null)) {
	        $builder->add('submit', 'submit', array(
	            'label' => 'form.enregistrer',
	            'translation_domain' => 'messages',
	            'attr' => array(
	                'class' => "btn btn-md btn-block btn-info",
	                ),
	            ))
	        ;
	    }
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
