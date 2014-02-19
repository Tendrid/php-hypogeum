<? #V A1.5.1
class oauth_consumer extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'oauth_consumer';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	$this->_settings['db_id'][] = 'consumer_id';
	$this->_settings['searchVars']['integer'][] = 'consumer_id';
	$this->_settings['searchVars']['string'][] = 'consumer_displayname';

    $this->buildCols( 'oauth_consumer',
	                  array('consumer_id',
                            'consumer_key',
                            'consumer_secret',
                            'consumer_displayname',
							'consumer_description') );


	$this->_settings['db_uid']['oauth_consumer'] = array('consumer_id');
    $this->_settings['baseQuery']= "SELECT * FROM oauth_consumer WHERE (1=1) LIMIT 0,30";
	
    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
  
}
?>