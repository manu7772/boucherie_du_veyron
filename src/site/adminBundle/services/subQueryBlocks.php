<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;

class subQueryBlocks extends \Twig_Extension {

	private $container;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	public function getFunctions() {
		return array(
			'SQBlock'			=> new \Twig_Function_Method($this, 'SQBlock'),
			);
	}

	public function getName() {
		return 'subQueryBlocks';
	}

	/**
	 * SQBlock
	 * 
	 * @return string
	 */
	public function SQBlock() {
		return '<h1>TEST SQBlock</h1>';
	}

}