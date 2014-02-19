<? #V A1.5.1
class hyveuser extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'user';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	$this->_settings['db_id'][] = 'user_id';
	$this->_settings['searchVars']['integer'][] = 'hyve_id';
	$this->_settings['searchVars']['string'][] = 'displayname';

    $this->buildCols( 'user',
	                  array('user_id',
                            'displayname',
                            'firstname',
                            'lastname',
							'firstin',
							'lastin',
							'zip',
							'avatar') );

    $this->buildCols( 'image',
	                  array('image_id',
                            'owner_id',
                            'filename',
                            'title',
                            'dt',
                            'accesslvl',
                            'viewcount',
                            'serverloc') );

    $this->buildCols( 'hyve_user_map',
	                  array('hyve_id',
                            'user_id',
                            'permissions') );


	$this->_settings['db_uid']['user'] = array('user_id');
    $this->_settings['baseQuery']= "SELECT * FROM `user` LEFT JOIN `image` on user.avatar = image.image_id WHERE user.user_id IN (SELECT `user_id` FROM `hyve_user_map` WHERE (1=1) ) LIMIT 0,30";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
  
}
?>