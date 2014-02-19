<? #V A1.5.1
class trigger_facebook extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'trigger_facebook';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	$this->_settings['db_id'][] = 'trigger_id';
	$this->_settings['searchVars']['integer'][] = 'user_id';
	$this->_settings['searchVars']['string'][] = 'facebook_id';

    $this->buildCols( 'trigger_facebook',
	                  array('trigger_id',
                            'user_id',
                            'facebook_id',
                            'dt' ) );
							
    $this->requiredCols( array('user_id',
                               'facebook_id',
                               'dt' ) );
							   
    $this->secureCols( array('user_id' => REGEX_INT,
                             'facebook_id' => REGEX_INT,
                             'dt' => REGEX_INT ) );


	$this->_settings['db_uid']['trigger_facebook'] = array('trigger_id');

    $this->_settings['baseQuery']= "SELECT * FROM {$this->_settings['table']} WHERE (1=1) LIMIT 0,30";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
  
}
?>