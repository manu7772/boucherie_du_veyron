<?php
namespace site\adminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class insDatepickerType extends AbstractType {

	private $formatDate;

	public function __construct($formatDate) {
		$this->formatDate = $formatDate;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'widget' => 'single_text',
			'format' => $this->formatDate
		));
	}

	public function getParent() {
		return 'date';
	}

	public function getName() {
		return 'insDatepicker';
	}
}