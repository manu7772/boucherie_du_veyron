<?php
namespace site\adminBundle\services;

/**
 * Service JSON
 */
class toolsJson {

	/**
	 * compile les données $params sérializées
	 * @param string $params
	 * @return array
	 */
	public function JSonExtract($params) {
		$returnPdata = array();
		$pd = json_decode($params, true);
		if($pd !== null) $returnPdata = $pd;
		$params = $returnPdata;
		return $params;
	}


}