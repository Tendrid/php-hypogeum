<?php
class comment extends base{

  protected $_query;
  
  function build( $id=false ){
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    $this->_settings['table'] = 'comment';
	$this->_settings['searchVars']['integer'][] = 'comment_id';
	$this->_settings['searchVars']['string'][] = 'comment';

    $this->addCol( 'comment', 'comment_id', REGEX_INT_ID, (COL_UNIQUE|COL_UNIQUE_LOOKUP|COL_PRIMARY) );
    $this->addCol( 'comment', 'chapter_id', REGEX_FILENAME, (false) );
    $this->addCol( 'comment', 'object_id', REGEX_INT_ID, (COL_REQUIRED) );
    $this->addCol( 'comment', 'user_id_er', REGEX_INT_ID, (COL_REQUIRED) );
    $this->addCol( 'comment', 'user_id_ie', REGEX_INT_ID, (COL_REQUIRED) );
    $this->addCol( 'comment', 'comment_dt', REGEX_INT, (COL_REQUIRED) );
    $this->addCol( 'comment', 'accesslvl', REGEX_INT, (false) );
    $this->addCol( 'comment', 'comment', REGEX_TEXT, (false) );

    $this->addCol( 'user', 'user_id', REGEX_INT_ID, (COL_UNIQUE) );
    $this->addCol( 'user', 'displayname', REGEX_SPECIAL_NAME, (false) );
    $this->addCol( 'user', 'avatar', REGEX_INT, (false) );

    $this->addCol( 'image', 'image_id', REGEX_INT_ID, (COL_UNIQUE) );
    $this->addCol( 'image', 'filename', REGEX_FILENAME, (false) );
    $this->addCol( 'image', 'title', REGEX_TEXT, (false) );
    $this->addCol( 'image', 'dt', REGEX_INT, (false) );
    $this->addCol( 'image', 'serverloc', REGEX_INT, (false) );

    #$this->_settings['baseQuery']= "SELECT * FROM {$this->_settings['table']} WHERE (1=1) LIMIT 0,100";

    $this->_settings['baseQuery']= "SELECT * FROM {$this->_settings['table']}
									JOIN `user` ON (comment.user_id_er = user.user_id)
									LEFT JOIN `image` on (user.avatar = image_id)
									WHERE (1=1) LIMIT 0,100";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }  
}
?>