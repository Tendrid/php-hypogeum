<? #V A1.5.1
class contact extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'contact';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	$this->_settings['db_id'][] = 'contact_id';
	#$this->_settings['searchVars']['id'][] = 'id';
	$this->_settings['searchVars']['integer'][] = 'user_id_er';
	$this->_settings['searchVars']['string'][] = 'url';

    $this->buildCols( 'contact',
	                  array('contact_id',
                            'user_id_er',
                            'user_id_ie',
                            'contact_dt',
							'follow') );

	$this->_settings['db_uid']['lilurl'] = array('contact_id');

    $this->_settings['baseQuery']= "SELECT * FROM {$this->_settings['table']} WHERE (1=1) LIMIT 0,100";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
  
}
?>