<?php
define('FACEBOOK_PERMISSION_offline_access'	, 1);
define('FACEBOOK_PERMISSION_publish_stream'	, 2);
define('FACEBOOK_PERMISSION_user_photos'	, 4);
define('FACEBOOK_PERMISSION_user_videos'	, 8);
define('FACEBOOK_PERMISSION_email'			, 16);


class user_auth_facebook extends base{

  protected $_query;
  protected $connection = false;
  
  function build( $id=false ){
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    $this->_settings['table'] = 'auth_facebook';
	$this->_settings['searchVars']['integer'][] = 'user_id';
	$this->_settings['searchVars']['string'][] = 'screen_name';

    $this->addCol( 'auth_facebook', 'auth_facebook_id', REGEX_INT_ID, (COL_UNIQUE|COL_UNIQUE_LOOKUP|COL_PRIMARY) );
    $this->addCol( 'auth_facebook', 'facebook_id', REGEX_INT_ID, (false) );
    $this->addCol( 'auth_facebook', 'owner_id', REGEX_INT_ID, (false) );
    $this->addCol( 'auth_facebook', 'user_id', REGEX_INT_ID, (false) );
    $this->addCol( 'auth_facebook', 'hyve_id', REGEX_INT_ID, (false) );	
    $this->addCol( 'auth_facebook', 'authKey', REGEX_TEXT, (COL_REQUIRED) );
    $this->addCol( 'auth_facebook', 'screen_name', REGEX_TEXT, (false) );
    $this->addCol( 'auth_facebook', 'permissions', REGEX_INT, (false) );
    $this->addCol( 'auth_facebook', 'album', REGEX_INT, (false) );

    $this->_settings['baseQuery']= "SELECT * FROM {$this->_settings['table']} WHERE (1=1) LIMIT 0,100";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }

  public function userLogin(){
	#$this->permCheck();
    return connect::$_codex->user(intval($this->attr('user_id')))->login(false,false,$this);
  }

  public function switchOwner($email, $password){
    if( connect::$_codex->_authUser != false ){
      $oldMe = clone connect::$_codex->_authUser;
      $user = connect::$_codex->user->search( $email, false, 'personalemail' );
      if( gettype($user) == 'array' && isset($user[0]) ){
        if( $user[0]->login( $email, $password ) === true ){
          # change owner_id
          $this->attr( 'owner_id', $user[0]->attr('user_id') );
          $this->attr( 'user_id', $user[0]->attr('user_id') );
          $this->save();
          # delete old user (delete mail hook, built in to user delete)
          #$oldMe->delete();
          return true;
        }
      }
    }else{
	  // must login first
	}
    return false;
  }

  public function getAuth($code){
      return $this->getUserData();
  }

  public function getUserData($out=false){
    #$out = $this->request();
	#var_dump($this->connection->getAuthKey());die();
	#var_dump($out);die();
	if( $out != false){
      $this->attr('facebook_id',$out['id']);
      $this->attr('screen_name',str_replace('http://www.facebook.com/','',$out['link']));
      $this->attr('authKey', $this->connection->getAuthKey());
	}else{
      $out = $this->request();
	}
	#var_dump($out);
    return array('facebook_id' => $out['id'],
                 'displayname' => str_replace('http://www.facebook.com/','',$out['link']),
                 'securityname' => str_replace('http://www.facebook.com/','',$out['link']),
                 'firstname' => $out['first_name'],
                 'lastname' => $out['last_name'],
				 'securityflags' => 1000,
                 'authKey' => $this->connection->getAuthKey(),
                 'firstin'=> mktime(),
                 'lastin'=> mktime());
  }

  private function _checkAuth(){
    if($this->connection == false){
      $this->connection = new socialOauth('facebook', SESSION);
	}
	if( $this->connection->getAuthKey() == false){
	  $this->connection->login($this->attr('authKey'));
	}
    #$this->connection->setAuthKey($this->connection->getAuthKey());
  }

  public function auth(){
    // set referer path
    if( !isset($_SESSION['HTTP_REFERER']) ){ $_SESSION['HTTP_REFERER'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'http://lyfe.net'; }

    $so = new socialOauth('facebook', SESSION);
    $this->connection = $so;
    $me = $so->login();

    if( isset($_GET['code']) ){
      $fbUser = $this->getUserData($me);
      if( isset($fbUser['error']) ){
        $error = $fbUser['error'];
        $_SESSION['temp']['fb_error'] = serialize($error);
        header('Location: /?page=auth_facebook&referer='.$_SERVER['HTTP_REFERER']);
      }
      $fbMe = connect::$_codex->user_auth_facebook->search($fbUser['facebook_id'],false,'facebook_id');
      if( $fbMe == array() ){
        $fbMe = $this;
        $me = $fbMe->addUser();
        $fbMe->save();
      }else{
        if( count($fbMe == 1) ){
          $fbMe = $fbMe[0];
    	}else{
          die('error'); // hack attempt!
    	}
      }
      $fbMe->userLogin();
      $referer = isset($_SESSION['HTTP_REFERER']) ? $_SESSION['HTTP_REFERER'] : 'http://lyfe.net';
      unset($_SESSION['HTTP_REFERER']);
      header('Location: '.$referer);
    }else{
      exit( json_encode(array('error'=>'/* oAuth Request Token required */')) );
    }
  }


  public function addUser(){
  #var_dump($this->getUserData());die();
    $me = connect::$_codex->user->create($this->getUserData());
	$me->save();
    if( errorHandler::isThrown(1000) ){
      $mask = $me->attr('displayname').'_'.mktime();
	  $me->attr('displayname',$mask);
	  $me->attr('securityname',$mask);
	  $me->save();
      $_SESSION['temp']['fb_error'] = serialize(array(1000));
    }
	$this->attr('owner_id',$me->attr('user_id'));
	$this->attr('user_id',$me->attr('user_id'));
	return $me;
  }

  public function request($item='me', $params=array() ){
    $this->_checkAuth();
	return $this->connection->request($item, $params);
  }

  private function _hasError($data){
    if( is_object($data) && isset($data->error) ){
	  return true;
	}else{
	  return false;
	}
  }

  public function permCheck(){
    if( $this->attr('owner_id') > 0 ){
      $p = ($this->attr('permissions') == -1 ) ? 0 : $this->attr('permissions');
      $required = array('offline_access','publish_stream');
	  $perms = '';
      foreach( $required as $key => $val ){
        if( !($p & constant('FACEBOOK_PERMISSION_'.$val)) ){
          if( $perms != '' ){ $perms.=','; }
		  $perms.=$val;
		  $p+=constant('FACEBOOK_PERMISSION_'.$val);
        }
      }
	  if( $perms != '' ){
	    $this->attr('permissions',$p);
		#var_dump($perms);
		#var_dump($this);
		$this->save();
		#die();
        header( 'Location: https://graph.facebook.com/oauth/authorize?client_id='.FACEBOOK_CONSUMER_KEY.'&scope='.htmlentities($perms).'&redirect_uri=http://dev.lyfe.net/rpc/user/auth/facebook/create.php?referer='.$_SERVER['HTTP_REFERER'] );
	    exit();
	  }
	}
  }
/*
  private function _request($url, $params=false){
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL            => $url,
      CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_AUTOREFERER    => true,
	  CURLOPT_FOLLOWLOCATION => true
    ));

	if( $params!= false ){
      $params['access_token'] = $this->attr('oauth_token_secret');
	  #$params='access_token='.$this->attr('oauth_token_secret').'&message=test_message';
      curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
	  curl_setopt($curl, CURLOPT_POST, true);
	}
    $result = curl_exec($curl);
    curl_close($curl);
    return $result; 
  }  
*/
}

?>