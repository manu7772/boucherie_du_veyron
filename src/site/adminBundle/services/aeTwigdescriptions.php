<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\services\aeEntity;
use \Exception;

class aeTwigdescriptions extends aeEntity {

	protected $commandNames;

	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
		$this->commandNames = array(
			'repository',
			);
	}

	public function getCommandesInFile($file) {
		$result = array();
		$file = $this->gotoroot.self::SOURCE_FILES.$file;
		if(file_exists($file)) {
			$result = $this->getCommandsInContent(@file_get_contents($file));
		} else {
			throw new Exception("aeTwigdescriptions::getCommandesInFile() : le fichier n'existe pas \"".$file."\"", 1);
		}
		return $result;
	}

	protected function getCommandsInContent($content = null) {
		$commands = array();
		if($content.'' != '') {
			$results = array();
			preg_match_all('"{#[ ]+@('.implode('|', $this->commandNames).')[ ]+({.+})[ ]+#}"', $content, $results, PREG_SET_ORDER);
			// echo('<pre>');
			// var_dump($results);
			// echo('</pre>');
			// foreach ($results as $item) {
			// 	echo('<pre>');
			// 	echo('<h3>'.$item[1].'</h3>');
			// 	var_dump(json_decode($item[2], true));
			// 	echo('</pre>');
			// }
			// die('<p>END</p>');
			foreach ($results as $key => $item) {
				$commands[$key] = array();
				$commands[$key][] = $item[1];
				$commands[$key][] = json_decode($item[2], true);
			}
			// echo('<pre>');
			// echo('<h3>'.$item[1].'</h3>');
			// var_dump($commands);
			// die('<p>END</p>');
		}
		return $this->computeCommands($commands);
	}

	public function getCommandNames() {
		return $this->commandNames;
	}

	public function computeCommands($commands) {
		$data = array();
		foreach ($commands as $key => $value) {
			switch ($value[0]) {
				case 'repository':
					$method = $value[1]['method'];
					$classname = $value[1]['classname'];
					$service = $this->getEntityService($classname);
					$params = null;
					if(isset($value[1]['params'])) $params = $value[1]['params'];
					$data[$value[1]['name']] = $service->getRepo($classname)->$method($params);
					break;
				default:
					throw new Exception("Commande aeTwigdescriptions non supportée : ".$value[0], 1);
					break;
			}
		}
		return $data;
	}

}