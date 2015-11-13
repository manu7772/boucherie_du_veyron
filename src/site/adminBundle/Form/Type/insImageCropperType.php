<?php
namespace site\adminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class insImageCropperType extends AbstractType {

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
		));
	}

	public function getParent() {
		return 'file';
	}

	public function getName() {
		return 'insImageCropper';
	}
}