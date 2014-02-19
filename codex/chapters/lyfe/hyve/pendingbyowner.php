<? #V A1.5.1
class hyve_pendingbyowner extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'hyve_user_request';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	$this->_settings['searchVars']['integer'][] = 'user_id';
	$this->_settings['searchVars']['string'][] = 'hyve_id';

    $this->addCol( 'hyve_user_request', 'request_id', REGEX_INT_ID, (COL_UNIQUE|COL_UNIQUE_LOOKUP|COL_PRIMARY) );
    $this->addCol( 'hyve_user_request', 'hyve_id', false, (false) );
    $this->addCol( 'hyve_user_request', 'user_id', false, (false) );
    $this->addCol( 'hyve_user_request', 'permissions', false, (false) );

    $this->addCol( 'hyve', 'hyve_id', false, (false) );
    $this->addCol( 'hyve', 'owner_id', false, (false) );
    $this->addCol( 'hyve', 'securityname', false, (false) );
    $this->addCol( 'hyve', 'displayname', false, (false) );
    $this->addCol( 'hyve', 'description', false, (false) );
    $this->addCol( 'hyve', 'public', false, (false) );

    $this->addCol( 'user', 'user_id', false, (false) );
    $this->addCol( 'user', 'displayname', false, (false) );
    $this->addCol( 'user', 'firstname', false, (false) );
    $this->addCol( 'user', 'lastname', false, (false) );
    $this->addCol( 'user', 'firstin', false, (false) );
    $this->addCol( 'user', 'lastin', false, (false) );
    $this->addCol( 'user', 'zip', false, (false) );
    $this->addCol( 'user', 'avatar', false, (false) );

    $this->addCol( 'image', 'image_id', false, (false) );
    $this->addCol( 'image', 'user_id', false, (false) );
    $this->addCol( 'image', 'filename', false, (false) );
    $this->addCol( 'image', 'title', false, (false) );
    $this->addCol( 'image', 'dt', false, (false) );
    $this->addCol( 'image', 'accesslvl', false, (false) );
    $this->addCol( 'image', 'viewcount', false, (false) );
    $this->addCol( 'image', 'hyve_id', false, (false) );
    $this->addCol( 'image', 'serverloc', false, (false) );

    #$this->_settings['baseQuery'] = "SELECT * FROM `hyve_user_request` JOIN `hyve` on hyve.hyve_id = hyve_user_request.hyve_id WHERE hyve_user_request.hyve_id in (select hyve_id from hyve where (1=1))";

    $this->_settings['baseQuery'] = "SELECT * FROM `hyve_user_request` JOIN `hyve` on hyve.hyve_id = hyve_user_request.hyve_id JOIN `user` on user.user_id = hyve_user_request.user_id LEFT JOIN `image` on image.image_id = user.avatar WHERE hyve_user_request.hyve_id in (select hyve_id from hyve WHERE (1=1) LIMIT 0,30)";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
  
}
?>