<?php
//http://outagemap.serv.dteenergy.com/GISRest/services/OMP/OutageLocations/MapServer/2/query?f=json&text=a&where=Shape_Length%3E5&outFields=JOB_ID%2CNUM_CUST%2CEST_REP_DTTM%2CCAUSE%2COFF_DTTM%2CADD_DTTM%2CCIRCUIT_EST_DTTM%2CCIRCUIT_EST_ENDDTTM%2CEST_REP_ENDDTTM%2CEST_REP_DTTM
class dteOutage extends base{
	function build( $id=false ){
		//$this->addCol( __CLASS__, 'id', false, (COL_AUTOINC|COL_UNIQUE_ID|COL_SEARCH_INT) );
		//$this->addCol( __CLASS__, 'dt', REGEX_INT, (false) );
		//$this->addCol( __CLASS__, 'spots', REGEX_INT, (false) );
		//$this->addCol( __CLASS__, 'title', REGEX_TEXT, (false) );
		
		$this->addCol( __CLASS__, 'OBJECTID', false, (false) );
		$this->addCol( __CLASS__, 'Shape', false, (false) );
		$this->addCol( __CLASS__, 'JOB_ID', false, (COL_UNIQUE_ID|COL_REQUIRED) );
		$this->addCol( __CLASS__, 'ADD_DTTM', false, (false) );
		$this->addCol( __CLASS__, 'TYCOD', false, (false) );
		$this->addCol( __CLASS__, 'NUM_CUST', false, (false) );
		$this->addCol( __CLASS__, 'NUM_CUST_RESTORED', false, (false) );
		$this->addCol( __CLASS__, 'TOAL_CUST_AFFECTED', false, (false) );
		$this->addCol( __CLASS__, 'OFF_DTTM', false, (false) );
		$this->addCol( __CLASS__, 'EST_REP_DTTM', false, (false) );
		$this->addCol( __CLASS__, 'CAUSE', false, (false) );
		$this->addCol( __CLASS__, 'DEV_NAME', false, (false) );
		$this->addCol( __CLASS__, 'DEV_ID', false, (false) );
		$this->addCol( __CLASS__, 'DEV_TYPE', false, (false) );
		$this->addCol( __CLASS__, 'DEV_TYPE_NAME', false, (false) );
		$this->addCol( __CLASS__, 'EVENT_STATUS', false, (false) );
		$this->addCol( __CLASS__, 'DISPATCH_DTTM', false, (false) );
		$this->addCol( __CLASS__, 'CREW_STATUS', false, (false) );
		$this->addCol( __CLASS__, 'EST_REP_ENDDTTM', false, (false) );
		$this->addCol( __CLASS__, 'CIRCUIT_EST_DTTM', false, (false) );
		$this->addCol( __CLASS__, 'CIRCUIT_EST_ENDDTTM', false, (false) );
		$this->addCol( __CLASS__, 'STORM_MODE', false, (false) );
		$this->addCol( __CLASS__, 'Shape_Length', false, (false) );
		$this->addCol( __CLASS__, 'Shape_Area', false, (false) );
		$this->addCol( __CLASS__, 'area', false, (false) );
		$this->addCol( __CLASS__, 'closedt', REGEX_INT, (false) );
		
		$this->setSettings('baseQuery','SELECT * FROM '.__CLASS__.' WHERE (1=1) LIMIT 0,50000');
	}

	function fromWeb(){
		$tmpFile = '/tmp/dteOutage.kmz';
		$url_json = 'http://outagemap.serv.dteenergy.com/GISRest/services/OMP/OutageLocations/MapServer/2/query?f=json&where=Shape_Length%3E5&outFields=OBJECTID%2CShape%2CJOB_ID%2CADD_DTTM%2CTYCOD%2CNUM_CUST%2CNUM_CUST_RESTORED%2CTOAL_CUST_AFFECTED%2COFF_DTTM%2CEST_REP_DTTM%2CCAUSE%2CDEV_NAME%2CDEV_ID%2CDEV_TYPE%2CDEV_TYPE_NAME%2CEVENT_STATUS%2CDISPATCH_DTTM%2CCREW_STATUS%2CEST_REP_ENDDTTM%2CCIRCUIT_EST_DTTM%2CCIRCUIT_EST_ENDDTTM%2CSTORM_MODE%2CShape_Length%2CShape_Area';
		$url_kmz = 'http://outagemap.serv.dteenergy.com/GISRest/services/OMP/OutageLocations/MapServer/2/query?f=kmz&where=Shape_Length%3E5&outFields=OBJECTID%2CShape%2CJOB_ID%2CADD_DTTM%2CTYCOD%2CNUM_CUST%2CNUM_CUST_RESTORED%2CTOAL_CUST_AFFECTED%2COFF_DTTM%2CEST_REP_DTTM%2CCAUSE%2CDEV_NAME%2CDEV_ID%2CDEV_TYPE%2CDEV_TYPE_NAME%2CEVENT_STATUS%2CDISPATCH_DTTM%2CCREW_STATUS%2CEST_REP_ENDDTTM%2CCIRCUIT_EST_DTTM%2CCIRCUIT_EST_ENDDTTM%2CSTORM_MODE%2CShape_Length%2CShape_Area';
		$fileSize_json = 0;
		$minFileSize_json = 150; // empty is 136
		$fileSize_kmz = 0;
		$minFileSize_kmz = 300; // empty is 283
		
		while($fileSize_json < $minFileSize_json){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url_json);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$j = curl_exec($ch);
			curl_close($ch);
			$fileSize_json = strlen($j);
			if($fileSize_json < $minFileSize_json){ sleep(1); echo "Had to wait for json\n"; }
		}
		$json = json_decode($j);
		

		while($fileSize_kmz < $minFileSize_kmz){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url_kmz);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$k = curl_exec($ch);
			curl_close($ch);

			$tmp = fopen($tmpFile,'w');
			fwrite($tmp, $k);
			fclose($tmp);

			$zip = zip_open($tmpFile);
			$zip_entry = zip_read($zip);
			#echo "Actual Filesize:    " . zip_entry_filesize($zip_entry) . "\n";
			if($zip_entry != false){
				if (zip_entry_open($zip, $zip_entry, "r")) {
					$kml = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
					zip_entry_close($zip_entry);
				}

				zip_close($zip);
				$fileSize_kmz = zip_entry_filesize($zip_entry);
			}
			if($fileSize_kmz < $minFileSize_kmz){ sleep(1); echo "Had to wait for kmz\n"; }
			unlink($tmpFile);
		}
		

		$kmlObj = simplexml_load_string($kml);
		// name == DEV_NAME
		$area = Array();
		foreach($kmlObj->Document->Folder->Placemark as $key => $val){
			$area[$val->name->__toString()] = $val->MultiGeometry->Polygon->outerBoundaryIs->LinearRing->coordinates->__toString();
		}

		$retVal = Array();
		foreach($json->features as $key => $val){
			$vals = Array();
			foreach($val->attributes as $k=>$v){
				$vals[$k] = $v;
			}
			$vals['area'] = trim($area[$val->attributes->DEV_NAME]);
			$vals['closedt'] = 0;
			$retVal[] = $vals;
		}
		$this->resetCloseDT();
		return($retVal);
	}

	function resetCloseDT(){
		$this->rawSql('update dteOutage set closedt = '.mktime().' where closedt = 0');
	}
}
/*

JOB_ID%2CNUM_CUST%2CEST_REP_DTTM%2CCAUSE%2COFF_DTTM%2CADD_DTTM%2CCIRCUIT_EST_DTTM%2CCIRCUIT_EST_ENDDTTM%2CEST_REP_ENDDTTM%2CEST_REP_DTTM

OBJECTID%2CShape%2CJOB_ID%2CADD_DTTM%2CTYCOD%2CNUM_CUST%2CNUM_CUST_RESTORED%2CTOAL_CUST_AFFECTED%2COFF_DTTM%2CEST_REP_DTTM%2CCAUSE%2CDEV_NAME%2CDEV_ID%2CDEV_TYPE%2CDEV_TYPE_NAME%2CEVENT_STATUS%2CDISPATCH_DTTM%2CCREW_STATUS%2CEST_REP_ENDDTTM%2CCIRCUIT_EST_DTTM%2CCIRCUIT_EST_ENDDTTM%2CSTORM_MODE%2CShape_Length%2CShape_Area

 */
?>
