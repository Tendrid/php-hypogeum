<? #V A1.5.1
class hyve_member extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'hyve_user_map';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	$this->_settings['db_id'][] = 'map_id';
	$this->_settings['searchVars']['integer'][] = 'user_id';
	$this->_settings['searchVars']['string'][] = 'hyve_id';

    $this->buildCols( 'hyve_user_map',
	                  array('map_id',
                            'hyve_id',
                            'user_id',
                            'permissions') );

	$this->_settings['db_uid']['hyve_user_map'] = array('map_id');
    $this->_settings['baseQuery']= "SELECT * FROM `hyve_user_map` WHERE (1=1) LIMIT 0,30";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
  
}
?>