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
use \Exception;

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
	 * Réduit les objets à leur slug, id ou autre propriété pour format YML
	 * @param array &$array
	 * @return array
	 */
	public function nameObjectcsInArray(&$array) {
		foreach ($array as $key => $item) {
			if(is_array($item)) $array[$key] = $this->nameObjectcsInArray($item);
			if(is_object($item)) $array[$key] = $this->getBestPropertyOFObject($item);
		}
		return $array;
	}

	/**
	 * Renvoie une string correspondant à l'une des propriétés de l'objet $object
	 * @param object $object
	 * @param string $methods = []
	 * @return string
	 */
	public function getBestPropertyOFObject($object, $methods = []) {
		if(is_string($methods)) $methods = array($methods);
		$methods = array_unique($methods + array('getSlug', 'getId', 'getNom'));
		foreach ($methods as $method) {
			if(method_exists($object, $method)) return $object->$method();
		}
		return get_class($object);
	}

	/**
	 * Écriture un fichier d'après path
	 * @param string $path
	 * @param array $array
	 * @return boolean (nb d'octets si success)
	 */
	public function dump_yaml_toFile($path, $array, $testEnv = true) {
		$launch = true;
		if($this->container !== null && $testEnv === true) {
			$env = $this->container->get('kernel')->getEnvironment();
			if(!in_array($env, array('dev', 'test'))) $launch = false;
		}
		if($launch == true) {
			$this->reduceArray($array);
			$dumper = new Dumper();
			$r = @file_put_contents(
				$path,
				$dumper->dump($this->nameObjectcsInArray($array), self::YAML_LEVELS)
			);
		}
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
			$inc = 0;
			$file = $this->gotoroot.'web/debug/debugFile_'.$date->format('Ymd_His').'-'.$inc.'.yml';
			while (@file_exists($file)) {
				$file = $this->gotoroot.'web/debug/debugFile_'.$date->format('Ymd_His').'-'.$inc++.'.yml';
			}
			$r = @file_put_contents(
				$file,
				$dumper->dump($this->nameObjectcsInArray($array), self::YAML_LEVELS)
			);
		}
		return $r;
	}

	/**
	 * Écriture un fichier de nom $name d'après path. Ajoute les infos en fin de fichier, avec date. 
	 * Préciser le nom du fichier uniquement, sans chemin ni extension (.yml)
	 * Le ficher est placé dans le dossier web/debug/
	 * @param string $name
	 * @param array $array
	 * @return boolean (nb d'octets si success)
	 */
	public function debugNamedFile($name, $array, $testEnv = true) {
		$launch = true;
		$r = true;
		if($this->container !== null && $testEnv === true) {
			$env = $this->container->get('kernel')->getEnvironment();
			if(!in_array($env, array('dev', 'test'))) $launch = false;
		}
		if($launch == true) {
			$dumper = new Dumper();
			$date = new DateTime();
			$dateTxt = $date->format('Y-m-d H:i:s');
			$array = array(array($dateTxt => $this->reduceArray($array)));
			$file = $this->gotoroot.'web/debug/'.$name.'.yml';
			if(file_exists($file)) {
				if(is_writable($file)) {
					$fop = @fopen($file, 'a');
					$r = $r && $fop;
					$r = $r && fwrite($fop, $dumper->dump($this->nameObjectcsInArray($array), self::YAML_LEVELS));
					$r = $r && fclose($fop);
					if($r != true) throw new Exception("Fichier Debug n'a pas pu être ajouté en écriture : ".$file, 1);
				} else throw new Exception("Fichier Debug non accessible en écriture : ".$file, 1);
			} else {
				$r = $r && file_put_contents(
					$file,
					$dumper->dump($this->nameObjectcsInArray($array), self::YAML_LEVELS)
				);
				if($r != true) throw new Exception("Fichier Debug n'a pas pu être créé en écriture : ".$file, 1);
			}
		}
		return $r;
	}

	/**
	 * Réduit la taille des chaînes trop longues dans un tableau. Méthode résursive.
	 * @param array &$array
	 * @param integer $maxSize = 50
	 * @return array
	 */
	protected function reduceArray(&$array, $maxSize = 50) {
		foreach ($array as $key => $item) {
			if(is_string($item)) if(strlen($item) > $maxSize) $array[$key] = substr($item, 0, $maxSize).' ('.strlen($item).' char.)';
			if(is_array($item)) $array[$key] = $this->reduceArray($array[$key]);
		}
		return $array;
	}

}

