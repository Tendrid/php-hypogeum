<?php

class geoloc extends base{

  protected $_query;
  
  function build( $id=false ){
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    $this->_settings['table'] = 'geoloc';
    $this->_settings['searchVars']['integer'][] = 'geoloc_id';
    $this->_settings['searchVars']['string'][] = 'name';

    $this->addCol( 'geoloc', 'geoloc_id', REGEX_INT_ID, (COL_UNIQUE|COL_UNIQUE_LOOKUP|COL_PRIMARY) );
    $this->addCol( 'geoloc', 'twitterloc_id', REGEX_TEXT, (false) );
    $this->addCol( 'geoloc', 'country', false, (false) );
    $this->addCol( 'geoloc', 'city', false, (false) );
    $this->addCol( 'geoloc', 'name', false, (false) );
    $this->addCol( 'geoloc', 'lat1', false, (false) );
    $this->addCol( 'geoloc', 'lon1', false, (false) );
    $this->addCol( 'geoloc', 'lat2', false, (false) );
    $this->addCol( 'geoloc', 'lon2', false, (false) );
    $this->addCol( 'geoloc', 'lat3', false, (false) );
    $this->addCol( 'geoloc', 'lon3', false, (false) );
    $this->addCol( 'geoloc', 'lat4', false, (false) );
    $this->addCol( 'geoloc', 'lon4', false, (false) );
    $this->addCol( 'geoloc', 'ran1', false, (false) );
    $this->addCol( 'geoloc', 'ran2', false, (false) );
    $this->addCol( 'geoloc', 'ran3', false, (false) );
    $this->addCol( 'geoloc', 'ran4', false, (false) );
	

    $this->_settings['baseQuery']= "SELECT * FROM {$this->_settings['table']} WHERE (1=1) LIMIT 0,100";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }

}

?>