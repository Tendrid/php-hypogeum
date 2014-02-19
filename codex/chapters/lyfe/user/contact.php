<? #V A1.5.1
class user_contact extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'contact';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

	$this->_settings['searchVars']['integer'][] = 'user_id_er';
	$this->_settings['searchVars']['string'][] = 'displayname';

    $this->addCol( 'user', 'user_id', false, (COL_UNIQUE_LOOKUP) );
    $this->addCol( 'user', 'displayname', REGEX_SPECIAL_NAME, (COL_REQUIRED) );
    $this->addCol( 'user', 'firstname', REGEX_NAME, (COL_REQUIRED) );
    $this->addCol( 'user', 'lastname', REGEX_NAME, (COL_REQUIRED) );
    $this->addCol( 'user', 'firstin', false, (false) );
    $this->addCol( 'user', 'lastin', false, (false) );
    $this->addCol( 'user', 'zip', REGEX_INT, (COL_REQUIRED) );
    $this->addCol( 'user', 'avatar', false, (false) );

    $this->addCol( 'image', 'image_id', false, (false) );
    $this->addCol( 'image', 'filename', false, (false) );
    $this->addCol( 'image', 'title', false, (false) );
    $this->addCol( 'image', 'dt', false, (false) );
    $this->addCol( 'image', 'accesslvl', false, (false) );
    $this->addCol( 'image', 'viewcount', false, (false) );
    $this->addCol( 'image', 'hyve_id', false, (false) );
    $this->addCol( 'image', 'serverloc', false, (false) );

    $this->addCol( 'contact', 'contact_id', false, (COL_PRIMARY) );
    $this->addCol( 'contact', 'user_id_er', false, (false) );
    $this->addCol( 'contact', 'contact_dt', false, (false) );
    $this->addCol( 'contact', 'follow', false, (false) );

    $this->_settings['baseQuery']= "SELECT * FROM `{$this->_settings['table']}`
									LEFT JOIN user ON contact.user_id_ie=user.user_id
									LEFT JOIN image ON user.avatar=image.image_id
									WHERE (1=1)
									LIMIT 0,100";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }

  function save(){
    //stub override
  }

}
?>