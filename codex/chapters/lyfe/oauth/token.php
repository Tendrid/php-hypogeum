<? #V A1.5.1
class oauth_token extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'oauth_token';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	$this->_settings['db_id'][] = 'token_id';
	$this->_settings['searchVars']['integer'][] = 'token_id';
	$this->_settings['searchVars']['string'][] = 'request_token';

    $this->buildCols( 'oauth_token',
	                  array('token_id',
                            'request_token',
                            'user_id',
                            'consumer_id',
							'dt') );


	$this->_settings['db_uid']['oauth_token'] = array('token_id');
    $this->_settings['baseQuery']= "SELECT * FROM oauth_token WHERE (1=1) LIMIT 0,30";
	
    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
  
}
?>