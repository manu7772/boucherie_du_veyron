<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
// yaml parser
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;
// aetools
use site\adminBundle\services\aetools;

use \DateTime;

/**
 * Service aeDebug
 * - Gestion debug
 */
class aeDebug extends aetools {

	const YAML_LEVELS = 32;			// niveaux yaml
	protected $container; 			// container

	public function __construct(ContainerInterface $container = null) {
		$this->container = $container;
		parent::__construct($this->container);
	}

	/**
	 * Écriture un fichier d'après path
	 * @param string $path
	 * @param array $array
	 * @return boolean (nb d'octets si success)
	 */
	public function dump_yaml_toFile($path, $array) {
		$this->reduceArray($array);
		$dumper = new Dumper();
		$r = @file_put_contents(
			$path,
			$dumper->dump($array, self::YAML_LEVELS)
		);
		return $r;
	}

	/**
	 * Écriture un fichier d'après path
	 * @param array $array
	 * @return boolean (nb d'octets si success)
	 */
	public function debugFile($array, $testEnv = true) {
		$this->reduceArray($array);
		$launch = true;
		$r = null;
		if($this->container !== null && $testEnv === true) {
			$env = $this->container->get('kernel')->getEnvironment();
			if(!in_array($env, array('dev', 'test'))) $launch = false;
		}
		if($launch == true) {
			$date = new DateTime();
			$dumper = new Dumper();
			$r = @file_put_contents(
				$this->gotoroot.'web/params/debugFile_'.$date->format('Ymd_His').'.yml',
				$dumper->dump($array, self::YAML_LEVELS)
			);
		}
		return $r;
	}

	protected function reduceArray(&$array) {
		if(isset($array['raw'])) if(is_string($array['raw'])) $array['raw'] = substr($array['raw'], 0, 50).' ('.strlen($array['raw']).' char.)';
	}

}

