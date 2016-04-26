<?php

namespace site\adminBundle\Form;

use Symfony\Component\Form\AbstractType;
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

use site\adminBundle\services\aeEntity;
use site\adminBundle\Entity\statutRepository;

abstract class baseType extends AbstractType {

	protected $controller;
	protected $securityContext;
	protected $parametres;
	protected $_em;
	protected $aeEntities = null;
	
	public function __construct(Controller $controller = null, $parametres = array()) {
		$this->controller = $controller;
		if(null !== $this->controller) {
			$this->_em = $this->controller->get('doctrine')->getManager();
			$this->aeEntities = $this->controller->get('aetools.aeEntity');
			$this->securityContext = $controller->get('security.context');
			$this->user = $this->securityContext->getToken()->getUser();
		}
		$this->parametres = $parametres;
	}


	protected function initBuilder(FormBuilderInterface &$builder) {
		// ajout de action si défini
		if(isset($this->parametres['form_action'])) $builder->setAction($this->parametres['form_action']);

		$data = $builder->getData();
		if(is_object($data)) {
			$this->aeEntities->checkStatuts($data, false);
			// if($this->aeEntities->checkStatuts($data, false))
			// 	echo('<p>Statut "'.$data->getStatut()->getNom().'" ajouté à '.get_class($data).' depuis "'.get_called_class().'"</p>');
			// 	else
			// 		echo('<p>Statut "'.$data->getStatut()->getNom().'" déjà existant à '.get_class($data).' depuis "'.get_called_class().'"</p>');
		}

		$aeEntities = $this->aeEntities;
		$builder->addEventListener(
			FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($aeEntities) {
				$data = $event->getData();
				$form = $event->getForm();
				// à conserver !! ci-dessous
				if(null === $data) return;
				// ajout du statut par défaut si null
				if(is_object($aeEntities)) $aeEntities->checkStatuts($data, false);
				// if($aeEntities->checkStatuts($data, false)) 
				// 	echo('<p>Statut "'.$data->getStatut()->getNom().'" ajouté à '.get_class($data).' depuis "'.get_called_class().'" (by event)</p>');
				// 	else
				// 		echo('<p>Statut "'.$data->getStatut()->getNom().'" déjà existant à '.get_class($data).' depuis "'.get_called_class().'" (by event)</p>');
			}
		);

	}

	protected function addSubmit(FormBuilderInterface &$builder, $text = 'form.enregistrer') {
		// AJOUT SUBMIT
		if(!($builder->getData() == null)) {
			$builder->add('submit', 'submit', array(
				'label' => $text,
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
	protected function addHiddenValues(FormBuilderInterface &$builder, $addSubmit = false, $text = 'form.enregistrer') {
		if(!($builder->getData() == null)) {
			if($addSubmit == true) $this->addSubmit($builder, $text);
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
			// entités liées par défaut
			// $entity = $builder->getData();
			// $this->aeEntities->fillWithDefaultLinked($entity);
			// $this->aeEntities->fillAllAssociatedFields($entity);
		}
		// return
		return $builder;
	}

}
