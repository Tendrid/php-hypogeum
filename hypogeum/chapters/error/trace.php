<?php
class _error_trace extends base{

  protected $_query;

  function build( $id=false ){
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    $this->_settings['table'] = 'hypo_error_trace';
	$this->_settings['searchVars']['integer'][] = 'error_id';
	$this->_settings['searchVars']['string'][] = 'description';

    $this->addCol( $this->_settings['table'], 'trace_id', REGEX_INT_ID, (COL_UNIQUE|COL_UNIQUE_LOOKUP|COL_PRIMARY) );
    $this->addCol( $this->_settings['table'], 'error_id', REGEX_INT_ID );
    $this->addCol( $this->_settings['table'], 'file' );
    $this->addCol( $this->_settings['table'], 'line' );
    $this->addCol( $this->_settings['table'], 'function' );
    $this->addCol( $this->_settings['table'], 'class' );
    $this->addCol( $this->_settings['table'], 'args' );

	$this->_settings['baseQuery']= "SELECT * FROM `{$this->_settings['table']}` where (1=1) LIMIT 0,30";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
}
?>