<? #V A1.5.1
class user extends base{

  protected $_query;
  public $facebook = false;
  public $twitter = false;
  
  function build( $id=false ){

// kill below this line
    /*
	move all settings into chapter (outlines in top of base.php)
	create setSettings and getSettings that can set a deep array, and set a deep array that maps to the chapter.
	*/
    #die('dead in user page! read above notes!');
    #$class = $this->_class;
    #connect::$_codex->$class->_settings['class'] = __CLASS__;
	#var_dump(connect::$_codex->$class);die();

// kill above this line

	#$this->_settings['class'] = __CLASS__;
	#$this->setClass(__CLASS__);

    #$this->_settings['table'] = 'user';
	#$this->_settings['searchVars']['integer'][] = 'user_id';
	#$this->_settings['searchVars']['string'][] = 'displayname';

    $this->addCol( 'user', 'user_id', false, (COL_UNIQUE|COL_UNIQUE_LOOKUP|COL_PRIMARY|COL_SEARCH_INT) );
    $this->addCol( 'user', 'displayname', REGEX_SPECIAL_NAME, (COL_REQUIRED|COL_SEARCH_STR) );
    $this->addCol( 'user', 'password', REGEX_PASSWORD, (COL_PRIVATE) );
    $this->addCol( 'user', 'securityname', REGEX_TEXT, (COL_PRIVATE) );
    $this->addCol( 'user', 'personalemail', REGEX_EMAIL, (COL_REQUIRED) );
    $this->addCol( 'user', 'firstname', REGEX_NAME, (false) );
    $this->addCol( 'user', 'lastname', REGEX_NAME, (false) );
    $this->addCol( 'user', 'securityflags', REGEX_INT, (false) );
    $this->addCol( 'user', 'securityemail', REGEX_EMAIL, (COL_PRIVATE) );
    $this->addCol( 'user', 'firstin', REGEX_INT, (false) );
    $this->addCol( 'user', 'lastin', REGEX_INT, (false) );
    $this->addCol( 'user', 'zip', REGEX_INT, (COL_PRIVATE) );
    $this->addCol( 'user', 'avatar', REGEX_INT, (false) );

    $this->addCol( 'image', 'image_id', REGEX_INT_ID, (false) );
    $this->addCol( 'image', 'filename', REGEX_FILENAME, (false) );
    $this->addCol( 'image', 'title', REGEX_TEXT, (false) );
    $this->addCol( 'image', 'dt', REGEX_INT, (false) );
    $this->addCol( 'image', 'accesslvl', REGEX_INT, (false) );
    $this->addCol( 'image', 'viewcount', REGEX_INT, (false) );
    $this->addCol( 'image', 'hyve_id', REGEX_INT, (false) );
    $this->addCol( 'image', 'serverloc', REGEX_INT, (false) );

    $this->setSettings('baseQuery','SELECT * FROM user LEFT JOIN `image` ON (user.avatar = image.image_id) WHERE (1=1) LIMIT 0,100');

    #$this->_settings['baseQuery']= "SELECT * FROM user LEFT JOIN `image` ON (user.avatar = image.image_id) WHERE (1=1) LIMIT 0,100";
    if( $this->init($id) === false){
	  return false;
	}
	// dont do this. it doesnt scale
    #connect::$_codex->user->map( 'user_contact', 'user_id', 'user_id_er' );
  }
  
  protected function postBuild($id){
/*
    $facebook = connect::$_codex->user_auth_facebook->search($this->attr('user_id'),false,'owner_id');
    $twitter = connect::$_codex->user_auth_twitter->search($this->attr('user_id'),false,'owner_id');
    if( $facebook !=false ){
      if(count($facebook) ==1 ){
        $this->facebook = $facebook[0];
      }else{
      
      }
    }
    if( $twitter !=false ){
      if(count($twitter) ==1 ){
        $this->twitter = $twitter[0];
      }else{
      
      }
    }
*/
  }
  
  protected function PRESAVE_securityname($key){
    $t = connect::$_codex->user->search($key,false,'securityname');
	if($t !== array() ){
	  if($t[0]->attr('user_id') != $this->attr('user_id')){
        errorHandler::report(ERR_FATAL,'securityname already exists');
	    return false;
	  }
	}
	return true;
  }
  protected function PRESAVE_personalemail($key){
    if( $key == '' ){ return true; }
    $t = connect::$_codex->user->search($key,false,'personalemail');  
	if($t !== array() ){
	  if($t[0]->attr('user_id') != $this->attr('user_id')){
        errorHandler::report(PUB_FATAL,'personalemail already exists');
        errorHandler::report(ERR_FATAL,'personalemail already exists');
	    return false;
	  }
	}
	return true;
  }
  protected function PRESAVE_displayname($key){
    $t = connect::$_codex->user->search($key,false,'displayname');  
	if($t !== array() ){
	  if($t[0]->attr('user_id') != $this->attr('user_id')){
        errorHandler::report(PUB_FATAL,'displayname already exists');
        errorHandler::report(ERR_FATAL,'displayname already exists', 1000);
	    return false;
	  }
	}
	return true;
  }
  
  protected function POSTCREATE(){
    if( $this->createMailEntry() == false ){
      errorHandler::report(ERR_FATAL, 'Creation of Mail Entry for user failed');
	}
  }
  
  public function login($personalemail, $password, $from=false){
    //TODO change to md5 support
	#var_dump($from);die();
	$preAuth = false;
	if( strtolower($this->attr('personalemail')) == strtolower($personalemail) && $this->getPassword() == $password ){
	  $preAuth = true;
	}
	if( $personalemail === false && $password === false ){
	  $preAuth = true;
	}
    if( $preAuth ){
	  if( $from != false ){
	    if( $from->getSettings('class') == 'user_auth_twitter' || $from->getSettings('class') == 'user_auth_facebook' ){
	      $this->lastLogin();
	      if( $this->save() == true ){
            connect::$_codex->_authUser($this);
            return true;
          }else{
	        return false;
	      }
		}
	  }
      if( $this->getPassword() == '' && $this->attr('personalemail') != '' ){ return false; }

      #$facebook = connect::$_codex->user_auth_facebook($this->attr('user_id'));
      #$twitter = connect::$_codex->user_auth_twitter($this->attr('user_id'));
	  #if( $facebook !=false ){ $this->facebook = $facebook; }
	  #if( $twitter !=false ){ $this->twitter = $twitter; }
      connect::$_codex->_authUser($this);
	  $this->lastLogin();
	  if( $this->save() == true ){
        return true;
      }else{
	    return false;
	  }
	}else{
	  return false;
	}
  }
  
  public function lastLogin(){
    $this->attr('lastin',mktime());
  }
  
  public function getPassword(){
    return $this->_private['password'];
  }

  public function getSecurityname(){
    return $this->_private['securityname'];
  }
  
  public function logout(){
    session_destroy();
	$_SESSION = array();
  }

  function extendedData_PRE(){
    $col = 'personalemail';
    if( $this->attr($col) !== NULL ){
      $this->_private[$col] = $this->attr($col);
      unset( $this->_public[$col] );
	  return true;
    }else{
	  return false;
	}

    #return array( 'img_thumb'=>$this->getThumbImage(), 'img_large'=>$this->getFlashImage(), 'img_full'=>$this->getFullImage() );
  }
#*/  
  public function createMailEntry(){
    #function scrubbed
  }
    
}
?>
