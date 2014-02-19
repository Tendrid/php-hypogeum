<? #V A1.5.1
class image_gps extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'image_data_gps';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

	$this->_settings['searchVars']['integer'][] = 'image_id';
	$this->_settings['searchVars']['string'][] = 'latitude';


    $this->addCol( 'image_data_gps', 'attrib_id', REGEX_INT_ID, (COL_UNIQUE|COL_UNIQUE_LOOKUP|COL_PRIMARY) );
    $this->addCol( 'image_data_gps', 'image_id', REGEX_INT_ID, (COL_REQUIRED) );
    $this->addCol( 'image_data_gps', 'latitude', false, (false) );
    $this->addCol( 'image_data_gps', 'longitude', false, (false) );
    $this->addCol( 'image_data_gps', 'altitude', false, (false) );

    $this->_settings['baseQuery']= "SELECT * FROM {$this->_settings['table']} WHERE (1=1) LIMIT 0,100";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }  
}
?>