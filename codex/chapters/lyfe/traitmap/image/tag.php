<? #V A1.5.1
class traitmap_image_tag extends base{

  protected $_query;
  
  function build( $id=false ){
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

	// Still need to remove these.
    $this->_settings['table'] = 'traitmap_image_tag';
	$this->_settings['searchVars']['integer'][] = 'trait_tag_id';
	$this->_settings['searchVars']['string'][] = 'trait_tag_text';

    $this->addCol( 'trait_tag',				'trait_tag_id',		REGEX_INT_ID,	(COL_UNIQUE|COL_INDEXED) );
    $this->addCol( 'trait_tag',				'trait_tag_text',	REGEX_TEXT,		(COL_REQUIRED) );
    $this->addCol( 'traitmap_image_tag',	'trait_id',			REGEX_INT_ID,	(COL_UNIQUE_LOOKUP|COL_PRIMARY|COL_INDEXED) );
    $this->addCol( 'traitmap_image_tag',	'image_id',			REGEX_INT_ID,	(COL_REQUIRED|COL_INDEXED|COL_UNIQUE) );
    $this->addCol( 'traitmap_image_tag',	'trait_tag_id',		REGEX_INT_ID,	(COL_INDEXED|COL_UNIQUE) );
    $this->addCol( 'traitmap_image_tag',	'creator_id',		REGEX_INT_ID,	(COL_REQUIRED|COL_INDEXED) );

	$this->_settings['baseQuery']= "SELECT * FROM traitmap_image_tag
									LEFT JOIN `trait_tag` ON (traitmap_image_tag.trait_tag_id = trait_tag.trait_tag_id)
									WHERE (1=1) LIMIT 0,100";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
  
}
?>