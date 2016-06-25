<?php
namespace site\adminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class bs_buttonType extends AbstractType {

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			"attr"	=> array("class" => 'btn btn-primary btn-xs btn-outline'),
			"label"	=> '<i class="fa fa-times icon-wait-on-click"></i> Supprimer',
		));
	}

	public function getParent() {
		return 'button';
	}

	public function getName() {
		return 'bs_button';
	}
}