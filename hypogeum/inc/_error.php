<?php

define('PUB_DEV_NOTE', 'pubDevNote');
define('PUB_NOTICE', 'pubNotice');
define('PUB_WARNING', 'pubWarning');
define('PUB_FATAL', 'pubFatal');

define('ERR_DEV_NOTE', 'eDevNote');
define('ERR_NOTICE', 'eNotice');
define('ERR_WARNING','eWarning');
define('ERR_FATAL','eFatal');

class errorHandler{
  static $_errors = array(); // change this from _errors to something better
  static $_public = array(); // change this to fit with the above change
  static $_errorTypes = array(ERR_DEV_NOTE, ERR_NOTICE, ERR_WARNING, ERR_FATAL);
  static $_publicEvents = array(PUB_DEV_NOTE, PUB_NOTICE, PUB_WARNING, PUB_FATAL);
  
  public function report($type=ERR_FATAL, $message='Unknown exception', $errorId=0 ){
    if( in_array($type,self::$_publicEvents) ){
      self::$_public[] = new $type($message, $errorId);
	}else{
      if( array_search($type, self::$_errorTypes) < ERR_HALT_LEVEL ){
        if( ERR_TRACK == true ){
          self::$_errors[] = new minorEvent($message, $errorId); #array($type, $message, $errorId);
        }
      }else{
	    throw new $type($message, $errorId);
	  }
	}
  }
  
  public function isThrown($id){
    //errorHandler::isThrown(1000);
	foreach( connect::$_codex->_errors as $key => $val ){
	  if( $val->getCode() === $id ){
	    return true;
	  }
	}
	return false;
  }
  
  public function getMessage($id){
    //stub
  }
  
}

/**
 * Exception Interface
 */

interface iException{

  public function getMessage();                 // Exception message
  public function getCode();                    // User-defined Exception code
  public function getFile();                    // Source filename
  public function getLine();                    // Source line
  public function getTrace();                   // An array of the backtrace()
  public function getTraceAsString();           // Formated string of trace
   
  public function __toString();                 // formated string for display
  public function __construct($message = null, $code = 0);
}

/**
 * Exception Base Class
 */

abstract class baseException extends Exception implements iException{
  protected $message = 'Unknown exception';     // Exception message
  private   $string;                            // Unknown
  protected $code    = 0;                       // User-defined exception code
  protected $file;                              // Source filename of exception
  protected $line;                              // Source line of exception
  private   $trace;                             // Trace of code error

  public function __construct($message = null, $code = 0){
	$this->preThrow();
    if( DEBUG_LOG_ERRORS === true ){
      $err = connect::$_codex->_error(false);
      $err->createFromException($this);
    }
    if (!$message){
      throw new $this('Unknown '. get_class($this));
    }
    parent::__construct($message, $code);
	$this->postThrow();
  }
  
  public function __toString(){
    return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
                            . "{$this->getTraceAsString()}\n";
  }
}

/**
 * minor event tracking.  No errors thrown.
 */

class minorEvent extends Exception{
  protected $message = 'Unknown exception';     // Exception message
  private   $string;                            // Unknown
  protected $code    = 0;                       // User-defined exception code
  protected $file;                              // Source filename of exception
  protected $line;                              // Source line of exception
  private   $trace;                             // Trace of code error

  public function __construct($message = null, $code = 0){
	#$this->preThrow();
    #if (!$message){
      #throw new $this('Unknown '. get_class($this));
    #}
    parent::__construct($message, $code);
	#$this->postThrow();
  }
   
  public function __toString(){
    return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
                            . "{$this->getTraceAsString()}\n";
  }
}

/**
 * Public Events
 */

class pubDevNote extends baseException{
  function preThrow(){
    #connect::$_codex->_errors[] = $this;
  }
  
  function postThrow(){
  
  }
}
class pubNotice extends baseException{
  function preThrow(){
    #connect::$_codex->_errors[] = $this;
  }
  
  function postThrow(){
  
  }
}
class pubWarning extends baseException{
  function preThrow(){
    #connect::$_codex->_errors[] = $this;
  }
  
  function postThrow(){
  
  }
}
class pubFatal extends baseException{
  function preThrow(){
    #connect::$_codex->_errors[] = $this;
	#connect::$_codex->_error->create($this);
	#var_dump($this);die();
  }
  
  function postThrow(){

  }
}

/**
 * Private Events
 */

class eDevNote extends baseException{
  function preThrow(){
    connect::$_codex->_errors[] = $this;
  }
  
  function postThrow(){
  
  }
}
class eNotice extends baseException{
  function preThrow(){
    connect::$_codex->_errors[] = $this;
  }
  
  function postThrow(){
  
  }
}
class eWarning extends baseException{
  function preThrow(){
    connect::$_codex->_errors[] = $this;
  }
  
  function postThrow(){
  
  }
}
class eFatal extends baseException{
  function preThrow(){
    connect::$_codex->_errors[] = $this;
  }
  
  function postThrow(){

  }
}

?>