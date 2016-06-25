<?php
namespace site\adminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

use site\adminBundle\services\aetools;

class filecropperType extends AbstractType {

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		// $info = $this->getCropperInfo();
		// $modelWidth = $info['modelWidth'];
		$resolver->setDefaults(
			array(
				'plain_image' => '#',
				'cropper' => array(
					'init' => null,
					// 'modelWidth' => $modelWidth,
					'ratioIndex' => 0,
					'options' => array(
						"flipable" => false,
						"zoomable" => false,
						"rotatable" => false,
						),
					'deletable' => false,
					'format' => array(),
					'accept' => ".jpeg,.jpg,.png,.gif",
					'filenameCopy' => array(),
					),
			)
		);
	}

	public function buildView(FormView $view, FormInterface $form, array $options) {
		$view->vars['cropper'] = $options['cropper'];
		$view->vars['plain_image'] = $options['plain_image'];
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->setAttribute('cropper', $options['cropper']);
		$builder->setAttribute('plain_image', $options['plain_image']);
	}

	public function getParent() {
		return 'textarea';
	}

	public function getName() {
		return 'filecropper';
	}

	// public function getCropperInfo() {
	// 	$aetools = new aetools();
	// 	$data = $aetools->getConfigParameters('cropper.yml');
	// 	return $data;
	// }

}