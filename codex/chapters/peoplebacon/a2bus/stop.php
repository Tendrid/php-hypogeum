<?php
class a2bus_stop extends base{
	public $json = '';
	
	function build( $id=false ){
		$this->addCol( __CLASS__, 'pkey', false, (COL_UNIQUE_ID|COL_SEARCH_INT) );
		$this->addCol( __CLASS__, 'routeid', REGEX_INT, (false) );
		$this->addCol( __CLASS__, 'number', REGEX_INT, (false) );
		$this->addCol( __CLASS__, 'name', REGEX_TEXT, (false) );
		$this->addCol( __CLASS__, 'name2', REGEX_TEXT, (false) );
		$this->addCol( __CLASS__, 'name3', REGEX_TEXT, (false) );
		$this->addCol( __CLASS__, 'latitude', REGEX_TEXT, (false) );
		$this->addCol( __CLASS__, 'longitude', REGEX_TEXT, (false) );
		$this->addCol( __CLASS__, 'designation', REGEX_TEXT, (false) );
		$this->addCol( __CLASS__, 'toas', REGEX_TEXT, (false) );
		$this->addCol( __CLASS__, 'dt', REGEX_INT, (false) );
		
		$this->setSettings('baseQuery','SELECT * FROM '.__CLASS__.' WHERE (1=1) LIMIT 0,100');
	}
	
	function fromWeb(){
		$url_json = 'http://shepherdis.com/systems/umtest/public_feed.json';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_json);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$j = curl_exec($ch);
		curl_close($ch);
		$json = json_decode($j);
		$this->json = $json;
		$retVal = array();
		foreach($json->families as $key => $val){
			foreach($val->routes[0]->stops as $k => $v){
				$out = array(	'pkey'=>$val->routes[0]->id.$v->number,
								'routeid'=>$val->routes[0]->id,
								'number'=>$v->number,
								'name'=>$v->name,
								'name2'=>$v->name2,
								'name3'=>$v->name3,
								'latitude'=>$v->latitude,
								'longitude'=>$v->longitude,
								'designation'=>$v->designation,
								'toas'=>json_encode($v->toas),
								'dt'=>mktime());
				$retVal[] = $out;
			}
		}
		return $retVal;
	}
	
	function getRoutes(){
		$retVal = Array();
		foreach($this->json->families as $key => $val){
			$retVal[] = array('route_id'=>$val->routes[0]->id,'route'=>$val->routes[0]->name);
		}
		return $retVal;
	}
}

?>