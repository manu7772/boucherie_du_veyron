<?php
namespace site\adminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class filecropperType extends AbstractType {

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(
			array(
				'cropper' => array(
					'options' => array(
						"flipable" => true,
						"zoomable" => true,
						"rotatable" => true,
						),
					'deletable' => false,
					'format' => array(),
					// 'format' => array('x' => 800, 'y' => 600),
					),
			)
		);
	}

	public function buildView(FormView $view, FormInterface $form, array $options) {
		$view->vars['cropper'] = $options['cropper'];
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->setAttribute('cropper', $options['cropper']);
	}

	public function getParent() {
		return 'textarea';
	}

	public function getName() {
		return 'filecropper';
	}
}