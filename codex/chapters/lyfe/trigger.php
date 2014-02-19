<? #V A1.5.1
class trigger extends base{

  protected $_query;
  
  function build( $id=false ){
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    $this->_settings['table'] = 'trigger';
	$this->_settings['searchVars']['integer'][] = 'image_id';
	$this->_settings['searchVars']['string'][] = 'title';

    $this->addCol( 'trigger', 'trigger_id', false, (COL_UNIQUE_LOOKUP|COL_PRIMARY) );
    $this->addCol( 'trigger', 'user_id', REGEX_INT, (COL_REQUIRED) );
    $this->addCol( 'trigger', 'event_id', REGEX_INT, (COL_REQUIRED) );
    $this->addCol( 'trigger', 'chapter_id', REGEX_INT, (COL_REQUIRED) );
    $this->addCol( 'trigger', 'object_id', REGEX_INT, (COL_REQUIRED) );
    $this->addCol( 'trigger', 'expire_dt', false, (false) );
    $this->addCol( 'trigger', 'txt', REGEX_INT, (COL_REQUIRED) );
    $this->addCol( 'trigger', 'email', REGEX_EMAIL, (COL_REQUIRED) );
    $this->addCol( 'trigger', 'lilurl_id', REGEX_INT, (COL_REQUIRED) );

    $this->_settings['baseQuery']= "SELECT * FROM {$this->_settings['table']} WHERE (1=1) LIMIT 0,100";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
    
}
?>