<?php
/*
INCIDENTNU		AA110032607,
entered			07/08/2011,
CRIMECLASS		3597,
CRIMECLA_1		OPEN GENERIC,
CRIMECLA_2		3500,
CRIMEGROUP		NON-CRIMINAL COMPLAINTS,
class			C,
OCCURRENCE		07/08/2011,
GEN_ADD			4000 BLOCK PLATT RD,
POINT_X			-83.69974897,
POINT_Y			42.22686920

AA110033215,07/11/2011,3114,ACC, INJURY TYPE C       ,3100,TRAFFIC CRASHES,C,07/11/2011,S MAIN ST&E HOOVER AVE,-83.75019370,42.26934323
AA110033215,07/11/2011,3114,ACC, INJURY TYPE C       ,3100,TRAFFIC CRASHES,C,07/11/2011,S MAIN ST&E HOOVER AVE,-83.75019370,42.26934323
AA110033927,07/15/2011,3730,TRAFFIC MISCELLANEOUS A COMPLAINT,3700,MISCELLANEOUS TRAFFIC COMPLAINTS,C,07/15/2011,S MAIN ST&E HOOVER AVE,-83.75019370,42.26934323

*/
class a2crime extends base{
	function build( $id=false ){
		$this->addCol( __CLASS__, 'INCIDENTNU', false, (COL_UNIQUE_ID|COL_REQUIRED) );
		$this->addCol( __CLASS__, 'entered', false, (false) );
		$this->addCol( __CLASS__, 'CRIMECLASS', false, (false) );
		$this->addCol( __CLASS__, 'CRIMECLA_1', false, (false) );
		$this->addCol( __CLASS__, 'CRIMECLA_2', false, (false) );
		$this->addCol( __CLASS__, 'CRIMEGROUP', false, (false) );
		$this->addCol( __CLASS__, 'class', false, (false) );
		$this->addCol( __CLASS__, 'OCCURRENCE', false, (false) );
		$this->addCol( __CLASS__, 'GEN_ADD', false, (false) );
		$this->addCol( __CLASS__, 'POINT_X', false, (false) );
		$this->addCol( __CLASS__, 'POINT_Y', false, (false) );

		$this->setSettings('baseQuery','SELECT * FROM '.__CLASS__.' WHERE (1=1) LIMIT 0,100');
	}
	
	function fromWeb(){
	//var_dump(3%11);die();
		$list = array();
		foreach($this->_pubAttr['a2crime'] as $key => $val){
			$list[] = $key;
		}

		$url_csv = 'http://data.a2gov.org/feeds/safetyservices/calls_for_service.csv';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_csv);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$j = curl_exec($ch);
		curl_close($ch);
		
		$j = str_replace("\n",',',$j);
		$cont = str_getcsv($j);
		$x=0;
		$arr = array();
		while($curr = current($cont)){
			if($curr != 'ACC'){
				$j = $x%11;
				$arr[floor($x/11)][$list[$j]] = $curr;
				$x++;
			}
			next($cont);		
		}
		return $arr;
	}
}

?>