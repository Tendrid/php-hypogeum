<?php

/**
 * :: Hypogeum development space ::
 *    TODO - move attr() to a data class that can be used in codex, chapter, or base
 *
 * :: Future links ::
 *    lyfe.net/Tendrid
 *    lyfe.net/i
 *    lyfe.net/i/12413		( or other tinyurl id)
 *    lyfe.net/Tendrid/i/12413
 *    lyfe.net/h/14321
 *
 * :: maybe? ::
 *    hyve.lyfe.net/a2
 *    people.lyfe.net/Tendrid 
 */

error_reporting(E_ALL);
ini_set('display_errors',16384);
$minVer = '5.3.0';
if( version_compare(PHP_VERSION, $minVer) < 1 ){ exit('Site Codex requires >= PHP '.$minVer); }

// globals and abstracts
if( !defined('DATABASE_NAME') ){ require('inc/settings.php'); }
date_default_timezone_set(SERVER_TIMEZONE);
require('inc/_error.php');
require('inc/_connect.php');
#require('inc/layout.php');

// fire up the session;
ob_start("ob_gzhandler");
session_start();

function loadCodex($codex){
  $codex = preg_replace('/[^a-zA-Z0-9_\/]/','',$codex);
  include(SYSTEMROOT.H_SETTINGS_PATH.'/'.$codex.'/settings.php');
  require(SYSTEMROOT.H_PATH.'/inc/layout.php');
  include(SYSTEMROOT.H_SETTINGS_PATH.'/'.$codex.'/layout.php');

  require('codex.php');
  $c = new codex($codex);
  // user login
  if( isset( $_SESSION['_authUser'] ) ){
    $user = $c->user($_SESSION['_authUser']['user_id']);
	if( $user == false ){
	  session_destroy();
	  exit('Something went seriously wrong. please refresh your browser');
	}
	if( isset($_SESSION['_authUser']['password']) ){
      $user->login($_SESSION['_authUser']['personalemail'],$_SESSION['_authUser']['password']);
    }else{
	  session_destroy();
	  exit('Something went seriously wrong. please refresh your browser');
	}
  }
  if( isset($_GET['preload']) ){
    #$c->_pageVars['core_object'] = $c->$_GET['page'](DecodeBase64($_GET['id']));
  }
  return $c;
}
// if we have defined a default codex in our settings, load that now.
if( defined('H_LOAD_CODEX') ){
	$c = loadCodex(H_LOAD_CODEX);
}

//page
class page{
  static function getDefault(){
    global $c;
    if($c->_authUser !== false){
      return 'myprofile';
    }else{
      return 'home';
    }
  }

  static function resolve($_curPageName=false){
    global $c;
    if(is_null($_curPageName) || $_curPageName===false){
      $_curPageName = self::getDefault();
	}else{
  	  $_curPageName = str_replace('_','/',$_curPageName); // make file system safe
	  $_curPageName = preg_replace('/[^a-zA-Z0-9_\/]/','',$_curPageName);
	  if(is_file('.'.PAGEROOT.'/'.$c->_pageVars['template'].'/'.$_curPageName.'.php')){
	    return '.'.PAGEROOT.'/'.$c->_pageVars['template'].'/'.$_curPageName.'.php';
	  }else{
	    return false;
	  }
    }
  }  
  
  static function get($_curPageName=false){
    global $c;
    if(is_null($_curPageName) || $_curPageName===false){
      $_curPageName = self::getDefault();
    }
    $g = self::resolve($_curPageName);
	if($g !== false){
	  include($g);
  	  $_curPageName = str_replace('_','/',$_curPageName); // make file system safe
	  $_curPageName = preg_replace('/[^a-zA-Z0-9_\/]/','',$_curPageName);
      if(is_file('.'.JSROOT.PAGEROOT.'/'.$c->_pageVars['template'].'/'.$_curPageName.'.php')){
        echo '<script type="text/javascript">'."\n";
	    include('.'.JSROOT.PAGEROOT.'/'.$c->_pageVars['template'].'/'.$_curPageName.'.php');
        echo '</script>';
      }
      if(is_file('.'.JSROOT.PAGEROOT.'/'.$c->_pageVars['template'].'/'.$_curPageName.'.js')){
        echo '<script type="text/javascript">'."\n";
	    include('.'.JSROOT.PAGEROOT.'/'.$c->_pageVars['template'].'/'.$_curPageName.'.js');
        echo '</script>';
      }
	}else{
	  return false;
	}
  }
}

function verbose(){
  global $c;
  echo '<pre>';
  echo '<hr><h1>Errors (thrown):</h1>';
  foreach($c->_errors as $key => $val){
    echo $val."\n";
  }

  echo '<hr><h1>Minor Events (no halt):</h1>';
  foreach(errorHandler::$_errors as $key => $val){
    echo $val."\n";
  }

  echo '<hr><h1>Public Events:</h1>';
  foreach(errorHandler::$_public as $key => $val){
    echo $val."\n";
  }
  echo '</pre>';
}

function EncodeBase64($value){
  if( $value == 0 ){
    return "0";
  }
  
  // this is our conversation table of 64 values to printable ASCII
  $table = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_";

  $buffer = "";

  // loop over our data, converting
  while( $value > 0 ){
    // determine how much we have left over in the low 6 bits
    $buffer .= $table[ $value&63 ];

    // divide by 64 for the next round
    $value >>= 6;
  }

  // our data is reversed, flip the order
  $buffer = strrev( $buffer );
  return $buffer;
}


function DecodeBase64( $value ){

  $buffer = str_split($value);
  $bufferLen = strlen($value);

  // working value
  $retVal = 0;
  
  // process until we hit the end of the string
  $strLen = strlen($value);

  for($index = 0; $index < $strLen; ++$index ){
    // extract the next character
    $c = $buffer[$index];
    // convert it to a value 0-63
    $cVal = 0;
    if( $c >= '0' && $c <= '9' ){
      // convert from the ASCII to a decimal (offset from 0)
      $cVal = ord($c) - ord('0');
    }else if($c >= 'A' && $c <= 'Z' ){
      // don't forget to offset for 0-9
      $cVal = ord($c) - ord('A') + 10;
    }else if( $c >= 'a' && $c <= 'z' ){
      // don't forget to offset for 0-9 and A-Z
      $cVal = (ord($c) - ord('a')) + 10 + 26;
    }else if( $c == '-' ){
      $cVal = 62;
    }else if( $c == '_' ){
      $cVal = 63;
    }
	// fold it in (this is equivalent to: retVal = ( retVal * 64 ) + cVal
    $retVal <<= 6;   // multiply the existing by 64
    $retVal |= $cVal; // add in the value
  }
  return $retVal;
}

function mobileCheck(){

$isMobile = false;
$isBot = false;

$op = strtolower(@$_SERVER['HTTP_X_OPERAMINI_PHONE']);
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
$ac = strtolower($_SERVER['HTTP_ACCEPT']);
$ip = $_SERVER['REMOTE_ADDR'];

$isMobile = strpos($ac, 'application/vnd.wap.xhtml+xml') !== false
        || $op != ''
        || strpos($ua, 'sony') !== false 
        || strpos($ua, 'symbian') !== false 
        || strpos($ua, 'nokia') !== false 
        || strpos($ua, 'samsung') !== false 
        || strpos($ua, 'mobile') !== false
        || strpos($ua, 'windows ce') !== false
        || strpos($ua, 'epoc') !== false
        || strpos($ua, 'opera mini') !== false
        || strpos($ua, 'nitro') !== false
        || strpos($ua, 'j2me') !== false
        || strpos($ua, 'midp-') !== false
        || strpos($ua, 'cldc-') !== false
        || strpos($ua, 'netfront') !== false
        || strpos($ua, 'mot') !== false
        || strpos($ua, 'up.browser') !== false
        || strpos($ua, 'up.link') !== false
        || strpos($ua, 'audiovox') !== false
        || strpos($ua, 'blackberry') !== false
        || strpos($ua, 'ericsson,') !== false
        || strpos($ua, 'panasonic') !== false
        || strpos($ua, 'philips') !== false
        || strpos($ua, 'sanyo') !== false
        || strpos($ua, 'sharp') !== false
        || strpos($ua, 'sie-') !== false
        || strpos($ua, 'portalmmm') !== false
        || strpos($ua, 'blazer') !== false
        || strpos($ua, 'avantgo') !== false
        || strpos($ua, 'danger') !== false
        || strpos($ua, 'palm') !== false
        || strpos($ua, 'series60') !== false
        || strpos($ua, 'palmsource') !== false
        || strpos($ua, 'pocketpc') !== false
        || strpos($ua, 'smartphone') !== false
        || strpos($ua, 'rover') !== false
        || strpos($ua, 'ipaq') !== false
        || strpos($ua, 'au-mic,') !== false
        || strpos($ua, 'alcatel') !== false
        || strpos($ua, 'ericy') !== false
        || strpos($ua, 'up.link') !== false
        || strpos($ua, 'vodafone/') !== false
        || strpos($ua, 'wap1.') !== false
        || strpos($ua, 'wap2.') !== false;

        $isBot =  $ip == '66.249.65.39' 
        || strpos($ua, 'googlebot') !== false 
        || strpos($ua, 'mediapartners') !== false 
        || strpos($ua, 'yahooysmcm') !== false 
        || strpos($ua, 'baiduspider') !== false
        || strpos($ua, 'msnbot') !== false
        || strpos($ua, 'slurp') !== false
        || strpos($ua, 'ask') !== false
        || strpos($ua, 'teoma') !== false
        || strpos($ua, 'spider') !== false 
        || strpos($ua, 'heritrix') !== false 
        || strpos($ua, 'attentio') !== false 
        || strpos($ua, 'twiceler') !== false 
        || strpos($ua, 'irlbot') !== false 
        || strpos($ua, 'fast crawler') !== false                        
        || strpos($ua, 'fastmobilecrawl') !== false 
        || strpos($ua, 'jumpbot') !== false
        || strpos($ua, 'googlebot-mobile') !== false
        || strpos($ua, 'yahooseeker') !== false
        || strpos($ua, 'motionbot') !== false
        || strpos($ua, 'mediobot') !== false
        || strpos($ua, 'chtml generic') !== false
        || strpos($ua, 'nokia6230i/. fast crawler') !== false;

	return $isMobile;
}

?>