<? #V A1.5.1
class lilurl extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'lilurl';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	$this->_settings['db_id'][] = 'lilurl_id';
	#$this->_settings['searchVars']['id'][] = 'id';
	$this->_settings['searchVars']['integer'][] = 'lilurl_id';
	$this->_settings['searchVars']['string'][] = 'url';

    $this->buildCols( 'lilurl',
	                  array('lilurl_id',
                            'user_id',
                            'hits',
                            'url') );

	$this->_settings['db_uid']['lilurl'] = array('lilurl_id');

    $this->_settings['baseQuery']= "SELECT * FROM {$this->_settings['table']} WHERE (1=1) LIMIT 0,100";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
  
}
?>