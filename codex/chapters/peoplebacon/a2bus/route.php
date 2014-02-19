<?php
class a2bus_route extends base{
	function build( $id=false ){
		$this->addCol( __CLASS__, 'route_id', REGEX_INT, (COL_UNIQUE_ID|COL_SEARCH_INT) );
		$this->addCol( __CLASS__, 'route', REGEX_TEXT, (false) );

		$this->setSettings('baseQuery','SELECT * FROM '.__CLASS__.' WHERE (1=1) LIMIT 0,100');
	}
}

?>