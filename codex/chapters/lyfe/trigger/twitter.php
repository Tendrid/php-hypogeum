<? #V A1.5.1
class trigger_twitter extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'trigger_twitter';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	$this->_settings['db_id'][] = 'twitter_id';
	$this->_settings['searchVars']['integer'][] = 'twitter_id';
	$this->_settings['searchVars']['string'][] = 'screenname';

    $this->buildCols( 'trigger_twitter',
	                  array('twitter_id',
                            'user_id',
                            'hyve_id',
                            'screenname',
                            #'username',
                            'password',
                            'image_url') );
							
    $this->requiredCols( array('screenname',
                               'password') );
							   
    $this->secureCols( array('user_id' => REGEX_INT,
                             'hyve_id' => REGEX_INT,
                             'screenname' => REGEX_NAME,
                             #'username' => REGEX_EMAIL,
                             'password' => REGEX_TEXT) );


	$this->_settings['db_uid']['trigger_twitter'] = array('trigger_id');

    $this->_settings['baseQuery']= "SELECT * FROM {$this->_settings['table']} WHERE (1=1) LIMIT 0,100";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
  
  function syncTwitterDetails(){
    $twit = json_decode($this->wget('http://twitter.com/account/verify_credentials.json', $this->attr('screenname'), $this->attr('password')), true);
	if ($twit === NULL){ return false; }
	$this->attr('screenname',$twit['name']);
	$this->attr('image_url',$twit['profile_image_url']);
	return true;
  }
  
}
?>