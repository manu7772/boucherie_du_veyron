<?php
namespace site\adminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class insColorpickerType extends AbstractType {

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			// 'widget' => 'single_text',
			'empty_value' => '#ffffff',
			// 'format' => $this->formatDate
		));
	}

	public function getParent() {
		return 'text';
	}

	public function getName() {
		return 'insColorpicker';
	}
}