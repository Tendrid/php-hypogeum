<? #V A1.5.1
class hyve_pending extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'hyve_user_request';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	$this->_settings['db_id'][] = 'request_id';
	$this->_settings['searchVars']['integer'][] = 'user_id';
	$this->_settings['searchVars']['string'][] = 'hyve_id';

    $this->buildCols( 'hyve_user_request',
	                  array('request_id',
                            'hyve_id',
                            'user_id',
                            'permissions') );

	$this->_settings['db_uid']['hyve_user_request'] = array('request_id');
    $this->_settings['baseQuery']= "SELECT * FROM `hyve_user_request` WHERE (1=1) LIMIT 0,30";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
  
}
?>