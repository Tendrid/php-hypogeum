<? #V A1.5.1
class user_hyve extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'hyve';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	$this->_settings['db_id'][] = 'hyve_id';
	$this->_settings['searchVars']['integer'][] = 'hyve_id';
	$this->_settings['searchVars']['string'][] = 'displayname';

    $this->buildCols( 'hyve',
	                  array('hyve_id',
                            'owner_id',
                            'securityname',
                            'displayname',
							'description',
							'public') );

    $this->buildCols( 'hyve_user_map',
	                  array('hyve_id',
                            'user_id',
                            'permissions') );


	$this->_settings['db_uid']['user'] = array('user_id');
    $this->_settings['baseQuery']= "SELECT * FROM hyve_user_map JOIN hyve ON hyve_user_map.hyve_id = hyve.hyve_id WHERE (1=1) LIMIT 0,30";
	
    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
  
}
?>