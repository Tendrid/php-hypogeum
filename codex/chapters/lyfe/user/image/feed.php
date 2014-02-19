<? #V A1.5.1
class user_image_feed extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'user';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	$this->_settings['db_id'][] = 'image_id';
	#$this->_settings['searchVars']['id'][] = 'id';
	$this->_settings['searchVars']['integer'][] = 'user_id_er';
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
                            'hyve_id',
                            'serverloc') );

    $this->buildCols( 'contact',
	                  array('contact_id',
                            'contact_dt',
							'follow') );

	$this->_settings['db_uid']['contact'] = array('contact_id');

	//select latest X ( images and owner ) where userid in (select all of my contacts where follow =1)

	//select latest x images AND latest x comments
	
	/*	
SELECT * FROM image, user
WHERE image.user_id = user.user_id
AND user.user_id in
(SELECT user_id_ie as user_id from contact where user_id_er=1 AND follow=1)
order by dt desc limit 0,10
	*/
    $this->_settings['baseQuery']= "SELECT * FROM image, user
									WHERE image.owner_id = user.user_id
									AND user.user_id in
									(SELECT user_id_ie as user_id from contact where (1=1) AND follow=1)
									ORDER BY dt DESC LIMIT 0,5";

/*
    $this->_settings['baseQuery']= "SELECT * FROM {$this->_settings['table']}
									LEFT JOIN `contact` ON (user.user_id=contact.user_id_ie)
									LEFT JOIN `image` ON (user.avatar=image.image_id)
									WHERE user.user_id in(SELECT user_id_ie as user_id from contact where (1=1))
									LIMIT 0,100";
*/
    #$this->setQueryOrder('-dt');
    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }

  function save(){
    //stub override
  }

}
?>