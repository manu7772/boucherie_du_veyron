<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use \Twig_SimpleFunction;
use \Twig_Extension;

class subQueryBlocks extends Twig_Extension {

	private $container;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	public function getFunctions() {
		return array(
			new Twig_SimpleFunction('SQBlock', array($this, 'SQBlock')),
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