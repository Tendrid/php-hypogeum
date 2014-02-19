<? #V A1.5.1
class hyve extends base{

  protected $_query;

  function build( $id=false ){
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    $this->_settings['table'] = 'hyve';
	$this->_settings['searchVars']['integer'][] = 'hyve_id';
	$this->_settings['searchVars']['string'][] = 'displayname';
	$this->_settings['searchVars']['string'][] = 'description';

    $this->addCol( 'hyve', 'hyve_id', REGEX_INT_ID, (COL_UNIQUE|COL_UNIQUE_LOOKUP|COL_PRIMARY) );
    $this->addCol( 'hyve', 'owner_id', REGEX_INT_ID, (COL_REQUIRED) );
    $this->addCol( 'hyve', 'securityname', false, (COL_REQUIRED) );
    $this->addCol( 'hyve', 'displayname' );
    $this->addCol( 'hyve', 'description' );
    $this->addCol( 'hyve', 'public' );

	$this->_settings['baseQuery']= "SELECT * FROM `hyve` where (1=1) LIMIT 0,30";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }

  protected function PRESAVE_securityname($key){
    $t = connect::$_codex->hyve->search($key,false,'securityname');
	if($t !== array()){
	  if($t[0]->attr('hyve_id') != $this->attr('hyve_id')){
        errorHandler::report(PUB_FATAL,'securityname already exists');
        errorHandler::report(ERR_FATAL,'securityname already exists');
	    return false;
	  }
	}
	return true;
  }

  protected function PRESAVE_displayname($key){
    $t = connect::$_codex->hyve->search($key,false,'displayname');  
	if($t !== array() ){
	  if($t[0]->attr('hyve_id') != $this->attr('hyve_id')){
        errorHandler::report(PUB_FATAL,'displayname already exists');
        errorHandler::report(ERR_FATAL,'displayname already exists');
	    return false;
	  }
	}
	return true;
  }

  
}
?>