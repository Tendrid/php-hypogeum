<?php
// $c->user(1)->social(FACEBOOK)->getUpdates();
// $c->user(1)->social(FACEBOOK & TWITTER)->postUpdate($c->comment(421));
// $c->user(1)->social->facebook->postUpdate($c->image(944));


require(SYSTEMROOT.'/lib/facebook-platform/php/facebook.php');

class social_facebook{
  var $appapikey = 'ba3d9974b2cf80fad0bd0217b4ed92fc';
  var $appsecret = '80e9845a0055d8392d0e150bb5e3c50f';
  function __construct(){
    $this->facebook = new Facebook($this->appapikey, $this->appsecret);

  }

  function login($un, $pw){
  
  }

  function search($searchParam){
    $query = "SELECT name, uid, pic_small, affiliations FROM user WHERE name='{$searchParam}'"; 
    $result = $this->facebook->api_client->fql_query($query);
	if( $result == '' ){ $result = false; }
	return $result;
  }

}

class social_twitter{

  function login($un, $pw){
  
  }

  function search($searchParam){
    $host = 'http://twitter.com/users/show.json?screen_name='.$searchParam;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    #curl_setopt($ch, CURLOPT_POST, 0);

    $result = curl_exec($ch);
    // Look at the returned header
    $resultArray = curl_getinfo($ch);
    curl_close($ch);
	
    if($resultArray['http_code'] == "200"){
	  return json_decode($result);
    } else {
	  return false;
    }
  }
 
}
?>
