<?php
class log_event extends base{

	function build( $id=false ){
		$table = __CLASS__;

		$this->addCol( $table, 'id',REGEX_INT_ID, (COL_AUTOINC|COL_UNIQUE_ID|COL_SEARCH_INT|COL_UNIQUE) );
		$this->addCol( $table, 'page', REGEX_INT_ID, (COL_REQUIRED) );
		$this->addCol( $table, 'change', REGEX_TEXT, (COL_REQUIRED) );
		$this->addCol( $table, 'user', REGEX_TEXT, (COL_REQUIRED) );

		$this->setSettings('baseQuery', "SELECT * FROM {$table} WHERE (1=1) LIMIT 0,100");
	}
}
?>