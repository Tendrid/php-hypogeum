<?php

require_once('base.php');

/**
 * Chapter
 * Each chapter is a seperate object that is bound to a class file and database
 */
 
class chapter extends layout{
  #private $_parent;
  private $_class;
  protected $_blankPage = false;
  protected $_last = array();
  #private $_all = array();
  protected $_objType = 'chapter';
  protected $_lastCount = 0;

  public $_settings = array();
  public $_cache = array();
  public $_colMap = false;
  protected $query = false;
  #private $_queries = array();
  #var $_objMap = array();
  protected $_mapTable = array();
    
  //TODO: $_db['id'] is depprecated. use $_db['uid']['table_name'][array()];
  
  /**
   *  NOTE:
   *  The "_" char in a class name denotes a directory strucutre.
   *  Any class begining with a _ is considered private.  It is only avaliable for direct reference. (not via codex)
   */
  function __construct( $class_name ){
    $this->_class = $class_name;
    $class_name = str_replace('_','/',$class_name); // make file system safe
    if( $class_name != preg_replace('/[^a-zA-Z0-9\/]/','',$class_name) ){
      errorHandler::report(ERR_FATAL, 'invalid characters in chapter name');
    }
	if( $class_name[0] == '/' ){
	  $class_name = ltrim($class_name,'/');
      $cPath = H_PATH.'/chapters';
	}else{
      $cPath = H_CODEX_PATH.'/'.connect::$_codex->name();
	}
	if( is_file(SYSTEMROOT.$cPath.'/'.$class_name.'.php') ){
	  connect::$_codex->debugVars['libs'][] = $this->_class;
      require_once(SYSTEMROOT.$cPath.'/'.$class_name . '.php');
    }else{
      errorHandler::report(ERR_FATAL, 'invalid chapter');
	}	
  }

  function init(){
    $this->_last = array();
    $this->_blankPage = new $this->_class;
    return $this->_blankPage;
	#die('in-complete in chapter 56');
  }
  
  public function __call($name, $arguments){
    errorHandler::report(ERR_FATAL, 'Function call on page before initiating. No reason to throw this. create page, then recall and return.');
  }
  
  public function getAll( $start=0, $count=1000 ){
    errorHandler::report(ERR_FATAL, 'Deprecated method "getAll".  user "all".');
    $this->all($start, $count);
  }

  public function load( $ar_id ){
    if( $ar_id[0] === false ){ die('id required in chapter'); return $this->getBase(); }
	
	if( is_array($ar_id[0]) ){
	  // if the object has a multiple field unique id
      $ar_id = $ar_id[0];
	}
	/* CACHE MAGIC! */
	$fromCache = $this->_getCache( key($this->_settings['db_uid']), key(current($this->_settings['db_uid'])), $ar_id[0]  );
	if($fromCache !== false){return array($fromCache);}
	/* /CACHE MAGIC! */
	$out =  $this->search($ar_id[0], key($this->_settings['db_uid']).'.'.key(current($this->_settings['db_uid'])));
	return $out[0];
  }

	/**
	 * Return all (start,count)
	 * 
	 * @start int
	 * @count int
	 */

	public function all( $start=0, $count=1000 ){	
		$this->setSettings('db_limit', array(intval($start),intval($count)));
		$db = $this->initDB();
		$db->execute();
		$retVal =  $db->fetchAll(PDO::FETCH_ASSOC);
		return $this->objectify($retVal);
	}

	/**
	 * Search for basedon value(s) and column(s)
	 * 
	 * 
	 */
	public function search( $needle, $col=false, $like=false ){
		// TODO: Search by multiple columns
		if( is_array($col) ){ die('223ish in chapter:  array of cols not supported for caching yet.  Do it now.'); }
		if( $like == false){
			$cCheck = $this->getDataMap($col);
			/* CACHE MAGIC! */
			$fromCache = $this->_getCache( $cCheck[0], $cCheck[1], $needle  );
			if($fromCache !== false){return array($fromCache);}
			/* /CACHE MAGIC! */
		}

		// TODO: Make this use is_array.
		$type = gettype($needle);
		if( $type === gettype($col) && $type === 'array'){
			if( count($needle) !== count($col) ){
				errorHandler::report(ERR_FATAL,"search and column count must be equal in {$this->getSettings('class')}::_search");
				return false;
			}
		}

		// TODO: Search for array by default int &| string
		if( $col === false && $type != 'array' ){ die('not here yet. 111(ish) in chapter');}
		if( $type == 'array' ){
			$where = '';
			foreach($needle as $key => $val){
				$opp = $this->_buildLike($val, $col[$key], $like);
				if( $where != '' ){ $where .= " AND "; }
				$where .= $opp;
				$done[] = $col[$key];
			}
			if( count($col) !== count($done) ){
				errorHandler::report(ERR_FATAL,"Some columns could not be found {$this->getSettings('class')}::_search");
				return false;
			}
		}else{
			$opp = $this->_buildLike($needle, $col, $like);
		}
		$query = str_replace('1=1', $opp[0], $this->getSettings('baseQuery'));
		$db = $this->initDB($query);
		$db->bindValue(1, $opp[1], PDO::PARAM_STR);
		$db->execute();
		$retVal =  $db->fetchAll(PDO::FETCH_ASSOC);
		return $this->objectify($retVal);
	}

  public function getFamilyById( $id, $count, $serchVal, $searchCol ){
    $tmp = $this->getBase();
	#$dbVars = $tmp->getSettings('db_id');
    $this->_last = array();
	$out = $tmp->_getFamilyById( $id, $count, $serchVal, $searchCol );
	$in = $this->_doSearch($out);
	if( $in == false ){
      unset($tmp);
      return false;
    }
	foreach($in as $key => $val){
      if( $val->attr($tmp->getId()) < $id ){
        $retVal[0][] = $val;
	  }else{
	    $retVal[1][] = $val;
	  }
	}
	try{
      $this->_postSearch();
	}catch(eFatal $e){
    }
    unset($tmp);
	return $retVal;
	#return $this->_doSearch($out, $dbVars[0] );
  }
  
  public function getRandom( $start=0, $count=10000 ){
  //SELECT * FROM image ORDER BY  RAND() LIMIT 0,30
	$this->setSettings('db_orderby', 'RAND()');
    $this->setQueryLimit( $start, $count );
    $tmp = $this->getBase();
	$this->_last = array();
	foreach( $tmp->_all( $start, $count ) as $key => $val ){
      #errorHandler::report(ERR_DEV_NOTE,"make a getId() func, because things with multi-id will fail here");
      try{
		$this->setCache($val);
      }catch(eFatal $e){
		die('invalid characters in random');
	    return false;
      }
	  #$this->$val[$tmp->getId()] = $this->getBase();
	  #$this->$val[$tmp->getId()]->fill($val);
	  #$this->makeChild($val[$dbVars[0]]);
	  #$this->_last[] = $this->$val[$tmp->getId()];
      #$this->_all[$val[$tmp->getId()]] = $this->$val[$tmp->getId()];
	}
	unset($tmp);
	try{
      $this->_postSearch();
	}catch(eFatal $e){
    }
	return $this->fetchLast();
  }

  protected function setCache($val){
  /*
    return NULL;
    $tmp = $this->getBase();
	#var_dump($tmp->getId());die('163 in chapters');
    #var_dump($tmp->getUid($val));die();
    #var_dump($this->$val[$tmp->getUid($val)]);die();
    $me = $this->getBase();
    $me->fill($val);
    $uId = $me->attr($tmp->getId());
	$this->$uId = $me;
	$this->_last[] = $this->$uId;
    $this->_all[$uId] = $this->$uId;
    #unset($tmp);
    */
  }
    
  protected function _doSearch($searchResults){
	if( is_array($searchResults) ){
	  foreach( $searchResults as $key => $val ){
        try{
		  $this->setCache($val);
        }catch(eFatal $e){
		  #die('invalid characters in _doSearch');
	      return false;
        }
	  }
	}else{
      errorHandler::report(ERR_FATAL, 'searchResults param in _doSearch must be array');
	}
	return $this->fetchLast();
  }
  
  public function searchBetween( $string1, $string2, $col ){
    $tmp = $this->getBase();
	#$dbVars = $tmp->getSettings('db_id');
    $this->_last = array();
	try{
      $out = $tmp->_searchBetween( $string1, $string2, $col );
	}catch(eFatal $e){
	  unset($tmp);
	  return false;
	}
    $retVal = $this->_doSearch($out);
    unset($tmp);	
	try{
      $this->_postSearch();
	}catch(eFatal $e){
    }
	return $retVal;
  }

  private function _postSearch(){
    foreach($this->_mapTable as $key => $val){
      $this->_loadMap($val);
    }
  }

	/**
	 *  Create 1:Many, Many:Many, Many:1 map between objects
	 * 
	 * @lib
	 * 
	 * @mapParent
	 * 
	 * $mapChild
	 * 
	 * @lazy (bool)
	 * 		true:	the children will load as soon as the parent is loaded.
	 * 		false:	the children will only load if directly referenced.
	 */
	public function map( $lib, $mapParent, $mapChild ,$lazy = false){
		#$_key = md5($lib.':'.$mapParent.':'.$mapChild);
		#if( !isset($this->_mapTable[$_key]) ){
		$lazy = (is_bool($lazy)) ? $lazy : false;
		$this->_mapTable[] = array('lib'=>$lib, 'mapParent'=>$mapParent, 'mapChild'=>$mapChild, 'lazy' => $lazy);
		#}
	}

/*
  // DEPRECATED
  public function isLoaded( $args ){
    $id='';
    foreach($args as $key=>$val){
      if($id!=''){$id.=':';}
      $id.=str_replace(':','',$val);
    }
    return isset($this->$id);
  }
*/

  private function _loadMap($map){
  return true;
  #die('deprecated');
  // TODO NEXT:
  // check to see if we are mapping to the $lib id.
  // If we are, loop through each $lib obj to see if it is loaded already.
  // If it is loaded, remove it from the IN(...) sql array.
  // thats fucking awesome.
/*
array(3) {
  ["lib"]=>
  string(11) "user_attrib"
  ["mapParent"]=>
  string(7) "user_id"
  ["mapChild"]=>
  string(7) "user_id"
}
*/
  #die('272 in chapter');
  #var_dump($this->_last);
		errorHandler::report(ERR_DEV_NOTE,"Change this from a search to searchIn");
		foreach($this->_last as $key => $val){
			if( !isset($us) ){
				$mapCol = $val->attr($map['mapParent']);
				$us = connect::$_codex->$map['lib']->search($mapCol[0],$map['mapChild']);
			}
			foreach($us as $k => $v){
				$val->addMapChild($v);
			}
		}
#var_dump($this->_last);
#die();
		return true;
/*
    $tmp = microtime();
    $this->$tmp = connect::$_codex->$map['lib'](false);
    #$dbVars = $this->$tmp->getSettings('db_id');
	$isIndexed = false;
	#var_dump($map['mapChild'], $this->$tmp->getId($dbVars));

    #foreach(connect::$_codex->$map['lib'] as $key => $val){
    #  var_dump($_codex->$map['lib']);
    #}

    $parent = $this->_class;
	$child = $map['lib'];
	$arrList = array();
    foreach (connect::$_codex->$parent->fetchLast() as $key => $val){
      $arrList[] = $val->attr($map['mapParent']);
	}
	if( $arrList == array() ){
	  unset($this->$tmp);
	  return false;	
	}
	try{
      $out = $this->$tmp->_searchIn( $map['mapChild'], $arrList );
      $retVal = connect::$_codex->$child->_doSearch($out);
	}catch(eFatal $e){
	  unset($this->$tmp);
	  return false;
	}
    unset($this->$tmp);
	foreach($retVal as $key => $val){
      #if( !isset($mapLink[$val->attr($map['mapChild'])]) ){$mapLink[$val->attr($map['mapChild'])] = array();}
#$mapLink[$val->attr($val->getId())][] = $val;
      $mapLink[$val->attr($map['mapChild'])][] = $val;
      #$this->_objMap[$val->attr($dbVars[0])] =& $val;
	  // $this is core object (the "1" in 1:many)
	  // $val is mapped obj (the "many" in 1:many)
	  #$this->addMapChild($val);
	}
	#var_dump(connect::$_codex->$parent->fetchLast());die();
	foreach(connect::$_codex->$parent->fetchLast() as $key => $val){
	#var_dump($mapLink,$val->attr($map['mapChild']));die();
      if( isset($mapLink[$val->attr($map['mapChild'])]) ){
        foreach( $mapLink[$val->attr($map['mapChild'])] as $k => $v ){
		#var_dump($val);die();
		  // $v is mapped obj (the "many" in 1:many)
		  $val->addMapChild($v);
		}
	  }
	}
	return $retVal;
	*/
  }

  public function getQueryCount(){
    return $this->_lastCount;
    #$result = mysql_query('SELECT FOUND_ROWS();');
    #$row = mysql_fetch_array($result);
	#return $row[0];
  }

  public function setQueryCount(){
    #$result = mysql_query('SELECT FOUND_ROWS();');
    #$row = mysql_fetch_array($result);
	#$this->_lastCount = $row[0];
	#return $row[0];
  }

  public function getCount( $needle=false, $like=false, $col=false ){
die('dig deeper: chapter 301');
    $this->setQueryLimit(0,1);
	$this->search( $needle, $like, $col );
    return $this->getQueryCount();
  }

  public function toJSON( $format='grid' ){
  // argument passed should be format type (tree, grid, raw, etc...)
    errorHandler::report(ERR_DEV_NOTE, 'make arguments defined constants');
	$out = array();
	$i=0;
	foreach($this->_last as $key => $val ){
	  if( method_exists($val,'extendedData_PRE') ){
	    $_out = $val->extendedData_PRE();
		if( is_array($_out) ){
		  $out[$i] = array_merge($val->attr('_public'),$_out);
		}
	  }else{
        $out[$i] = $val->attr('_public');
	  }
	  if( method_exists($val,'extendedData') ){
	    $_out = $val->extendedData();
		if( is_array($_out) ){
		  $out[$i] = array_merge($out[$i],$_out);
		}
	  }
	  foreach($val->_objMap as $k => $v){
	  //each maped chapter
        if(!isset($tmp[$i])){$tmp[$i] = array();}
        if(!isset($tmp[$i][$k])){$tmp[$i][$k] = array();}
	    foreach($v as $_k => $_v){
		//each maped item
		  $tmp[$i][$k][] = $_v->attr();
		}
	  }
	  $i++;
	}
    if( $format === 'raw' ){
	  return json_encode($out);
	}else{
      $numRows = $this->getQueryCount();
      $settings = $this->getSettings();
	  return "{\"identifier\": \"".current($settings['db_id'])."\", \"numRows\": \"$numRows\" ,\"label\": \"{$settings['searchVars']['string'][0]}\", \"items\": ".json_encode($out)."}";
	}
  }
  
  public function fetchAll(){
    errorHandler::report(ERR_FATAL, 'Deprecated method "fetchAll".  no replacement method at this time. (if need, work off of cache, method name: fetchCache)');
    #return $this->_all;
  }

  public function fetchLast(){
    return $this->_last;
  }

  public function getLast(){
    errorHandler::report(ERR_FATAL, 'Deprecated method "getLast".  user "fetchLast".');
    return $this->fetchLast();
  }

  function attr( $key=false /*, $val=NULL, $private=false */ ){
    $retVal = array();
    foreach( $this->_all as $k => $v ){
      $retVal[] = $v->attr($key);
	}
	return $retVal;
  }

  public function getBase(){
    if( $this->_blankPage == false ){ $this->_blankPage = new $this->_class; }
    $out = clone $this->_blankPage;
	return $out;
  }
  
  public function create($arr = false){
    $tmp = $this->getBase();
    if( $arr !== false ){
      if( $tmp->fill($arr) === false ){
        errorHandler::report(ERR_DEV_NOTE,'I should be using a try under this method to catch public errors.');
        return false;
	  }
    }else{
      #$this->_all[PHP_INT_MAX&microtime(true)*10000] = $tmp;
      #$this->_all[uniqid()] = $tmp;
	}
	return $tmp;
  }

}
?>