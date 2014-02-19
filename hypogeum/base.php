<?
/** Things to be pushed upstream in to chapter
 * _query
 * _settings
 * _colMap
 */

abstract class base extends layout{

  protected $_update;
  protected $_objType = 'page';
  protected $_classType;
  protected $_pubAttr = array();
  protected $_prvAttr = array();
  protected $_settings = array();
  

  final function __construct( $id=false ){
    $this->_classType = get_class($this);

    $this->build($id);
    if( $this->getSettings('lazyLoad', true) === NULL ){
		$this->lazyLoad(false);
    }
    $this->loadClass($id);

	$this->_postBuild();
	
	// want to build a init method to set things like this
	
  }

  protected function _postBuild(){
    if( method_exists($this,'postBuild') ){
      $id = $this->getId();
	  if( !is_bool($this->attr($id)) && $this->attr($id) !== -1){
	    #if(intval($this->_pubAttr[$id]) == $this->_public[$id]){ $this->_pubAttr[$id] = intval($this->_pubAttr[$id]); }
        $this->postBuild($this->attr($id));
	  }
	}
  }

  public function isClass( $type=false ){
    if( $type === false ){
      return $this->_classType;
    }else{
      return ( $type == $this->_classType ) ? true : false;
    }
  }

  protected function setClass( $type ){
/*	// DEPRECATED //
    if( is_string($type) ){
      $this->_classType = $type;
	}
*/
  }

  public function reload(){
    $this->loadClass($this->attr($this->getId()));
  }

  protected function init( $id = false ){
    return $this->loadClass($id);
  }

  function loadClass( $id = false ){
    $settings = $this->getSettings();    
	//$class = $this->_classType;
	try{
	  if( $this->inAttr('_pubAttr') === NULL ){
	    errorHandler::report(ERR_FATAL,"Database col vars not defined in {$this->getSettings('class')}");
	  }
	  if( $settings['baseQuery'] === array() ){
	    errorHandler::report(ERR_FATAL,"No base query set.");
	  }
	}catch(eFatal $e){
	  return false;
	}
	if( !isset($settings['db_limit']) ){
	  $s = stristr( $settings['baseQuery'], 'LIMIT');
	  if( $s !== false ){
	    $a = preg_replace('/.[^\d,]+/', '', $s);
		$limit = explode(',',$a);
		if( count($limit)==2 ){
          #$this->setQueryLimit( $limit[0], $limit[1] );
          $this->setSettings('db_limit',array(intval($limit[0]),intval($limit[1])));
		}else{
          errorHandler::report(ERR_FATAL,"Query limits not set in {$this->getSettings('class')} but were detected in base query, however there was trouble extracting. Please check SQL.");
		  return false;
		}
	  }else{
        errorHandler::report(ERR_WARNING,"Query limits not set in {$this->getSettings('class')}. Assuming defaults 0,30");
        $this->setSettings('db_limit',array(0,30));
        #$this->setQueryLimit( 0, 30 );
      }
	}

    if( $id !== false ){
    
      $ids = explode(':',$id);
      
      if( count($ids) == count(current($settings['db_uid'])) ){
        $fv = $this->_lookup($ids);
        if($fv === false){return false;}
        if(count($fv) > 1 ){die('too many back!');}
        $this->fill($fv[0]);
        $this->_setCache($this);
      }else{
        errorHandler::report(ERR_FATAL,"database ID count for {$this->_table} does not match argument count in {$this->getSettings('class')}");
        return false;
      }
	}else{
      // return blank page
	}
    if(!isset($settings['db_order'])){
	  $this->setSettings('db_order',array('DESC'=>$this->getId()));
    }
  }

  public function makeChild(){
    errorHandler::report(ERR_WARNING,"DEPRECATED: makeChild method");
  }

  /**
   *  Returns uid of object based on all uniques
   *
   */

  public function getUid( $fillData = false ){
    if( !is_array($fillData) ){
	  $fillData = $this->attr();
	}
    errorHandler::report(ERR_FATAL,"DEPRECATED: db_id now returns something like array(\"user\"=>array(\"user_id\"=>true))");
	
    $ids = $this->getSettings('db_id');
	$id = '';
	foreach($ids as $key => $val ){
      if($id!=''){$id.=':';}
      $id.=str_replace(':','',$fillData[$val]);
	}
	return $id;
  }
/*
  public function getUId( $ids=false ){
    $id='';
	if( !is_array($ids) ){
      $ids = $this->getSettings('db_id');
	} 
	foreach($ids as $key=>$val){
      if($id!=''){$id.=':';}
      $id.=str_replace(':','',$this->attr($val));
	}
	return $id;
  }
*/
  function inAttr( $key=false, $val=NULL, $private=false ){
    if(isset($this->{$key})){
	  if( $val === NULL ){
	    return $this->{$key};
	  }else{
	    $this->{$key} = $val;
		return $this->{$key};
	  }
    }else{
	  return false;
	}
  }

  function attr( $key=false, $val=NULL, $private=false ){
    if( $key === false ){
      return ($private === false) ? $this->_pubAttr : array_merge_recursive($this->_pubAttr,$this->_prvAttr);
	}
	$key = $this->getDataMap($key);
	if( $key === false ){ return false;}

    if( $val != NULL ){
      $regex = $this->getSettings('regex');
	  if(isset($regex[$key[0]][$key[1]])){
	    $t = preg_replace($regex[$key[0]][$key[1]],'',$val);
		if( $t != '' ){
	  	  errorHandler::report(ERR_FATAL,"Invalid characters detected in $key");
		  return false;
		}
	  }
    }
	if( isset($this->_pubAttr[$key[0]][$key[1]]) ){
	  if( $val === NULL ){
	    return $this->_pubAttr[$key[0]][$key[1]];
	  }else{
#die('set a pub var');
		$this->_pubAttr[$key[0]][$key[1]] = $val;
	    if( !isset($this->_update[$key[0]][$key[1]]) ){
	      $this->_update[$key[0]][$key[1]] = true;
	    }else{
          errorHandler::report(ERR_NOTICE,"{$key} has already been altered in {$this->getSettings('class')}");
	    }
		return $this->_pubAttr[$key[0]][$key[1]];
	  }
	}else{
      if( !isset($this->_prvAttr[$key[0]][$key[1]]) ){
        errorHandler::report(ERR_NOTICE,"unknown column: {$key[1]} in table {$key[0]} in chapter {$this->getSettings('class')}");
	  }else{
        if( $private !== true ){
          errorHandler::report(ERR_WARNING,"{$key[1]} is private in {$this->getSettings('class')}");		
		  return false;
		}
		if( $val !== NULL ){
		  $this->_prvAttr[$key[0]][$key[1]] = $val;
	      if( !isset($this->_update[$key[0]][$key[1]]) ){
	        $this->_update[$key[0]][$key[1]] = true;
	      }else{
            errorHandler::report(ERR_NOTICE,"{$key} has already been altered in {$this->getSettings('class')}");
	      }
		}
		return $this->_prvAttr[$key[0]][$key[1]];
	  }
	}
  }

  function isNew(){
    $out = true;
    $ids = $this->getAutoInc();
    foreach($ids as $key => $val){
      if($this->attr($val) !== -1){
        $out = false;
	  }
    }
	return $out;
  }

  function save($meth = DB_INSERT){
	if($this->isNew()){
      $result = $this->_saveByInsert($meth);
	}else{
      $result = $this->_saveByUpdate($meth);
    }

    if( $result === false ){
      return false;
    }else{
      return true;
	}
  }
  
  function delete(){
    $this->_delete();
  }
  
  /**
   * fill
   * $row = array('attr1'=>'attrib', 'attr2'=>'attrib');
   */
  
  function fill( $row ){
    // required attribs.  setwith bitwise in chapters.
	$required = $this->getSettings('required');
    foreach($row as $key => $val){
      $_r = $this->getDataMap( $key );
      if( $_r != false ){
	    if( !isset($nRow[$_r[0]]) ){$nRow[$_r[0]] = array(); }
	    $nRow[$_r[0]][$_r[1]] = $val;
      }
	}
    $stillHere = array();
	
	foreach( $row as $key => $val ){
	  $this->secCheck($key, $val);
	}
	
#	$regex = $this->getSettings('regex'); // table => col_name, regex

#	if( $regex !== array() ){
#      errorHandler::report(ERR_DEV_NOTE,"Do regex in seperate security method");
#	  foreach($nRow as $key => $val){ // col_name, value
#        foreach($val as $k => $v){
#          if(isset($regex[$key][$k]) && $v != NULL){
#            $t = preg_match($regex[$key][$k], $v, $matches);
#            if( $t != 1 ){ $invalid[] = $key.'.'.$k; }
#		  }
#		}
#	  }
#	}
#var_dump($invalid);
#	if(isset($invalid)){
#      while($er = $invalid){ errorHandler::report(ERR_FATAL,"Invalid characters detected in col: {$er}"); }
#      return false;
#	}
	foreach( $nRow as $key => $val ){
      foreach($val as $k => $v){
        if(isset($this->_pubAttr[$key][$k])){
		  $this->_pubAttr[$key][$k] = $v;
		}else{
		  if(isset($this->_prvAttr[$key][$k])){
		    $this->_prvAttr[$key][$k] = $v;
		  }else{
		    //
		  }
		}
          //$this->attr($key, $val, true);
	    #}else{
	    #  if( gettype($key) != 'integer' ){
		#    errorHandler::report(ERR_NOTICE,"skipped col {$key} in {$this->getSettings('class')} fetch");
		#  }
	    #}
      }
	}
	$this->_postBuild();
  }
  
  public function secCheck($col, $val){
    $col = $this->getDataMap($col);
    
    /* check required cols */
    $required = $this->getSettings('required');
    if( isset($required[$col[0]][$col[1]])){
      if( is_bool($val) || $val === H_EMPTY_VALUE || $val === NULL ){
        errorHandler::report(ERR_FATAL,"Required field not filled: {$col[0]}.{$col[1]}");
        return false;
	  }
	}

    /* check regex */
	$regex = $this->getSettings('regex');
    if( isset($regex[$col[0]][$col[1]]) && $val != H_EMPTY_VALUE ){
      $t = preg_replace($regex[$col[0]][$col[1]],'',$val);
      if( $t != '' ){
        errorHandler::report(ERR_FATAL,"Invalid characters detected in {$col[0]}.{$col[1]}");
        return false;
      }
	}
	return true;
  }
  
  public function toJSON( $key=true ){
    if( $key === false ){
	  return json_encode($this->_pubAttr);
	}else{
      $settings = $this->getSettings();
	  return "{identifier: '".current($settings['db_uid'])."', label: '{$settings['searchVars']['string'][0]}', items: [".json_encode($this->_pubAttr)."]}";
	}
  }
  /*
  protected function _prefetch_array( $arr ){}
  protected function _prefetch_double( $dbl ){
    return $this->_prefetch_integer( $dbl );
  }
  protected function _prefetch_integer( $int ){
    $this->_query = $this->buildQuery( 'integer', $int );
  }
  protected function _prefetch_boolean( $bool ){
    die('hi');
  }
  protected function _prefetch_object( $obj ){}
  protected function _prefetch_string( $str ){
    $this->_query = $this->buildQuery( 'string', $str );  
  }
*/
  // DEPRECATED
  /*
  protected function fire(){
    // this func may not be needed anymore
    return $this->_dofetch();
  }
  */

  private function _dofetch(){
    $this->connect_db();
    $result = $this->select($this->_query);
    if( $result === false ){
      errorHandler::report(ERR_FATAL,mysql_error().' SQL: '.$this->_query);
      return false;
    }
    if( mysql_num_rows($result) === 0 ){
      errorHandler::report(ERR_FATAL,"{$this->getSettings('class')} not found.");
	  return false;	
	}
    if( mysql_num_rows($result) > 1 ){
      errorHandler::report(ERR_FATAL,"More than one result returned in {$this->getSettings('class')} _dofetch.");
	  return false;
    }
	$row = mysql_fetch_array($result);
	foreach( $row as $key => $val ){
	  if( isset( $this->_pubAttr[$key] ) ){
        $this->_pubAttr[$key] = $val;
	  }else{
	    if( !is_int($key) ){
          errorHandler::report(ERR_NOTICE,"skipped col {$key} in {$this->getSettings('class')} fetch");
		}
	  }
	}
    return true;
  }

  function getAutoInc($table=false){
    $s = $this->getSettings('db_autoinc',true);
    $retVal = array();
    if($table!=false){
	  if( !isset($s[$table]) ){
	  	$retVal = false;
	  }else{
        $retVal = $s[$table];
	  }
	}else{
      foreach($s as $key => $val){
        $retVal[] = $key.'.'.$val;
	  }
    }
    return $retVal;
  }

  protected function addCol( $table, $col, $validation=false, $sqlFlags=false ){

    // PDO!
	if( !isset($this->_pubAttr[$table]) ){ $this->_pubAttr[$table] = array(); }
    $this->_pubAttr[$table][$col] = -1;
    $obj = $this->_classType;
	$this->setDataMap($table,$col);
    // PDO!

	if( $validation !== false ){
      $this->setSettings( 'regex' , array($table=>array($col=>'/'.$validation.'/')) );
    }

	if( $sqlFlags & COL_AUTOINC ){
	  $this->setSetting2( 'db_autoinc' , $table, $col );
	  $this->setSetting2( 'db_unique', $table, array($col=>true) );
	}
	if( $sqlFlags & COL_REQUIRED ){
	  $this->setSetting2( 'required' , $table, array($col=>true) );
	}
	/* Single or multi column Primary Key supported */
	if( $sqlFlags & COL_UNIQUE_ID ){
	  $this->setSetting2( 'db_uid' , $table, array($col=>true) );
	}
	if( $sqlFlags & COL_UNIQUE ){
	  $this->setSetting2( 'db_unique', $table, array($col=>true) );
	}
	if( $sqlFlags & COL_PRIVATE ){
      $this->_setColsPrivate($table.'.'.$col);
	}
	if( $sqlFlags & COL_SEARCH_INT ){
      $this->setSetting2('searchVars',$table, array($col=>'integer') );
	}

	if( $sqlFlags & COL_SEARCH_STR ){
      $this->setSetting2('searchVars',$table, array($col=>'string') );
	}
  }

  function buildCols( $table, $colArray ){
    $settings = $this->getSettings();
    if( !isset($settings['tables'][$table]) ){
      $this->setSettings( 'tables', array($table=>$colArray) );
    }
    foreach($colArray as $key => $val){
	  $this->_pubAttr[$val] = -1;
	}
  }

  function requiredCols( $colArray ){
    foreach($colArray as $key => $val){
      $this->_settings['required'][] = $val;
	}
  }

  function secureCols( $regexArray ){
    foreach($regexArray as $key => $val){
      $this->setSettings('regex', array($key=>'/'.$val.'/'));
    }
  }
  
  protected function _setColsPrivate( $col ){
    $col = $this->getDataMap($col);
#	if( isset($this->_prvAttr[$col[0]][$col[1]])){return true;}
    if( $col !== false ){
      $this->_prvAttr[$col[0]][$col[1]] = $this->_pubAttr[$col[0]][$col[1]];
      unset( $this->_pubAttr[$col[0]][$col[1]] );
	  return true;
    }else{
	  return false;
	}
  }

  function bindCols( $value, $pointer ){
    $v = explode('.',$value);
    $p = explode('.',$pointer);
	if( $v[1] == $p[1] ){
	  errorHandler::report(ERR_FATAL,"Same col name in bindCols");
	  return false;
	}
	$this->_pubAttr[$p[1]] = &$this->_pubAttr[$v[1]];
  }
}
?>