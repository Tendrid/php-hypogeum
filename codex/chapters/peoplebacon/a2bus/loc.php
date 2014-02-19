<?php
class a2bus_loc extends base{
	function build( $id=false ){
		$this->addCol( __CLASS__, 'id', REGEX_INT, (COL_UNIQUE_ID|COL_SEARCH_INT) );
		$this->addCol( __CLASS__, 'lat', REGEX_TEXT, (false) );
		$this->addCol( __CLASS__, 'lon', REGEX_TEXT, (false) );
		$this->addCol( __CLASS__, 'heading', REGEX_INT, (false) );
		$this->addCol( __CLASS__, 'routeid', REGEX_INT, (false) );
		$this->addCol( __CLASS__, 'dt', REGEX_INT, (false) );

		$this->setSettings('baseQuery','SELECT * FROM '.__CLASS__.' WHERE (1=1) order by dt desc LIMIT 0,1000');
	}
	
	function fromWeb(){
		$url_json = 'http://shepherdis.com/systems/umtest/location_feed.json';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_json);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$j = curl_exec($ch);
		curl_close($ch);
		$json = json_decode($j);
		
		$retVal = array();
		foreach($json->buses as $key => $val){
			$out = array(	'id'=>$val->id,
							'lat'=>$val->lat,
							'lon'=>$val->lon,
							'heading'=>$val->heading,
							'routeid'=>$val->routeid,
							'dt'=>$json->time_built);
			$retVal[] = $out;
		}
		return $retVal;
	}
}

?>