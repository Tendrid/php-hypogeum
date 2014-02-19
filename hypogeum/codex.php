<?

require_once('chapter.php');

/**
 * Codex
 * This is the main container object for their entire site.
 * All objects are created through codex
 */


class codex extends layout{
  var $_pageVars;
  var $_authUser = false;
  var $_errors = array();
  var $_errorCodes = array();
  var $debugVars = array();
  private $_queries = array();
  private $_db;
  private $_projectName;

  function __construct($codex){
	$this->initDebugVars();
	$this->_projectName = $codex;
    $this->_db = new PDO('mysql:host='.DATABASE_LOC.';dbname='.DATABASE_NAME, DATABASE_USERNAME, DATABASE_PASSWORD);
    $this->_db->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, 1);
    $this->_pageVars['title_major'] = H_TITLE;
    $this->_pageVars['title_minor'] = H_SUBTITLE;
    $this->_pageVars['template'] = 'default';
    if( is_file(SYSTEMROOT.H_SETTINGS_PATH.'/'.$codex.'/error_codes.ini') ){
      $this->_errorCodes = parse_ini_file(SYSTEMROOT.H_SETTINGS_PATH.'/'.$codex.'/error_codes.ini');
    }
	connect::$_codex = &$this;
	if( DEBUG === true ){
      //expand into debug class
	}
  }
  
  public function __clone(){
    errorHandler::report(ERR_FATAL, 'Do not clone the codex, if youhave 2 projects spin up a second codex.');
  }
  
  function db(){
  	return $this->_db;
  }
  
  function initQuery($query){
    $this->_queries[] = $this->_db->prepare($query);
    end($this->_queries);
    return current($this->_queries);
  }
  
  function name(){
    return $this->_projectName;
  }

  function __destruct(){
    if( DEBUG === true ){
		$_SESSION['debug'] = '';
        $_SESSION['debug']['errors']['major'] = array();
        $_SESSION['debug']['errors']['minor'] = array();
        $_SESSION['debug']['errors']['public'] = array();

		$_SESSION['debug']['request_start'] = $this->debugVars['request_start'];
		
		$_SESSION['debug']['request_end'] = microtime(true);
		$_SESSION['debug']['request_uri'] = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : 'bash';

        $_SESSION['debug']['get_vars'] = $_GET;
        $_SESSION['debug']['post_vars'] = $_POST;

		$_SESSION['debug']['libs'] = '';
		foreach($this->_queries as $key => $val){
		  $_SESSION['debug']['queries'][] = $val->queryString;
		}
		foreach($this->debugVars['libs'] as $key => $val){
		  $_SESSION['debug']['libs'][$val] = array();
#		  foreach( $this->$val->fetchAll() as $k => $v ){
#		    $_SESSION['debug']['libs'][$val][$k] = serialize($v->attr());
#		  }
		}

      foreach($this->_errors as $key => $val){
        //errors
        $_SESSION['debug']['errors']['major'][$key] = $val->__toString();
      }

      foreach(errorHandler::$_errors as $key => $val){
        // minor events
        $_SESSION['debug']['errors']['minor'][$key] = $val->__toString();
      }

      foreach(errorHandler::$_public as $key => $val){
        //public events
        $_SESSION['debug']['errors']['public'][$key] = $val->__toString();
      }

	}
  }

  private function initDebugVars(){
    $this->debugVars['request_start'] = microtime(true);
	$this->debugVars['queries'] = array();
    $this->debugVars['libs'] = array();
  }

  public function __get($name){
    try{
      if( !isset($this->$name) ){
        $this->$name = new chapter($name);        
        $this->$name->init();
	  }
  	  return $this->$name;
	}catch(eFatal $e){
      // invalid class name
	}catch(eWarning $e){
	  // invalid characters
	}
  }
  
  public function __call($name, $arguments){
    $this->__get($name);
    try{
	  $retVal = $this->$name->load($arguments);
	}catch(eFatal $e){
      return false;
	}
	return $retVal;
  }

  function attr( $key=false, $val=NULL ){ // setting not supported until abstraction
    if( $key === false ){
	  return $this->_pageVars;
	}else{
	  if( isset($this->_pageVars[$key]) ){
	    return $this->_pageVars[$key];
      }else{
	    return false;
	  }
	}
  }
  
  public function _authUser( $user ){
    $this->_authUser = $user;
    $_SESSION['_authUser'] = $this->_authUser->attr();
	$_SESSION['_authUser']['password'] = $user->getPassword();
  }

}

?>