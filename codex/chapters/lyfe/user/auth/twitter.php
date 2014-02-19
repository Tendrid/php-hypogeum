<?php
require( SYSTEMROOT.H_LIB_PATH.'/twitterOAuth/_oAuth.php' );

class user_auth_twitter extends base{

  protected $_query;
  public $interface;
  protected $connection = false;

  function build( $id=false ){
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    $this->_settings['table'] = 'auth_twitter';
    $this->_settings['searchVars']['integer'][] = 'user_id';
    $this->_settings['searchVars']['string'][] = 'screen_name';

    $this->addCol( 'auth_twitter', 'auth_twitter_id', REGEX_INT_ID, (COL_UNIQUE|COL_UNIQUE_LOOKUP|COL_PRIMARY) );
    $this->addCol( 'auth_twitter', 'twitter_id', REGEX_INT_ID, (false) );
    $this->addCol( 'auth_twitter', 'owner_id', REGEX_INT_ID, (false) );
    $this->addCol( 'auth_twitter', 'user_id', REGEX_INT_ID, (false) );
    $this->addCol( 'auth_twitter', 'hyve_id', REGEX_INT_ID, (false) );	
    $this->addCol( 'auth_twitter', 'authKey', false, (COL_REQUIRED) );
    $this->addCol( 'auth_twitter', 'screen_name', REGEX_TEXT, (false) );

    $this->_settings['baseQuery']= "SELECT * FROM {$this->_settings['table']} WHERE (1=1) LIMIT 0,100";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }

  public function userLogin(){
    return connect::$_codex->user(intval($this->attr('user_id')))->login(false,false,$this);
  }

  public function requestToken( $callback = TWITTER_OAUTH_CALLBACK ){
    unset($_SESSION['temp']['oauth_token_secret']);
    $_tc = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
	$request_token = $_tc->getRequestToken($callback);
	$_SESSION['temp']['oauth_token_secret'] = $request_token['oauth_token_secret'];
    try{
      if( $_tc->http_code != 200 ){ errorHandler::report(ERR_FATAL,'It looks like the Twitter API is currently down.'); }
    }catch(eFatal $e){
      return false;
    }
    return(array('redirect'=>'https://twitter.com/oauth/authenticate?oauth_token='.$request_token['oauth_token']));
  }

  private function _checkAuth(){
    if($this->connection == false){
      $this->connection = new socialOauth('twitter', SESSION);
	}
	if( $this->connection->getAuthKey() == false){
      if( $this->attr('authKey') == -1 ){
        $this->setUserData($this->connection->login());
	  }else{
        $this->setUserData($this->connection->login(unserialize($this->attr('authKey'))));
	  }
	}
  }

  public function auth(){
    // set referer path
    if( !isset($_SESSION['HTTP_REFERER']) ){ $_SESSION['HTTP_REFERER'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'http://lyfe.net'; }

	$twUser = $this->_checkAuth();
    $twMe = connect::$_codex->user_auth_twitter->search($this->attr('twitter_id'),false,'twitter_id');
    if( $twMe == array() ){
      $twMe = $this;
      $me = $twMe->addUser();
      $twMe->save();
    }else{
      if( count($twMe == 1) ){
        $twMe = $twMe[0];
      }else{
        die('error'); // hack attempt!
      }
    }
    $twMe->userLogin();

    // we're done.  lets go home.
    $referer = isset($_SESSION['HTTP_REFERER']) ? $_SESSION['HTTP_REFERER'] : 'http://lyfe.net';
    unset($_SESSION['HTTP_REFERER']);
    header('Location: '.$referer);
  }

  public function addUser(){
    $userData = array( 'displayname'   =>$this->attr('screen_name'),
                       'securityname'  =>$this->attr('screen_name'),
					   'securityflags' =>1000,
                       'authKey'       => serialize($this->connection->getAuthKey()),
                       'firstin'       =>mktime(),
                       'lastin'        =>mktime() );
    $me = connect::$_codex->user->create($userData);
	$me->save();
    if( errorHandler::isThrown(1000) ){
      $mask = $me->attr('displayname').'_'.mktime();
	  $me->attr('displayname',$mask);
	  $me->attr('securityname',$mask);
	  $me->save();
      $_SESSION['temp']['tw_error'] = serialize(array(1000));
    }
	$this->attr('owner_id',$me->attr('user_id'));
	$this->attr('user_id',$me->attr('user_id'));
	return $me;
  }

  private function setUserData($out){
    $this->attr('twitter_id',$out['id']);
    $this->attr('screen_name',$out['screen_name']);
    $this->attr('authKey', serialize($this->connection->getAuthKey()));
  }

  public function request($item='account/verify_credentials', $params=array() ){
    $this->_checkAuth();
	return $this->connection->request($item, $params);
  }

}

?>