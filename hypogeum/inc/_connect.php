<?php

define('MATCH_NONE',	0);
define('MATCH_START',	1);
define('MATCH_END',		2);
define('MATCH_ANY',		3);

define('DB_UPDATE',		true);
define('DB_INSERT',		false);
define('DB_REPLACE',	2);

abstract class connect{

  static $_codex;
  static $_error;
  #protected $_settings = array();
  protected $_objMap = array();

  function connect_db($d = DATABASE_NAME){
    $o = mysql_connect(DATABASE_LOC, DATABASE_USERNAME, DATABASE_PASSWORD) or die(DATABASE_CONNECT_ERROR_MESSAGE);
    mysql_select_db($d) or die(DATABASE_TABLE_ERROR_MESSAGE);
    return $o;
  }

  /**
   * $domain = www.domain.com
   * $uri = /path/to/file.php?id=1234
   */

  function wget($url, $username = false, $password = false){
    #$host = "http://twitter.com/statuses/update.xml?source=lyfe&status=".urlencode(stripslashes(urldecode($message)));
	         #http://twitter.com/statuses/followers.xml
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    #curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if( $username !== false && $password !== false ){
      curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	}
    #curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_POST, false);
	
    $result = curl_exec($ch);
    // Look at the returned header
    $resultArray = curl_getinfo($ch);
	$content = curl_multi_getcontent($ch);
    curl_close($ch);

    if($resultArray['http_code'] == "200"){
	  return $content;
    } else {
       return false;
    }
  }

  function select($query /*,$getCount = true*/){
    $this->connect_db();
    $result = $this->_query($query);
	if( $result === false ){
      errorHandler::report(ERR_FATAL, "SQL is teh borked: SQL({$query}) ERROR(".mysql_error().")");
	}
    $obj = $this->getSettings('class');
    errorHandler::report(ERR_DEV_NOTE, "currently running get rows on ALL queires.  This doublers queries. QUIT IT.");
    #if( $getCount === true ){
	  #connect::$_codex->$obj->setQueryCount();
	#}
    #$this->_codex->$obj->_queries[] = $query;
	return $result;
  }

  function alter($query){
    if( DATABASE_READONLY !== false ){
      errorHandler::report(ERR_FATAL, "Database is set to READ ONLY");
      return false;
    }else{
      $this->connect_db();
      $result = $this->_query($query);
      return $result;
    }
  }
  
  private function _query($query){
    if( DEBUG === true ){
	  connect::$_codex->debugVars['queries'][] = $query;
	}
	errorHandler::report(ERR_DEV_NOTE, "IMPORTANT!!! MySQL lib in deprecation.  Use PDO!");
    return mysql_query($query);
  }
  
  function buildQuery( $type, $searchValue, $like=MATCH_NONE ){
    $where = '';
	$sv = $this->getSettings('searchVars');
	var_dump($sv);die();
    foreach( $sv[$type] as $key => $val ){
	  if( $where != '' ){ $where.=' OR '; }
	  $opp = ( $like !== false ) ? "LIKE '%".addslashes($searchValue)."%'" : "= '".addslashes($searchValue)."'";
	  $where .= "`{$val}` {$opp}";
    }
	$this->setSettings('db_where', $where);
	$this->_query = $this->getSettings('baseQuery');
    $this->_query = $this->_alterQueryWhere( $this->_query );
    $this->_query = $this->_alterQueryLimit( $this->_query );
    $this->_query = $this->_alterQueryOrder( $this->_query );
    return $this->_query;
  }

  protected function _lookup( $ids ){
  // only supports one id right now.
  // do we ever need to spawn a PAGE based on 2 unique ids?
  // sql map tables should do that for us.
  #var_dump($ids);die();
	$dbVars = $this->getSettings('db_uid');
	$col = key($dbVars).'.'.key(current($dbVars));
	$query = str_replace('1=1',$col.'=?',$this->getSettings('baseQuery'));
    $db = connect::$_codex->db()->prepare($query);
	$db->bindValue(1, $ids[0]);
    $db->execute();
    return $db->fetchAll(PDO::FETCH_ASSOC);
  }

  ////////// SEARCH ///////////////////////////////////////////

  function _all( $start=false, $count=false ){
    $settings = $this->getSettings();
	$_s = $settings['db_limit'];
    if( $start !== false ){
	  $_s[0] = intval($start);
	}
    if( $count !== false ){
      $_s[1] = intval($count);
	}
	$this->setSettings('db_limit', $_s);

	#var_dump($this->getSettings());die();
	$this->_query = $settings['baseQuery'];
    $this->_query = $this->_alterQueryLimit( $this->_query );
    $this->_query = $this->_alterQueryOrder( $this->_query );
    $this->connect_db();
    try{
      $result = $this->select($this->_query);
	}catch(eFatal $e){
      //invalid sql query
	  return array();
    }
    while( $row = mysql_fetch_array($result, MYSQL_ASSOC) ){
	  $retVal[] = $row;
	}
	#var_dump($retVal);die();
	if( isset($retVal) ){
      return $retVal;
	}else{
	  return array();
	}
  }

  function _getFamilyById( $id, $count, $serchVal, $col ){
    $inDb = false;
    foreach( $this->getSettings('tables') as $key => $val ){
	  if(in_array($col,$val)){
	    $inDb = true;
      }
    }
    if( $inDb == false ){
	  return false;
	}
	$count = intval($count);
    $db_id = $this->getId();
	#$db_id = $this->getSettings('db_id');
	$this->setQueryLimit( 0, $count );
	$wUp = "`{$col}`={$serchVal} AND {$db_id} > $id";

	$this->setQueryOrder('-'.$db_id);
	$wDown = "`{$col}`={$serchVal} AND {$db_id} < $id";

    $queryUp = str_replace('1=1', $wUp, $this->getSettings('baseQuery'));
    $queryUp = $this->_alterQueryLimit( $queryUp );

    $queryDown = str_replace('1=1', $wDown, $this->getSettings('baseQuery'));

    $queryDown = $this->_alterQueryOrder( $queryDown );
    $queryDown = $this->_alterQueryLimit( $queryDown );

    $this->_query = '('.$queryDown.')UNION('.$queryUp.') ORDER BY '.$db_id;
	return $this->_doSearch();
  }

  function _searchBetween( $string1, $string2, $col ){
    if( gettype($string1) !== gettype($string2) ){
      errorHandler::report(ERR_FATAL,"Datatypes do not match in {$this->getSettings('class')}::_searchBetween");
	  return false;
	}
	$map = $this->getDataMap($col);
	if( $map == false ){
      errorHandler::report(ERR_FATAL,"Invalid col in {$this->getSettings('class')}::_searchBetween");
	  return false;
	}
    if( is_int($string1) ){
      $where = "`{$map[0]}`.`{$map[1]}` BETWEEN ".addslashes($string1)." AND ".addslashes($string2)."";
	}else{
      $where = "`{$map[0]}`.`{$map[1]}` BETWEEN '".addslashes($string1)."' AND '".addslashes($string2)."'";
    }
    $this->_query = str_replace('1=1', $where, $this->getSettings('baseQuery'));
    $this->_query = $this->_alterQueryLimit( $this->_query );
    $this->_query = $this->_alterQueryOrder( $this->_query );
	return $this->_doSearch();
  }

  function _searchIn( $col, $searchList, $retQuery=false ){
    if( !is_array($searchList) ){
      errorHandler::report(ERR_FATAL,"Datatype must be array in _searchIn");
	  return false;
	}
    if( !is_array($col) ){
      $col = array($col);
    }
    foreach($col as $key => $val){
      $row = $this->getDataMap($val);
      if( !isset($cols[$row[0]]) ){ $cols[$row[0]] = array(); }
      $cols[$row[0]][$row[1]] = $searchList;
    }

    $this->_query = $this->_alterQueryWhere($this->getSettings('baseQuery'),$cols);
	if( $retQuery == true ){
      return $this->_query;
	}else{
      return $this->_doSearch();
	}
  }

  protected function _doSearch(){
    #if( $this->_debug === true ){
    #  var_dump($this->_query);
	#}
    #die($this->_query);
    #$this->connect_db();
    try{
      $result = $this->select($this->_query);
	}catch(eFatal $e){
      //invalid sql statment
	  return array();
	}
	if( mysql_num_rows($result) == 0 ){
	  return array();
	}
    while( $row = mysql_fetch_array($result, MYSQL_ASSOC) ){
	  $retVal[] = $row;
	}
	#var_dump($retVal);die();
	return $retVal;
  }

  ///// DELETE ////////////////////////
  
  protected function _delete(){
    $table = $this->getSettings('table');
	$db_id = $this->getId();
    $query = "DELETE FROM {$table} WHERE {$db_id}={$this->attr($db_id)}";
	$this->alter($query);
  }

  ///// SAVE //////////////////////////
  
	protected function _saveByUpdate($meth){
		if( count($this->_update) == 0 ){
			errorHandler::report(ERR_WARNING, 'Nothing to save.');
			return false;
		}
		foreach( $this->_update as $key => $val ){
	    	foreach($val as $k => $v){
				// Pre-save hook
				$func = 'PRESAVE_'.$key.'_'.$k;
				if( method_exists( $this, $func ) ){ $this->$func($this->attr($key)); }
				//endhook
    		}
		}
		errorHandler::report(ERR_DEV_NOTE, 'onfail of any query, rollback orginal values');

		foreach($this->_update as $key => $val){
			foreach($val as $k=> $v){
				if(!isset($queries[$key]['cols'])){$queries[$key]['cols']=array();}
				if(!isset($queries[$key]['vals'])){$queries[$key]['vals']=array();}
				$queries[$key]['cols'][] = $k;
				$queries[$key]['vals'][] = $this->attr($k);
			}
		}
		$this->pdoQuery($queries, DB_UPDATE);

		foreach( $this->_update as $key => $val ){
			foreach($val as $k => $v){
				// Pre-save hook
				$func = 'POSTSAVE_'.$key.'_'.$k;
				if( method_exists( $this, $func ) ){ $this->$func($this->attr($key)); }
				//endhook
			}
		}

		try{
			if( method_exists($this, 'POSTINSERT') ){ $this->POSTINSERT(); }
		}catch(eFatal $e){
			return false;
		}
		return true;
  }


  protected function _saveByInsert($meth){
    $queries = array();
    foreach( $this->attr(false,NULL,true) as $k => $v ){
      if(!isset($queries[$k])){$queries[$k]=array();}
        foreach($v as $_k => $_v){
        if($_v !== -1){
          if(!isset($queries[$k]['cols'])){$queries[$k]['cols']=array();}
          if(!isset($queries[$k]['vals'])){$queries[$k]['vals']=array();}
          $queries[$k]['cols'][] = $_k;
          $queries[$k]['vals'][] = $_v;
	      $func = 'PRESAVE_'.$_v;
		  try{
            if( method_exists( $this, $func ) ){
		      if( $this->$func($_v) === false ){
                errorHandler::report(ERR_FATAL,"Failed presave precautions!");
			    return false;
			  }
            }
          }catch(eFatal $e){
            return false;
		  }
	      //endhook
        }else{
		  // skipped val because it was never set.
          errorHandler::report(ERR_DEV_NOTE, 'check here to see if val is required');
		}
	  }
      //do sql
	  $this->pdoQuery($queries, $meth);
        foreach($v as $_k => $_v){
          if( $_v !== -1){
            // Post-save hook
            $func = 'POSTSAVE_'.$_k;
            if( method_exists( $this, $func ) ){ $this->$func($_v); }
            //endhook
          }
        }
	}
	try{
      if( method_exists($this, 'POSTCREATE') && $meth != DB_REPLACE){ $this->POSTCREATE(); }
	}catch(eFatal $e){
	  return false;
	} 
	#var_dump($this);die();
	return true;
  }


	/**
	 * 
	 * 
	 */
  protected function rawSql($s){
		connect::$_codex->db()->beginTransaction();
		$sql = connect::$_codex->db()->prepare($s);
		$sql->execute();
		connect::$_codex->db()->commit();
	}

	/**
	 * 
	 * 
	 */
	public function pdoQuery($q, $type=DB_INSERT){
		if( !is_array($q) ){ die('invalid argument in _connet::pdoInsert'); }
		connect::$_codex->db()->beginTransaction();
		try{
			foreach($q as $key => $val){ // for each table
				if(!is_array($val['cols'])){ errorHandler::report(ERR_FATAL,"invalid argument in _connet::pdoInsert::key"); }
				if(!is_array($val['vals'])){ errorHandler::report(ERR_FATAL,"invalid argument in _connet::pdoInsert::val"); }
				if( count($val['cols']) != count($val['vals']) ){ errorHandler::report(ERR_FATAL,"miscount val:col in pdoInsert"); }
//var_dump($type,DB_UPDATE);die();
				if($type === DB_UPDATE){
					foreach($val['cols'] as $k => $v){ // for each col
						if(isset($r)){$r.=',';}else{$r='';}
						$r.='`'.$key.'`.'.'`'.$v.'` = ?';
					}
					$sqlvals = implode(',',array_fill(0,count($val['cols']),'?'));
					//var_dump($this->getAutoInc($key));die();
					$ids = $this->getId(true);
					//$where = 'WHERE ';
					if($this->getAutoInc($key) == false){die('Fix 387 in _connect. loop through getId');}
					$sql = connect::$_codex->db()->prepare("UPDATE {$key} SET {$r} WHERE {$this->getAutoInc($key)} = '{$this->attr($this->getAutoInc($key))}'");
				}else{
					foreach($val['cols'] as $k => $v){ // for each col
						if(isset($r)){$r.=',';}else{$r='';}
						$r.='`'.$key.'`.'.'`'.$v.'`';
					}
					$sqlvals = implode(',',array_fill(0,count($val['cols']),'?'));
					$inType = ($type == DB_INSERT) ? 'INSERT' : 'REPLACE';
					$sql = connect::$_codex->db()->prepare("{$inType} INTO {$key} ({$r}) VALUES ({$sqlvals})");
				}
//				var_dump($sql);die();
				foreach($val['vals'] as $k => $v){
					$sql->bindValue($k+1,$v);
				}
				#var_dump($sql);
				$sql->execute();
				//var_dump($sql->errorInfo());die();
				$outId = connect::$_codex->db()->lastInsertId();
				// check error info here.
				if($type == DB_INSERT){
					if( $outId == '0' ){
						//this should never happen.  Take a look at this and figure out if its needed in this method
						errorHandler::report(ERR_FATAL,"Value already exisits in database");
					}else{
						$this->attr( $this->getAutoInc($key),$outId);
					}
				}
				unset($r);
			}
			connect::$_codex->db()->commit();
			#var_dump($sql);
		}catch(eFatal $e){
			connect::$_codex->db()->rollBack();
			#connect::$_codex->db->errorInfo();
		}
	}
/*  
  public function pdoInsert($q){
  #var_dump($q);
    if( !is_array($q) ){ die('invalid argument in _connet::pdoInsert'); }
	connect::$_codex->db()->beginTransaction();
    try{
      foreach($q as $key => $val){
        if(!is_array($val['cols'])){ errorHandler::report(ERR_FATAL,"invalid argument in _connet::pdoInsert::key"); }
        if(!is_array($val['vals'])){ errorHandler::report(ERR_FATAL,"invalid argument in _connet::pdoInsert::val"); }
        if( count($val['cols']) != count($val['vals']) ){ errorHandler::report(ERR_FATAL,"miscount val:col in pdoInsert"); }
        foreach($val['cols'] as $k =>$v){
          if(isset($r)){$r.=',';}else{$r='';}
          $r.='`'.$key.'`.'.'`'.$v.'`';
	    }
        $sqlvals = implode(',',array_fill(0,count($val['cols']),'?'));
        $sql = connect::$_codex->db()->prepare("INSERT INTO {$key} ({$r}) VALUES ({$sqlvals})");
        foreach($val['vals'] as $k => $v){
          $sql->bindValue($k+1,$v);
          #var_dump($v);
		}
        #var_dump($sql);
		$sql->execute();
        #var_dump(connect::$_codex->db->errorInfo());
        $outId = connect::$_codex->db()->lastInsertId();
		// check error info here.
		if( $outId == '0' ){
          //this should never happen.  Take a look at this and figure out if its needed in this method
          errorHandler::report(ERR_FATAL,"Value already exisits in database");
		}else{
          $this->attr( $this->getAutoInc($key),$outId);
        }
        unset($r);
      }
	  connect::$_codex->db()->commit();
    }catch(eFatal $e){
      connect::$_codex->db()->rollBack();
      #connect::$_codex->db->errorInfo();
    }
  }
*/
  private function _formatCols($t, $c){
    foreach($c as $key =>$val){
      if(isset($r)){$r.=',';}else{$r='';}
      $r.='`'.$t.'`.'.'`'.$val.'`';
	}
    return $r;
  }
  private function _formatVals($t, $v){
    foreach($v as $key =>$val){
      if(isset($r)){$r.=',';}else{$r='';}
      $r.='\''.$val.'\'';
	}
    return $r;
  }

  /////// SET QUERY ATTRIBUTES //////////////////

  public function setQueryLimit( $start, $limit ){
    $this->setSettings('db_limit', array(intval($start), intval($limit)) );
  }

  public function setQueryOrder( $orderBy ){
var_dump($orderBy);die('476 in _connect');
    if($orderBy[0] == '-'){
	  $orderBy = ltrim($orderBy,'-');
      $order = 'DESC';
    }else{
      $order = 'ASC';  
    }
	//security flaw.
	
    $this->setSettings('db_orderby', addslashes($orderBy));
	$this->setSettings('db_order', addslashes($order));
  }

  function _alterQueryWhat( $query ){
    die('deprecated?');
/*
    $db_what = $this->getSettings('db_where');
	if( $db_what !== array() ){
      return str_replace('(*)', $db_what, $query);
	}else{
      return $query;
    }
*/
  }

  function _alterQueryWhere( $query, $in=false ){
    $db_where = $this->getSettings('db_where');
	if( $in === false ){
      return str_replace('1=1', $db_where, $this->getSettings('baseQuery'));
	}else{
      if( !is_array($in) ){
        errorHandler::report(ERR_FATAL,'in parameter must be array in _alterQueryWhere');
	  }
      $searchTerm = '';
      foreach($in as $key=> $val){ // table, col=>array(vals)
        foreach($val as $k => $v){ // col, vals
          foreach($v as $_k => $_v) {
            if( isset($searchIn) ){ $searchIn.= ","; }else{$searchIn='';}
			if( is_int($_v) ) {
              $searchIn.= $_v;
			}else{
              $searchIn.= "'".addslashes($_v)."'";
			}
		  } // per search term
        } // per search col
		if( $searchTerm != '' ){ $searchTerm.= ' OR '; }
		$searchTerm .= addslashes($key).'.'.addslashes($k).' IN ('.$searchIn.')';
      } // per search table
      return str_replace('(1=1)', $searchTerm, $query);
	}
  }

  function _alterQueryParam($query, $param='*'){
  die('deprecated?');
/*
    if( gettype($param) == 'string' ){
      $param = array($param);
	}
	foreach($param as $key => $val){
      if(isset($_params)){$_params .= ',';}
	  $_params .= $val;
	}
    return str_replace('*', $_params, $query);	
*/
  }

  function _alterQueryLimit( $query ){
    $settings_db = $this->getSettings('db_limit');
    $min = intval($settings_db[0]);
    $max = intval($settings_db[1]);
	$a = preg_replace('/LIMIT [\d,]*/', "LIMIT {$min},{$max}", $query);
    if($a == $query && strstr($a,'LIMIT') === false){
	  return $query." LIMIT {$min},{$max}";
	}else{
	  if($a !== NULL){
	    return $a;
	  }else{
        errorHandler::report(ERR_FATAL,"Internal error discovered in {$this->getSettings('class')}::_alterQueryLimit");
		return false;
	  }
	}
  }

  function _alterQueryOrder( $query ){
    $order = $this->getSettings('db_order');
    if( stristr( $query, 'ORDER' ) == false ){
      $query = str_replace('LIMIT','ORDER BY '.current($order).' '.key($order).' LIMIT', $query);
	}
	return $query;
  }

  protected function _buildLike($val, $col, $like){
    if( $like === true && is_bool($like) ){
      $like = MATCH_ANY;
      errorHandler::report(ERR_NOTICE, "use of bool(true) in search is depricated.  Use const MATCH_ANY.");
    }
    $col = $this->getDataMap($col);
    $col = '`'.$col[0].'`.`'.$col[1].'`';
    #var_dump($col,$val);die();
    if( $like == MATCH_NONE){
		$opp = array("{$col} = ?",$val);
    }else{
    	$p_start = ( $like & MATCH_END ) ? '%' : '';
    	$p_end = ( $like & MATCH_START ) ? '%' : '';
    	if( is_int($val) ){
    	  $opp[0] = "{$col} LIKE ?";
    	  $opp[1] = $p_start.intval($val).$p_end;
    	}else{
	      $opp[0] = "{$col} LIKE ?";
	      $opp[1]= $p_start.addslashes($val).$p_end;
	    }
    }
	return (isset($opp)) ? $opp : false;
  }
  
  function _alterQueryLike( $query ){
  
  }

	function initDB($query = false){
		if($query==false){$query = $this->getSettings('baseQuery');}
    	$query = $this->_alterQueryLimit( $query );
    	$query = $this->_alterQueryOrder( $query );
		return connect::$_codex->initQuery($query);
	}

	/**
	 * Returns list of pages
	 * 
	 * @items string,array data item(s) from db call
	 * 
	 */

	function objectify($items){
		if( $this->isType('page') ){ return connect::$_codex->{$this->_classType}->objectify($items); }
		if( !is_array($items) ){$items = array($items);}
		$retVal = array();
		$this->_last = array();
		foreach($items as $key => $val){
			try{
				$_a = $this->getBase();
				$_a->fill($val);
				$_a->_setCache($_a);
			}catch(eFatal $e){
				return false;
			}

			// post build hook
			if( method_exists($_a,'postBuild') ){ $_a->postBuild(); }
			$retVal[] = $_a;
			$this->_all[] = $_a;
		}
		$this->_last = $retVal;
		// lazy load all of the mapped object data
		$this->runMaps(false,true);
		return $retVal;
	}

	/**
	 * 
	 * 
	 * 
	 */
	function runMaps($map = false, $lazy = false){
		if( $this->isType('page') ){ return connect::$_codex->{$this->_classType}->runMaps($map,$lazy); }
		$items = $this->_last;
			$mapData = array();
			foreach($this->_mapTable as $key => $val){
				if (($map == false || $map == $val['lib']) && ($val['lazy']==$lazy)){
					$mapData[] = $val;
				}
			}
		foreach($mapData as $key => $val){
			foreach($items as $k => $v){
				if( !isset($us) ){
					$mapCol = $v->attr($val['mapParent']);
					$us = connect::$_codex->$val['lib']->search($mapCol[0],$val['mapChild']);
				}
				foreach($us as $_k => $_v){
					$v->addMapChild($_v);
				}
			}
		}
	}

	/**
	 * 
	 * 
	 * 
	 */
	function getMap( $lib=false, $item=false ){
		if( $this->isType('page') ){ return connect::$_codex->{$this->_classType}->getMap($lib,$this); }
		if( $item == false ){ $item = $this; }
		if( isset( $item->_objMap[$lib] ) ){
			return $item->_objMap[$lib];
		}else{
			if( !isset($item->_objMap[$lib]) ){
				$item->runMaps($lib);
				if( isset($item->_objMap[$lib]) ){
					return $item->_objMap[$lib];
				}else{
					return array();
				}
			}else{
				if( $lib === false ){
					return $item->_objMap;
				}else{
					errorHandler::report(ERR_WARNING, 'requested library map does not exist');
					return array();
				}
			}
		}
	}


  function getSettings( $key=false, $squelch = false ){
    if( $this->isType('page') ){ return connect::$_codex->{$this->_classType}->getSettings($key, $squelch); }
    if( $key === false ){ return $this->_settings; }
#var_dump($this->_settings);die();
    if( isset($this->_settings[$key]) ){
      return $this->_settings[$key];
    }else{
		if(!$squelch){
			errorHandler::report(ERR_NOTICE, 'Settings attribute "'.$key.'" does not exist in '.$this->isType().'.');
		}
		return array();
    }
  }

 /**
  * setSettings
  *
  * key	(string, bool)		name of value you are setting.  If set to false, the
  							value passed int (val) will overwrite all settings
  * val	(string, array)		value you are assigning to the variable (key)
  * global	(bool)			if set to true, and object is a PAGE, the passwed
  *							arguments will be set acorss all PAGEs in that CHAPTER
  */
  
  
  function _pushArray($key, $val){
    if( is_array($this->_settings[$key]) ){
      foreach( $val as $k => $v ){
        if( isset($this->_settings[$key][$k]) && is_array($this->_settings[$key][$k]) ){
		#die('in');
          foreach( $v as $_k => $_v ){
            $tmp = array_search($v, $this->_settings[$key][$k]);
            if( $tmp !== false ){
              $this->_settings[$key][$k][$tmp] = $val;
            }else{
              $this->_settings[$key][$k] += $val;
            }
		  }
		}
        $tmp = array_search($val, $this->_settings[$key]);
        if( $tmp !== false ){
          $this->_settings[$key][$tmp] = $val;
        }else{
          $this->_settings[$key] += $val;
        }
      }
    }else{
      $this->_settings[$key] = $val;
    }
/*
    foreach( $val as $k => $v ){
      $tmp = array_search($val, $this->_settings[$key]);
      if( $tmp !== false ){
        $this->_settings[$key][$tmp] = $val;
      }else{
        $this->_settings[$key] += $val;
      }
    }
*/
  }

  /** _pushSetting
   * this needs to be redone?
   * this looks really complex.  Maybe moving to chapter only made it easier.
   */

/*
if val is array
	if isset


*/

  function _pushSetting( $key, $val ){
      if( $key === false ){
	    $this->_settings = $val;
	  }else{
        if( isset($this->_settings[$key]) ){
          if( is_array($val) ){
		    //3.5
            if( is_array($this->_settings[$key]) ){
              foreach( $val as $k => $v ){
                if( isset($this->_settings[$key][$k]) && is_array($this->_settings[$key][$k]) ){
#if($key=='tables'){var_dump($k);}
                  if( is_array($v) ){
                    foreach( $v as $_k => $_v ){
                      $tmp = array_search($v, $this->_settings[$key][$k]);
                      if( $tmp !== false ){
                        $this->_settings[$key][$k][$tmp] = $val;
                      }else{
                        if( is_int($_k) ){
                          $this->_settings[$key][$k] += $val;
                        }else{
                          $this->_settings[$key][$k][$_k] = $_v;
                        }
                      }
		            }
                  }else{
                    $this->_settings[$key][$k][] = $v;
				      #var_dump($key.' : '.$k.' : '.$v); die();
                  }
		        }else{
                  $tmp = array_search($val, $this->_settings[$key]);
                  if( $tmp !== false ){
                    $this->_settings[$key][$tmp] = $val;
                  }else{
                    if( is_int($k) ){
                      $this->_settings[$key] = $val;
					  #var_dump($this->_settings[$key],$val);
#		  	  if($key == 'db_limit'){var_dump($this->_settings,$val);die('MEGATEST!!!');} ////////////////////////////////////////////
                    }else{
				      #var_dump($key.' : '.$k.' : '.$v);# die();
                      $this->_settings[$key][$k] = $v;                    
                    }
		  	  #if($key == 'db_limit'){var_dump($this->_settings,$val);die('MEGATEST!!!');} ////////////////////////////////////////////

                  }
                }
              }
            }else{
              $this->_settings[$key] = $val;
            }
		  }else{
		    //2.6
			if( is_array($this->_settings[$key]) ){
			  $tmp = array_search($val, $this->_settings[$key]);
			  if( $tmp !== false ){
			    $this->_settings[$key][$tmp] = $val;
			  }else{
			    $this->_settings[$key] += $val;
			  }
			}else{
              $this->_settings[$key] = $val;			  
			}
		  }
		}else{
          $this->_settings[$key] = $val;
		}
	  }
  }


  /** setSettings
   * looks good for now
   */
  function setSettings( $key, $val){
    if( $this->isType('chapter') ){
      $this->_pushSetting( $key, $val );
	}elseif( $this->isType('page') ){
      $obj = $this->_classType;
      return connect::$_codex->$obj->setSettings($key, $val);
	} 
  }
  
  public function walkSetting($val){
    #while($val = current($val)
  }
  
  function setSetting($key, $sec, $val = true ){
    if( $this->isType('page') ){
      // if we are calling from a page, feed upstream to chapter
      connect::$_codex->{$this->_classType}->setSetting($key, $sec, $val);
	}elseif( $this->isType('chapter') ){
      if( !isset($this->_settings[$key]) ){
	    $this->_settings[$key] = '';
	  }
	  if( !is_array($sec) ){
        $sec = explode('.',$sec);
      }
	  if( !isset($this->_settings[$key][$sec[0]]) ){
	    $this->_settings[$key][$sec[0]] = array();
	  }
	  if( !isset($this->_settings[$key][$sec[0]][$sec[1]]) ){
	    $this->_settings[$key][$sec[0]][$sec[1]] = $val;
	  }
	}
  }
    
  function setSetting2($key, $path, $val){
    if( $this->isType('page') ){ return connect::$_codex->{$this->_classType}->setSetting2($key, $path, $val); }
    if(!isset($this->_settings[$key])){$this->_settings[$key] = array();}	
	if( $path !== false ){
      if(!isset($this->_settings[$key][$path])){$this->_settings[$key][$path] = NULL;}
      if( is_array($val) && !isset($this->_settings[$key][$path][key($val)]) ){
        $this->_settings[$key][$path][key($val)] = current($val);
	    return true;
	  }
	  $this->_settings[$key][$path] = $val;
      return true;
	}else{
	  die('why do i do this? 762 in connect');
      if( is_array($val) && isset($this->_settings[$key][key($val)]) ){
        $this->_settings[$key][key($val)] = current($val);
	    return true;
	  }
	  $this->_settings[$key] = $val;
      return true;
    }
    return false;
  }
  

	/**
	 * 
	 * 
	 * 
	 */
	function _setCache($page){
		if( $this->isType('page') ){ return connect::$_codex->{$this->_classType}->_setCache($page); }
		foreach($this->getSettings('db_unique') as $key => $val){
			foreach($val as $k => $v){
				if(!isset($this->_cache[$key])){$this->_cache[$key] = array();}
				if(!isset($this->_cache[$key][$k])){$this->_cache[$key][$k] = array();}
				$this->_cache[$key][$k][$page->attr($key.'.'.$k)] = $page;				
			}
		}
	}

	/**
	 * 
	 * 
	 * 
	 */
	function _getCache($tableName, $colName, $val){
		if( $this->isType('page') ){ return connect::$_codex->{$this->_classType}->_getCache($tableName, $colName, $val); }
		if(isset($this->_cache[$tableName][$colName][$val])){ "from cache!\n";}
		return (isset($this->_cache[$tableName][$colName][$val])) ? $this->_cache[$tableName][$colName][$val] : false;
	}


  /**
   * 
   *  push to chapter
   */

  function setDataMap($table, $col){
    if( $this->isType('page') ){
      return connect::$_codex->{$this->_classType}->setDataMap($table, $col);
	}
    if( !isset($this->_colMap) ){
      $this->_colMap = array();
	}

	if( isset($this->_colMap[$col]) ){
      errorHandler::report(ERR_NOTICE,"column ({$col}) already mapped to table ({$table})");
      return false;
	}
	$this->_colMap[$col] = $table;
	return true;
  }

  /**
   * 
   *  push to chapter
   */ 

  public function getDataMap($key, $checkValid = true){
    if( $this->isType('page') ){
      return connect::$_codex->{$this->_classType}->getDataMap($key);
	}
    if(strstr($key,'.') === false){
      // Absolute db path not given.
      if( !isset($this->_colMap[$key]) ){
        errorHandler::report(ERR_NOTICE,"unknown col: $key");
		return false;
	  }
      if($this->_colMap[$key] == false){
        errorHandler::report(ERR_FATAL,"Requires absolute path to col: $key");
        return false;
	  }
	  $key = array($this->_colMap[$key],$key);
	}else{
      $key = explode('.',$key);
    }
#die('in');
    #if( !isset($this->_blankPage->_pubAttr[$key[0]][$key[1]]) && !isset($this->_prvAttr[$key[0]][$key[1]]) ){
    #if( $this->_blankPage->attr($key[0].'.'.$key[1]) == false ){
      // INVALID!
    #  return false;
	#}
    return $key;
  }

  /**
   * 
   *  push to chapter
   */ 

  public function isDataMap($key, $alertOnError = false){
    if( $this->isType('page') ){
      return connect::$_codex->{$this->_classType}->isDataMap($key);
    }
    if(strstr($key,'.') === false){
      // Absolute db path not given.
      if( !isset($this->_colMap[$key]) ){
        if($alertOnError){ errorHandler::report(ERR_NOTICE,"unknown col: $key"); }
		return false;
	  }
      if($this->_colMap[$key] == false){
        errorHandler::report(ERR_FATAL,"Requires absolute path to col: $key");
        return false;
	  }
	  $key = array($this->_colMap[$key],$key);
	}else{
      $key = explode('.',$key);
    }

  }
  
    
/*
 ** one method to rule them all
 *
 */
  public function getId( $asArray = false ){
    if( $this->isType('page') ){ return connect::$_codex->{$this->_classType}->getId($asArray); }
    $ids = $this->getSettings('db_uid');
    if( $asArray == true ){
      $id = $ids;
    }else{
      $id='';
      foreach($ids as $key=>$val){
        foreach($val as $k => $v){
          if($id!=''){$id.=':';}
          $id.=str_replace(':','',$key.'.'.$k);        
        }
      }
    }
	return $id;
  }
 
  protected function addMapChild( $obj ){
	if( !isset($this->_objMap[$obj->isClass()]) ){ $this->_objMap[$obj->isClass()] = array(); }
	$this->_objMap[$obj->isClass()][$obj->attr($obj->getId())] = $obj;
  }
/*  
  public function fetchMapChildren( $index = false ){
    if( $index !== false ){
      if( isset($this->_objMap[$index]) ){
        return $this->_objMap[$index];
	  }else{
        errorHandler::report(ERR_NOTICE, 'mapped library index does not exist');
		return array();
	  }
	}else{
      return $this->_objMap;	
	}
  }
*/
  function safeInt( $int ){
    if( is_string( $int ) && preg_match('/[^\d]/',$int) == 0 && $int != '' ){
	  return intval( $int );
	}else{
	  return $int;
	}
  }

  public function isType( $type=false ){
    if( $type === false ){
      return $this->_objType;
    }else{
      return ( $type == $this->_objType ) ? true : false;
    }
  }


}

?>