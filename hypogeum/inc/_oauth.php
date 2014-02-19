<?php
function oAuthCheck(){
  if(isset( $_GET['oauth_token']) ){
    var_dump( $_GET['oauth_token']);
    #die('oAuth Detected!');
  }
}

?>