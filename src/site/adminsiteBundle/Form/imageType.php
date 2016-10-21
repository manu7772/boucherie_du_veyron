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
use Labo\Bundle\AdminBundle\services\aetools;

class imageType extends baseType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// ajout de action si défini
		$this->initBuilder($builder);
		// paramètres cropper
		if(isset($this->parametres['image'])) {
			// propriétaire (entité liée)
			$imagesData = $this->parametres['image']; // Owner
		} else if(null !== $builder->getData()) {
			// si pas de lien, info dans la colonne "owner"
			$imagesData['owner'] = $builder->getData()->getOwner();
		}
		$cropperInfo = $this->controller->getParameter('cropperInfo');
		// formats par défaut
		$imagesData['formats'] = $cropperInfo['formats']['default'];
		// Formats propriétaire…
		if(isset($imagesData['owner'])) {
			$field = explode(':', $imagesData['owner']);
			if(count($field) > 1) {
				if(isset($cropperInfo['formats'][$field[0]][$field[1]]))
					$imagesData['formats'] = $cropperInfo['formats'][$field[0]][$field[1]];
			}
		}
		$imagesData['ratioIndex'] = 0;
		$imagesData['plain'] = '#';
		$imagesData['init'] = null;
    	// Builder…
    	// nom à ne pas mettre si imbriqué
    	if(null !== $builder->getData()) {
    		// le formulaire n'a pas de parent
			$builder
				->add('nom', 'text', array(
					'label'         => 'fields.nom',
					'translation_domain' => 'image',
					'disabled'      => false,
					'required'		=> false,
					))
				// ->add('owner', 'text', array(
				// 	'label'         => 'fields.owner',
				// 	'translation_domain' => 'image',
				// 	'disabled'      => true,
				// 	'required'		=> true,
				// 	))
				// ->add('statut')
				;
		} else {
			// pas d'image à l'origine
			$builder->add('binaryFile', 'filecropper', array(
				'label' => 'fields.binaryFile',
				'translation_domain' => 'image',
				'required'		=> false,
				'plain_image' => $imagesData['plain'],
				'cropper' => array(
					'init' => $imagesData['init'],
					'ratioIndex' => $imagesData['ratioIndex'],
					'options' => array(
						"flipable" => false,
						"zoomable" => false,
						"rotatable" => false,
						),
					'deletable' => true,
					'format' => $imagesData['formats'],
					'accept' => ".jpeg,.jpg,.png,.gif",
					'filenameCopy' => array('nom', 'originalnom'),
					'maxfilesize' => 12,
					),
				))
			;
		}

		$builder
			->add('originalnom', 'hidden', array(
				'label' => 'fields.originalnom',
				'required'		=> false,
				'translation_domain' => 'image',
				))
			->add('infoForPersist', 'hidden', array(
				'required'		=> false,
				))
		;

		$builder->addEventListener(
			FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($imagesData) {
				$data = $event->getData();
				$form = $event->getForm();
				// à conserver !! ci-dessous
				if(null === $data) return;
				// à conserver !! ci-dessus

				// ratio
				$imagesData['ratioIndex'] = $data->getRatioIndex();
				$raw = $data->getRawfile();
				if(is_object($raw)) $imagesData['plain'] = $raw->getCropThumbnail();
				$imagesData['init'] = $data->getCroppingInfo();	
				$form->add('binaryFile', 'filecropper', array(
					'label' => 'fields.binaryFile',
					'translation_domain' => 'image',
					'required'		=> false,
					'plain_image' => $imagesData['plain'],
					'cropper' => array(
						'init' => $imagesData['init'],
						'ratioIndex' => $imagesData['ratioIndex'],
						'options' => array(
							"flipable" => false,
							"zoomable" => false,
							"rotatable" => false,
							),
						'deletable' => true,
						'format' => $imagesData['formats'],
						'accept' => ".jpeg,.jpg,.png,.gif",
						'filenameCopy' => array('nom', 'originalnom'),
						'maxfilesize' => 12,
						),
					))
				;
			}
		);


		// ajoute les valeurs hidden, passés en paramètre
		$this->addHiddenValues($builder, true);
		// $this->addSubmit($builder);
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'site\adminsiteBundle\Entity\image'
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'site_adminsitebundle_image';
	}



}
