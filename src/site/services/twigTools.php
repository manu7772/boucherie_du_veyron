<?php

namespace site\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use site\services\primarydata;
use \Twig_Extension;
use \Datetime;

class twigTools extends Twig_Extension {

	private $decal;
	private $html;
	private $tab;
	private $container;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	public function getFunctions() {
		return array(
			'datatables_hidden'		=> new \Twig_Function_Method($this, 'datatables_hidden'),
			'correctTransField'		=> new \Twig_Function_Method($this, 'correctTransField'),
			'phraseCut'				=> new \Twig_Function_Method($this, 'phraseCut'),
			'colonizeWords'			=> new \Twig_Function_Method($this, 'colonizeWords'),
			'colonizeWordsWithP'	=> new \Twig_Function_Method($this, 'colonizeWordsWithP'),
			'cleanSpaces'			=> new \Twig_Function_Method($this, 'cleanSpaces'),
			'adminDataType'			=> new \Twig_Function_Method($this, 'adminDataType'),
			'intervalDateFR'		=> new \Twig_Function_Method($this, 'intervalDateFR'),
			'dateFR'				=> new \Twig_Function_Method($this, 'dateFR'),
			'minUCfirst'			=> new \Twig_Function_Method($this, 'minUCfirst'),
			'UCfirst'				=> new \Twig_Function_Method($this, 'UCfirst'),
			'magnifyText'			=> new \Twig_Function_Method($this, 'magnifyText'),
			'addZeros'				=> new \Twig_Function_Method($this, 'addZeros'),
			'dureeHM'				=> new \Twig_Function_Method($this, 'dureeHM'),
			'arrayprint'			=> new \Twig_Function_Method($this, 'arrayprint'),
			'slug'					=> new \Twig_Function_Method($this, 'slug'),
			'siteNFormat'			=> new \Twig_Function_Method($this, 'siteNFormat'),
			'pathTree'				=> new \Twig_Function_Method($this, 'pathTree'),
			'simpleURL'				=> new \Twig_Function_Method($this, 'simpleURL'),
			'Url_encode'			=> new \Twig_Function_Method($this, 'Url_encode'),
			'googleMapURL'			=> new \Twig_Function_Method($this, 'googleMapURL'),
			'serializeT'			=> new \Twig_Function_Method($this, 'serializeT'),
			'unserializeT'			=> new \Twig_Function_Method($this, 'unserializeT'),
			'paramsByUrl'			=> new \Twig_Function_Method($this, 'paramsByUrl'),
			'implode'				=> new \Twig_Function_Method($this, 'implode'),
			'plur'					=> new \Twig_Function_Method($this, 'pluriel'),
			'valueOfObject'			=> new \Twig_Function_Method($this, 'valueOfObject'),
			'imgVolume'				=> new \Twig_Function_Method($this, 'imgVolume'),
			'annee'					=> new \Twig_Function_Method($this, 'annee'),
			'URIperform'			=> new \Twig_Function_Method($this, 'URIperform'),
			'fillOfChars'			=> new \Twig_Function_Method($this, 'fillOfChars'),
			'idify'					=> new \Twig_Function_Method($this, 'idify'),
			'zerosDevant'			=> new \Twig_Function_Method($this, 'zerosDevant'),
			'doNl2br'				=> new \Twig_Function_Method($this, 'doNl2br'),
			'firstCapOnly'			=> new \Twig_Function_Method($this, 'firstCapOnly'),
			// tests types
			'is_string'				=> new \Twig_Function_Method($this, 'is_string'),
			'is_object'				=> new \Twig_Function_Method($this, 'is_object'),
			// Flashbag
			'getFlashes'			=> new \Twig_Function_Method($this, 'getFlashes'),
			'compareRoles'			=> new \Twig_Function_Method($this, 'compareRoles'),
			'permittedRoles'		=> new \Twig_Function_Method($this, 'permittedRoles'),
			'B64forImg'				=> new \Twig_Function_Method($this, 'B64forImg'),
			'thumbnail'				=> new \Twig_Function_Method($this, 'thumbnail'),
			);
	}

	public function getName() {
		return 'twigTools';
	}

	public function datatables_hidden($language = null) {
		$aeTrans = $this->container->get('aetools.translate');
		if(!in_array($language, $aeTrans->getLanguages()))
			$language = $aeTrans->getCurrentLocale();
		$bundle = 'siteadmin';
		$domain = 'datatables';
		$data = $aeTrans->parse_yaml($bundle, $domain, $language);
		// $data = array($language => array("decimal" => "X", "paginate" => array("previous" => "HOP !")));
		return json_encode($data, true);
	}

	public function correctTransField($field) {
		return str_replace('___', '.', $field);
	}

	/**
	 * Renvoie le texte $t réduit à $n lettres / Sans couper les mots
	 * si $tre = true (par défaut), ajoute "..." à la suite du texte
	 * Pour autoriser le coupage de mots, mettre $_Wordcut à "true"
	 * @param string
	 * @param intger
	 * @param boolean
	 * @param boolean
	 * @return string
	 */
	public function phraseCut($t, $n, $tre=true, $wordcut=false) {
		$t = strip_tags($t);
		$prohib=array(' ',',',';','.');
		if(strlen($t)>=$n) {
			$r1=substr($t, 0, $n);
			if(!$wordcut) while(substr($r1, -1)!=" " && strlen($r1)>0) $r1=substr($r1, 0, -1);
			if(strlen($r1)<1) $r1=substr($t, 0, $n);
			if(in_array(substr($r1, -1), $prohib)) $r1=substr($r1, 0, -1);
			if($tre) $r1=trim($r1)."…";
		} else $r1=$t;
		return trim($r1);
	}

	/**
	 * Renvoie à la ligne pour optimiser un texte sur une colonne. 
	 * Pratique dans les tableaux
	 * @param string $t - texte
	 * @param intger $n - nombre de lettres maxi avant retour à la ligne
	 * @return string
	 */
	public function colonizeWords($t, $n, $separ = "br", $cutmot = true) {
		if($cutmot !== true) $cutmot = false;
		$voyelles = array("a", "e", "i", "o", "u", "y");
		$consonnes = array("b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "q", "r", "s", "t", "v", "w", "x", "z");
		// réduction pour faire une moyenne
		if($n > 5) $n = $n - 2;
		// supprime espaces en trop
		$t = $this->cleanSpaces(trim($t));
		$changes = array(" ", "-");
		$glueword = "-";
		$cpt = 1;
		$line = 0;
		$txr = array();
		$txr[$line] = "";
		// Génération des lignes de texte en tableau
		for ($i=0; $i < strlen($t); $i++) {
			$char = substr($t, $i, 1);
			$cutm = false;
			if($cutmot === true && $i > ($n - 1)) {
				// voyelle suivie d'une consonne… ou deux mêmes lettres
				if((in_array(strtolower($char), $voyelles) && in_array(strtolower(substr($t, $i + 1, 1)), $consonnes)) || (strtolower($char) == strtolower(substr($t, $i + 1, 1)))) {
					// au moins deux lettres avant de couper… et moins d'une lettre avant la fin du mot
					if(strtolower(substr($t, $i -1, 1)) != " " && strtolower(substr($t, $i -2, 1)) != " " && strtolower(substr($t, $i +1, 1)) != " " && strtolower(substr($t, $i +2, 1)) != " " && $i < (strlen($t) - 2)) {
						$cutm = true;
					}
				}
			}
			if($cpt > $n && (in_array($char, $changes) || $cutm === true)) {
				if($char == " ") $char = "";
				if($cutm === true) $char = $char.$glueword;
				$cpt = 1;
				$txr[$line] .= $char;
				$line++;
				$txr[$line] = "";
			} else {
				$cpt++;
				$txr[$line] .= $char;
			}
		}
		// ajout des séparateurs
		switch (strtolower($separ)) {
			case 'p':
			case 'span':
			case 'div':
			case 'li':
				$balise = strtolower($separ);
				$result = '<'.$balise.'>'.implode('</'.$balise.'><'.$balise.'>', $txr).'</'.$balise.'>';
				break;
			default: // <br> par défaut
				$result = implode('<br>', $txr);
				break;
		}
		return $result;
	}

	/**
	 * Crée des lignes <p> pour optimiser un texte sur une colonne. 
	 * Pratique dans les tableaux
	 * @param string $t - texte
	 * @param intger $n - nombre de lettres maxi avant retour à la ligne
	 * @return string
	 */
	public function colonizeWordsWithP($t, $n) {
		return $this->colonizeWords($t, $n, 'p');
	}

	/**
	 * Supprime les espaces doubles (ou plus) d'une phrase
	 * @param string $t - texte
	 * @param intger $n - nombre d'espaces à supprimer (à partir de 2, par défaut)
	 * @return string
	 */
	public function cleanSpaces($t, $n = 2) {
		return preg_replace('#\s{'.$n.',}#', " ", $t);
	}


	/**
	 * Renvoie la donnée sous forme de données admin
	 * "true" ou "false" pour un booléen, par exemple
	 * @param data
	 * @return string
	 */
	public function adminDataType($data, $miseEnForme = true, $developpe = false) {
		if(is_bool($data)) {
			if($data === true) $miseEnForme?$r = "<span style='color:green;'>#true</span>":$r = "#true";
				else $miseEnForme?$r = "<span style='color:red;'>#false</span>":$r = "#false";
			return $r;
		} else if(is_array($data)) {
			if($developpe === true) {
				$txt = serialize($data);
			} else {
				$txt = count($data);
			}
			$miseEnForme?$r = "<span style='color:blue;font-style:italic;'>(#array ".$txt.")</span>":$r = "(#array ".$txt.")";
			return $r;
		} else if(is_object($data)) {
			$miseEnForme?$r = "<span style='color:blue;font-style:italic;'>(#object)</span>":$r = "(#object)";
			return $r;
		} else {
			return $data;
		}
	}

	/**
	 * developpeArray
	 * 
	 * Transforme un array() en informations texte
	 * @param data
	 * @return string
	 */
	public function developpeArray($data) {
		if(is_array($data)) {
			$r = $this->developpeArray_recursive($data);
		} else $r = $data;
		return $r;
	}
	public function developpeArray_recursive($data) {
		$sep = "";
		if(is_array($data) && count($data) > 0) foreach($data as $nom => $vals) {
			if(is_array($vals)) $r = $sep.$nom." = ".$this->developpeArray_recursive($vals);
				else $r = $sep.$nom." = ".$vals;
			$sep = " | ";
		} else {
			$r = $data;
		}
		return $r;
	}

	/**
	 * developpeObject
	 * 
	 * Transforme un object() en informations texte
	 * @param data
	 * @return string
	 */
	public function developpeObject($data) {
		return print_r($data);
	}


	/**
	 * Transforme une date au format jj/mm/aa en aaaa-mm-jj pour utilisation Datetime
	 * @param string $date
	 * @return string
	 */
	protected function reform($date) {
		$date2 = explode('/', $date);
		if(is_array($date2)) {
			if(count($date2) == 3) {
				$date2 = $date2[2]."-".$date2[1]."-".$date2[0];
			} else $date2 = $date;
		} else $date2 = $date;
		return $date2;
	}

	public function intervalDateFR($datedebut, $datefin = null, $short = false) {
		// dates en string
		if(is_string($datedebut)) $datedebut = new Datetime($this->reform($datedebut));
		if(is_string($datefin)) $datefin = new Datetime($this->reform($datefin));

		if(($datefin === null) && (is_object($datedebut))) {
			$txt = "le ".$this->dateFR($datedebut, $short);
		} else if((is_object($datedebut)) && (is_object($datefin))) {
			$dd = $this->dateFR($datedebut, $short);
			$df = $this->dateFR($datefin, $short);
			// supprime l'année sur date de début si identique à celle de la date de fin
			if(substr($dd, -4) == substr($df, -4)) $dd = substr($dd, 0, strlen($dd) - 5);
			$txt = "du ".$dd." au ".$df;
		} else $txt = "";
		return $txt;
	}

	public function dateFR($date, $short = false) {
		if(is_string($date)) $date = new Datetime($this->reform($date));
		$sup = array(1);
		if($short === false) {
			$jours = array(
				"Sunday" 	=> "dimanche",
				"Monday" 	=> "lundi",
				"Tuesday" 	=> "mardi",
				"Wednesday" => "mercredi",
				"Thursday" 	=> "jeudi",
				"Friday" 	=> "vendredi",
				"Saturday" 	=> "samedi",
				);
			$mois = array(
				"January" 	=> "janvier",
				"February" 	=> "février",
				"March" 	=> "mars",
				"April" 	=> "avril",
				"May" 		=> "mai",
				"June" 		=> "juin",
				"July" 		=> "juillet",
				"August" 	=> "août",
				"September" => "septembre",
				"October" 	=> "octobre",
				"November" 	=> "novembre",
				"December" 	=> "décembre",
				);
		} else {
			$jours = array(
				"Sunday" 	=> "dim",
				"Monday" 	=> "lun",
				"Tuesday" 	=> "mar",
				"Wednesday" => "mer",
				"Thursday" 	=> "jeu",
				"Friday" 	=> "ven",
				"Saturday" 	=> "sam",
				);
			$mois = array(
				"January" 	=> "jan",
				"February" 	=> "fév",
				"March" 	=> "mar",
				"April" 	=> "avr",
				"May" 		=> "mai",
				"June" 		=> "jun",
				"July" 		=> "jul",
				"August" 	=> "aou",
				"September" => "sep",
				"October" 	=> "oct",
				"November" 	=> "nov",
				"December" 	=> "déc",
				);
		}
		$jj = $jours[$date->format('l')];
		$j = $date->format('j');
		if(in_array(intval($j), $sup)) $j .= "<sup>er</sup>";
		$m = $mois[$date->format('F')];
		$a = $date->format('Y');
		return $jj." ".$j." ".$m." ".$a;
	}

	/**
	 * minUCfirst
	 * 
	 * met la chaîne en minuscules et remet les premières en cap
	 * @param string
	 * @return string
	 */
	public function minUCfirst($t) {
		return (ucfirst(strtolower($t)));
	}

	/**
	 * UCfirst
	 * 
	 * met la première lettre en cap
	 * @param string
	 * @return string
	 */
	public function UCfirst($t) {
		return ucfirst($t);
	}

	/**
	 * Remplace les espaces après les mots courts par des espaces insécables pour une meilleure gestion des retours à la ligne
	 * @param string
	 * @return string
	 */
	public function magnifyText($t) {
		// supprime les espaces inutiles
		$t = $this->cleanSpaces($t);
		$search = array(
			" Les ",
			" et ",
			" ou ",
			" où ",
			" du ",
			" sur ",
			" les ",
			" au ",
			" un ",
			" une ",
			" si ",
			" la ",
			" le ",
			" de ",
			" des ",
			" à ",
			" a ",
			" :",
			" ;",
			" ?",
			" !",
			);
		$replace = array(
			" Les&nbsp;",
			" et&nbsp;",
			" ou&nbsp;",
			" où&nbsp;",
			" du&nbsp;",
			" sur&nbsp;",
			" les&nbsp;",
			" au&nbsp;",
			" un&nbsp;",
			" une&nbsp;",
			" si&nbsp;",
			" la&nbsp;",
			" le&nbsp;",
			" de&nbsp;",
			" des&nbsp;",
			" à&nbsp;",
			" a&nbsp;",
			"&nbsp;:",
			"&nbsp;;",
			"&nbsp;?",
			"&nbsp;!",
			);
		// PASSE 1
		$t = str_replace($search, $replace, $t);

		$search = array(
			"&nbsp;et ",
			"&nbsp;ou ",
			"&nbsp;où ",
			"&nbsp;du ",
			"&nbsp;sur ",
			"&nbsp;les ",
			"&nbsp;au ",
			"&nbsp;un ",
			"&nbsp;une ",
			"&nbsp;si ",
			"&nbsp;la ",
			"&nbsp;le ",
			"&nbsp;de ",
			"&nbsp;des ",
			"&nbsp;à ",
			"&nbsp;a ",
			);
		$replace = array(
			"&nbsp;et&nbsp;",
			"&nbsp;ou&nbsp;",
			"&nbsp;où&nbsp;",
			"&nbsp;du&nbsp;",
			"&nbsp;sur&nbsp;",
			"&nbsp;les&nbsp;",
			"&nbsp;au&nbsp;",
			"&nbsp;un&nbsp;",
			"&nbsp;une&nbsp;",
			"&nbsp;si&nbsp;",
			"&nbsp;la&nbsp;",
			"&nbsp;le&nbsp;",
			"&nbsp;de&nbsp;",
			"&nbsp;des&nbsp;",
			"&nbsp;à&nbsp;",
			"&nbsp;a&nbsp;",
			);
		// PASSE 2
		$t = str_replace($search, $replace, $t);

		return $t;
	}

	/**
	 * addZeros
	 * 
	 * Renvoie le nombre $chiffre avec des zéros devant pour faire une longueur de $n chiffres
	 * @param string
	 * @return string
	 */
	public function addZeros($chiffre, $n) {
		$s = $chiffre."";
		while(strlen($s) < $n) {
			$s = "0".$s;
		}
		return $s;
	}

	/**
	 * Renvoie un texte en heures pour une durée $duree en minutes
	 * @param int
	 * @return string
	 */
	public function dureeHM($duree) {
		$duree = intval($duree);
		$t = "";
		if($duree < 2) $t = $duree." minute";
		if($duree < 60 && $t === "") $t = $duree." minutes";
		if($duree > 59 && $t === "") {
			$h = floor($duree / 60);
			$m = fmod($duree, 60);
			$mt = " minute";
			if($h > 1) $s = "s"; else $s = "";
			if($h > 0) {
				$t = $h." heure".$s;
				$esp = " ";
				$mt = "";
			} else {
				$esp = "";
			}
			if($m > 1 && $mt !== "") $mt .= "s";
			if($m > 0) $t .= $esp.$m.$mt;
		}
		return $t;
	}

	/**
	 * Renvoie (array) les paramètres passés dans $def (string)
	 * Séparer les paramètres par un "&"
	 * Par ex. : "article=5&option=ok"
	 * si ça n'est pas une requête GET (sans les "=" et "&"), renvoie la valeur tout simplement
	 * Si aucun paramètre, renvoie null
	 * 
	 * @param string $def
	 */
	public function ParamStrAnalyse($def) {
		// $def = urldecode($def);
		if(is_string($def)) {
			// supprime le "?" s'il existe
			if(substr($def,0,1) == "?") $def = substr($def,1);
			$str = explode('&', $def);
			if(count($str) > 1) {
				$result = array();
				foreach ($str as $value) {
					$exp = explode('=', $value);
					if(isset($exp[1])) $result[$exp[0]] = $exp[1];
					else $result[] = $exp[0];
				}
			} else {
				$result = $def;
			}
			return $result;
		} else return null;
	}

	/**
	 * Renvoie le prix au format pour le site
	 *
	 * @param $number = prix
	 * @param $money = ajoute "€HT" si true (null par défaut) / ou on peut préciser un texte spécifique "$", etc.
	 */
	public function siteNFormat($number, $money = null) {
		if($money === true) {
			$money = "<sup> €HT</sup>";
		} else if(!is_string($money)) $money = null;
		return number_format($number, 2, ',', '').$money;
	}


	/**
	 * pathTree
	 *
	 */
	public function pathTree($items) {
		$r = array();
		foreach ($items as $item) {
			$r[] = $item->getSlug();
		}
		return $r;
	}

	/**
	 * Renvoie un slug du titre $title
	 *
	 * @param string $title
	 */
	public function slug($title, $d = 0) {
		if($id < 1) $id=""; else $id = "-".intval($id);
		if(is_string($title)) {
			$maxlen = 42;  //Modifier la taille max du slug ici
			$slug = strtolower($title);
			$slug = preg_replace("/[^a-z0-9s-]/", "", $slug);
			$slug = trim(preg_replace("/[s-]+/", " ", $slug));
			$slug = preg_replace("/s/", "-", $slug);
			$slug .= $id;
		} else return false;
		return $slug;
	}

	/**
	 * simpleURL
	 * Renvoie l'URL simplifiée : sans http:// ou https://
	 *
	 * @param string $URL
	 */
	public function simpleURL($URL) {
		return str_replace(array("http://", "https://"), "", $URL);
	}
	/**
	 * Url_encode
	 * encode l'URL pour envoi GET
	 *
	 * @param string $URL
	 */
	public function Url_encode($URL) {
		return urlencode($URL);
	}

	/**
	 * googleMapURL
	 * Renvoie l'adresse formatée pour google maps
	 *
	 * @param string
	 */
	public function googleMapURL($adresse) {
		return str_replace(" ", "+", $adresse);
	}

	/**
	 * serializeT
	 * Renvoie la chaîne unserialisée (PHP : serialize())
	 *
	 * @param string $data
	 */
	public function serializeT($data) {
		return serialize($data);
	}

	/**
	 * unserializeT
	 * Renvoie la chaîne unserialisée (PHP : unserialize())
	 *
	 * @param string $data
	 */
	public function unserializeT($data) {
		return unserialize($data);
	}

	/**
	 * paramsByUrl
	 * Renvoie du tableau fourni une chaîne comptatible URL pour passer en paramètre
	 *
	 * @param array $data
	 * @return string
	 */
	public function paramsByUrl($data) {
		$r = array();
		foreach($data as $nom => $val) $r[] = $nom."=".$val;
		$result = implode("&", $r);
		return "?".$result;
	}

	/**
	 * implode
	 * Renvoie du tableau fourni une chaîne comptatible URL pour passer en paramètre
	 *
	 * @param string/array $lk
	 * @param array $data
	 * @return string
	 */
	public function implode($lk, $data = null) {
		return implode($lk, $data);
	}

	/**
	 * pluriel
	 * Renvoie un "s" si count($elem) > 1
	 * on peut remplacer le "s" par "x" ou autre
	 * @param $elem
	 * @param $s
	 * @return string
	 */
	public function pluriel($elem, $s = "s") {
		$r = "";
		if(count($elem) > 1) $r = $s;
		return $r;
	}

	/**
	 * valueOfObject
	 * Renvoie la valeur de l'attribut "private" d'un objet
	 * ATTENTION : la classe doit contenir le getter correspondant !!
	 * @param $obj
	 * @param $nom
	 * @return une valeur
	 */
	public function valueOfObject($obj, $nom) {
		$methode = "get".ucfirst($nom);
		if(method_exists($obj, $methode)) return $obj->$methode();
			else return null;
	}

	/**
	 * imgVolume
	 * Renvoie le texte pour la largeur d'une image selon un volume donnée $vol
	 * ($vol correspond au nombre de pixels voulus / 1000 : soit $vol = 10 soit 10000 pixels)
	 * Possibilité de fixer une largeur et hauteur maximales
	 * @param $img
	 * @param $vol
	 * @param $xmax
	 * @param $ymax
	 * @return une valeur
	 */
	public function imgVolume($img, $vol = 10, $xmax = null, $ymax = null) {
		$vol = $vol * 1000;
		$x = $finalX = $img->getTailleX(); // 100  -  
		$y = $finalY = $img->getTailleY(); // 200  -  
		$volume = $x * $y; // 20 000
		$ratio = $x / $y; // 0.5
		if(($vol > 0) && ($volume > $vol)) {
			$ratio_vol = $vol / $volume;
			// $finalX = $vol 
		}
		if($xmax !== null && $finalX > $xmax) {
			$finalX = $xmax;
			$finalY = $xmax / $ratio;
		}
		if($ymax !== null && $finalY > $ymax) {
			$finalX = $ymax * $ratio;
			$finalY = $ymax;
		}
		return "width:".round($finalX)."px;";
	}

	/**
	 * annee
	 * Renvoie l'année en cours
	 * @return string
	 */
	public function annee() {
		$date = new Datetime();
		return $date->format("Y");
	}

	/**
	 *
	 */
	public function URIperform($t) {
		$search = array(
			"###ROOT###",
			);
		$replace = array(
			$this->container->get("request")->getBasePath(),
			);
		$t = str_replace($search, $replace, $t);
		return $t;
	}

	/**
	 * Remplit un texte avec des espaces (ou $char) pour obtenir une chaîne de la longueur $n
	 * @param $string - chaîne de caractères
	 * @param $n - nombre de caractères voulus au total
	 * @param $char - caractère de remplissage (espace, par défaut)
	 * @param $cut - 
	 * @return string
	 */
	public function fillOfChars($string, $n, $char = " ", $cut = true) {
		if(strlen($string) !== $n) {
			// mot de taille différente de $n
			if(strlen($string) > $n) {
				// mot plus long
				$string = substr($string, 0, $n-1)."…";
			} else {
				// mot plus court
				while(strlen($string) < $n) {$string .= $char;}
				// recoupe si trop long finalement
				// en effet, on peut mettre plusieurs caractères comme $char de remplissage ! ;-)
				if(strlen($string) > $n) {
					// mot plus long
					$string = substr($string, 0, $n);
				}
			}
		}
		return $string;
	}

	/**
	 * Transforme le texte en élément utilisable pour une classe ou un id. 
	 * sans espace ou caractères conflictuels
	 * @param string $text
	 * @return string
	 */
	public function idify($text) {
		$trans = array(
			" " => '_',
			"-" => '_',
			"%" => '',
			"#" => '',
			"*" => '',
			"&" => '',
			);
		return strtr($text, $trans);
	}


	public function zerosDevant($t, $long = 2) {
		$l = strlen($t."");
		while($this->testVide($t, $long)) {
			$t = "0".$t;
		}
		return $t;
	}

	public function doNl2br($t) {
		return nl2br($t);
	}

	public function is_string($elem) {
		return is_string($elem);
	}

	public function is_object($elem) {
		return is_object($elem);
	}

	public function firstCapOnly($t) {
		$sep = "-";
		$t = explode($sep, $t);
		foreach ($t as $key => $text) {
			$t[$key] = ucfirst(strtolower($text));
		}
		return implode($sep, $t);
	}

	public function getFlashes() {
		$primarydata = $this->container->getParameter('messages');
		$defaultAttributes = $primarydata['default_params'];
		$mess = $primarydata['types'];
		$messages = array();
		foreach ($mess as $type => $title) {
			$bags = $this->container->get("session")->getFlashBag()->get($type);
			foreach ($bags as $messageArray) {
				$message = array();
				if(is_string($messageArray)) {
					$message['texte'] = $messageArray;
				} else if(is_array($messageArray)) {
					if(count($messageArray) === 1) {
						$message['texte'] = reset($messageArray);
					} else if(count($messageArray) > 1) {
						$message = $messageArray;
						if(isset($messageArray['texte'])) {
							if(is_array($messageArray['texte'])) {
								$message['texte'] = implode("<br>", $messageArray['texte']);
							} else if(is_string($messageArray['texte'])) {
								$message['texte'] = $messageArray['texte'];
							} else $message['texte'] = "…";
						} else {
							$message['texte'] = "…";
						}
					}
				}
				// attributs par défaut
				$message['type'] = $type;
				if(!isset($message['title'])) $message['title'] = $title;
				foreach ($defaultAttributes as $method => $attr) {
					// if(!isset($message[$method])) $message[$method] = htmlentities($attr, ENT_QUOTES);
					if(!isset($message[$method])) $message[$method] = $attr;
				}
				// au-dessus de 10s de timeOut, on force le closeButton sur true
				if($message['timeOut'] > 9999) $message['closeButton'] = true;
				$messages[] = $message;
			}
		}
		return count($messages) > 0 ? array('messages' => $messages) : "";
	}

	protected function getBestRole($roles) {
		$rolesDefault = array(
			'ROLE_USER' => 0,
			'ROLE_TRANSLATOR' => 5,
			'ROLE_EDITOR' => 7,
			'ROLE_ADMIN' => 10,
			'ROLE_SUPER_ADMIN' => 20
			);
		$bestRole = null;
		$value = reset($rolesDefault);
		foreach ($roles as $role) {
			if($rolesDefault[$role] > $value) {
				$value = $rolesDefault[$role];
				$bestRole = $role;
			}
		}
		return $bestRole;
	}

	protected function addLowerRoles($roles) {
		$role = $this->getBestRole($roles);
		$result = array();
		$rolesDefault = array(
			'ROLE_USER' => 0,
			'ROLE_TRANSLATOR' => 5,
			'ROLE_EDITOR' => 7,
			'ROLE_ADMIN' => 10,
			'ROLE_SUPER_ADMIN' => 20
			);
		foreach ($rolesDefault as $key => $value) {
			if($value <= $rolesDefault[$role]) $result[] = $key;
		}
		return $result;
	}

	public function compareRoles($user1, $user2, $superieur = true) {
		$user[1] = $user1;
		$user[2] = $user2;
		$rolesDefault = array(
			'ROLE_USER' => 0,
			'ROLE_TRANSLATOR' => 5,
			'ROLE_EDITOR' => 7,
			'ROLE_ADMIN' => 10,
			'ROLE_SUPER_ADMIN' => 20
			);
		$roles[1] = $user[1]->getRoles();
		$roles[2] = $user[2]->getRoles();
		reset($rolesDefault);
		$userMaxRole[1] = $userMaxRole[2] = key($rolesDefault);
		foreach ($roles as $n => $allRoles) {
			foreach ($allRoles as $oneRole) {
				if($rolesDefault[$oneRole] > $rolesDefault[$userMaxRole[$n]]) $userMaxRole[$n] = $oneRole;
			}
		}
		if($superieur) return $rolesDefault[$userMaxRole[1]] > $rolesDefault[$userMaxRole[2]];
		else return $rolesDefault[$userMaxRole[1]] >= $rolesDefault[$userMaxRole[2]];
	}

	// permittedRoles
	public function permittedRoles($user, $roles) {
		$userRole = $this->getBestRole($user->getRoles());
		if(count($roles) < 1 || !is_array($roles)) $roles = array($userRole);
		return in_array($userRole, $this->addLowerRoles($roles));
	}

	public function B64forImg($version) {
		if(is_object($version))
			$format = $version->getFormat()->getContentType();
			else $format = 'image/png';
		return 'data:'.$format.';base64,'.base64_encode(stream_get_contents($version->getBinaryfile()));
	}

	public function thumbnail($file, $format = 'image/png') {
		return 'data:'.$format.';base64,'.$file->getB64File();
	}

}