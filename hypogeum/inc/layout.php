<?
define( "SMALL", 1 );
define( "MEDIUM", 2 );
define( "LARGE", 3 );

define( 'DATE_SHORT', 'M j, Y g:i A');
define( 'DATE_LONG', 'M jS, Y \a\\t g:i A');

define('TEMPLATE_LOADING', '<div class="loading">Loading...</div>' );
define('TEMPLATE_NO_RESULTS', '<div class="noResults">No Results :(</div>' );

define('JSON',0);
define('XML',1);
define('CSS',2);

class _coreLayout extends connect{

  public function draw($size=false, $type=false, $return=false){
    if( $type==false ){
      $m = '_draw_'.$this->_classType;
	}else{
	  $m = '_draw_'.$type;
	}
	if($size < SMALL || $size > LARGE){
	  $size = MEDIUM;
	}
    if( method_exists($this,$m) ){
      if( $return == false ){
        echo $this->$m($size);
      }else{
        return $this->$m($size);
      }
	}else{
	  errorHandler::report(ERR_WARNING, 'Draw method not yet created for '.$this->_classType);
	  return false;
	}
  }
  
	public function feed($type=JSON){
		if($this->isType('page') ){
			$out = array($this->attr());
		}else{
			$out = $this->attr();
		}
		$o = array();
		foreach( $out as $key => $val ){
			foreach( $val as $k => $v ){
				foreach( $v as $_k => $_v ){
					$o[$key][$_k] = $_v;
				}
			}
		}
		return json_encode($o);
	}


  function longDate($dt=false){
    if( $dt == false ){
	  if( $this->attr('dt') != NULL ){ $dt = $this->attr('dt'); }
	  if( $this->attr("{$this->getSettings('class')}_dt") != NULL ){ $dt = $this->attr("{$this->getSettings('class')}_dt"); }
	}
	if( $dt != false ){
      return date(DATE_LONG,$dt);
	}else{
	  return false;
	}
  }

  function shortDate($dt=false){
    if( $dt == false ){
	  if( $this->attr('dt') != NULL ){ $dt = $this->attr('dt'); }
	  if( $this->attr("{$this->getSettings('class')}_dt") != NULL ){ $dt = $this->attr("{$this->getSettings('class')}_dt"); }
	}
	if( $dt != false ){
      return date(DATE_SHORT,$dt);
	}else{
	  return false;
	}
  }

}

?>