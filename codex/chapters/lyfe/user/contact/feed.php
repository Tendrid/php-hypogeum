<? #V A1.5.1
class user_contact_feed extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'user';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	$this->_settings['db_id'][] = 'contact_id';
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

    $this->buildCols( 'contact',
	                  array('contact_id',
                            'user_id_er',
                            'user_id_ie',
                            'contact_dt',
							'follow') );

    $this->buildCols( 'misc', array('ie_dn','ie_id') );

	$this->_settings['db_uid']['contact'] = array('contact_id');

	//select latest X ( images and owner ) where userid in (select all of my contacts where follow =1)

	//select latest x images AND latest x comments
	
	/*
SELECT * FROM contact, user
WHERE contact.user_id_er = user.user_id
AND user.user_id in
(SELECT user_id_ie as user_id from contact where user_id_er=1 AND follow=1)
order by contact_dt desc limit 0,10
	*/
	
/*
SELECT *, ie.displayname as ie_dn FROM contact, user as er, user as ie
WHERE contact.user_id_er = er.user_id
AND contact.user_id_ie = ie.user_id
AND er.user_id in
(SELECT user_id_ie as user_id from contact where user_id_er=1 AND follow=1)
order by contact_dt desc limit 0,10
*/
    $this->_settings['baseQuery']= "SELECT contact.*, er.*, ie.displayname as ie_dn, ie.user_id as ie_id FROM contact, user as er, user as ie
									WHERE contact.user_id_er = er.user_id
									AND contact.user_id_ie = ie.user_id
									AND er.user_id in
									(SELECT user_id_ie as user_id from contact where (1=1) AND follow=1)
									order by contact_dt desc LIMIT 0,5";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }

  function save(){
    //stub override
  }

}
?>