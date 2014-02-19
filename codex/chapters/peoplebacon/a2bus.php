<?php
class a2bus extends base{
	function build( $id=false ){
		$this->addCol( __CLASS__, 'id', REGEX_INT, (COL_UNIQUE_ID|COL_SEARCH_INT) );
		$this->addCol( __CLASS__, 'lat', REGEX_TEXT, (false) );
		$this->addCol( __CLASS__, 'lon', REGEX_TEXT, (false) );
		$this->addCol( __CLASS__, 'heading', REGEX_INT, (false) );
		$this->addCol( __CLASS__, 'routeid', REGEX_INT, (false) );
		$this->addCol( __CLASS__, 'dt', REGEX_INT, (false) );

		$this->setSettings('baseQuery','SELECT * FROM '.__CLASS__.' WHERE (1=1) order by dt DESC LIMIT 0,100');
	}	
}

?>