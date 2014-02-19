<?php
class _error extends base{

  protected $_query;

  function build( $id=false ){
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    $this->_settings['table'] = H_DBNAME.'error';
	$this->_settings['searchVars']['integer'][] = 'error_id';
	$this->_settings['searchVars']['string'][] = 'description';

    $this->addCol( $this->_settings['table'], 'error_id', REGEX_INT_ID, (COL_UNIQUE|COL_UNIQUE_LOOKUP|COL_PRIMARY) );
    $this->addCol( $this->_settings['table'], 'description' );
    $this->addCol( $this->_settings['table'], 'code' );
    $this->addCol( $this->_settings['table'], 'file' );
    $this->addCol( $this->_settings['table'], 'line' );
    $this->addCol( $this->_settings['table'], 'dt' );
    $this->addCol( $this->_settings['table'], 'public' );

	$this->_settings['baseQuery']= "SELECT * FROM `{$this->_settings['table']}` where (1=1) LIMIT 0,30";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
  
  function createFromException($e){
	$trace = $e->getTrace();
    $error = array_shift($trace);
	$this->fill( array('description'=>$error['args'][1],
                       'code'=>$error['args'][0],
                       'file'=>$error['file'],
                       'line'=>$error['line'],
                       'dt'=>mktime(),
                       'public'=>0) );
    $this->save();
	foreach( $trace as $key => $val ){
      $trace = connect::$_codex->_error_trace(false);
      $trace->fill( array('error_id'=>$this->attr('error_id'),
                          'file'=>$val['file'],
                          'line'=>$val['line'],
                          'function'=>@$val['function'],
                          'class'=>@$val['class'],
                          'args'=>serialize($val['args']) ) );
      $trace->save();
    }
  }
  
}
?>