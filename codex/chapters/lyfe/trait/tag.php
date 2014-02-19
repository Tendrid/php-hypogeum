<? #V A1.5.1
class trait_tag extends base{

  protected $_query;
  
  function build( $id=false ){
    $this->_settings['table'] = 'trait_tag';
	$this->_settings['class'] = __CLASS__;
	$this->setClass(__CLASS__);

    //this should be rolled into a function like buildCols
	$this->_settings['db_id'][] = 'trait_tag_id';
	$this->_settings['searchVars']['integer'][] = 'trait_tag_id';
	$this->_settings['searchVars']['string'][] = 'trait_tag_text';

    $this->buildCols( 'trait_tag',
	                  array('trait_tag_id',
                            'trait_tag_text') );

	$this->_settings['db_uid']['trait_tag'] = array('trait_tag_id');

	$this->_settings['baseQuery']= "SELECT * FROM `{$this->_settings['table']}` where (1=1) LIMIT 0,30";

    if( $this->loadClass( $id ) === false){
	  return false;
	}
  }
  
}
?>