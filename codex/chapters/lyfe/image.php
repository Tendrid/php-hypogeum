<? #V A1.5.1
class image extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'image';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	#$this->_settings['db_id'][] = 'image_id';
	#$this->_settings['searchVars']['id'][] = 'id';
	$this->_settings['searchVars']['integer'][] = 'image_id';
	$this->_settings['searchVars']['string'][] = 'title';


    $this->addCol( 'image', 'image_id', REGEX_INT_ID, (COL_UNIQUE_LOOKUP|COL_PRIMARY) );
    $this->addCol( 'image', 'owner_id', REGEX_INT_ID, (COL_REQUIRED) );
    $this->addCol( 'image', 'filename', REGEX_FILENAME, (COL_REQUIRED) );
    $this->addCol( 'image', 'title', REGEX_TEXT, (false) );
    $this->addCol( 'image', 'dt', REGEX_INT, (false) );
    $this->addCol( 'image', 'accesslvl', REGEX_INT, (false) );
    $this->addCol( 'image', 'viewcount', REGEX_INT, (false) );
    $this->addCol( 'image', 'hyve_id', REGEX_INT, (false) );
    $this->addCol( 'image', 'commentcount', false, (false) );
    $this->addCol( 'image', 'tweet', false, (false) );
    $this->addCol( 'image', 'facebook', false, (false) );
    $this->addCol( 'image', 'serverloc', REGEX_INT, (false) );

	#$this->_settings['db_uid']['image'] = array('image_id');

    $this->_settings['baseQuery']= "SELECT * FROM {$this->_settings['table']} WHERE (1=1) AND (accesslvl < 100) LIMIT 0,100";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
    connect::$_codex->image->map( 'traitmap_image_tag', 'image_id', 'image_id' );
  }
  
  function delete(){
    $this->attr('accesslvl',101);
	return $this->save();
  }
  
  function extendedData(){
    return array( 'img_thumb'=>$this->getThumbImage(), 'img_large'=>$this->getFlashImage(), 'img_full'=>$this->getFullImage() );
  }
  
  function extendedData_PRE(){
    return array( 'image_id'=>EncodeBase64($this->attr('image_id')) );
  }
  
}
?>