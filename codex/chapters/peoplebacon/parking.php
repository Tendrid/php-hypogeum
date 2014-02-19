<?php
class parking extends base{
	function build( $id=false ){
		$this->addCol( __CLASS__, 'id', false, (COL_AUTOINC|COL_UNIQUE_ID|COL_SEARCH_INT) );
		$this->addCol( __CLASS__, 'spots', REGEX_INT, (false) );
		$this->addCol( __CLASS__, 'updatedt', REGEX_INT, (false) );
		$this->addCol( __CLASS__, 'title', REGEX_TEXT, (false) );
		$this->addCol( __CLASS__, 'lat', REGEX_TEXT, (false) );
		$this->addCol( __CLASS__, 'lon', REGEX_TEXT, (false) );

		$this->setSettings('baseQuery','SELECT * FROM '.__CLASS__.' WHERE (1=1) order by id LIMIT 0,100');

		//connect::$_codex->parking->map( 'a2parking', 'id', 'parking' ,true);
	}

}

?>