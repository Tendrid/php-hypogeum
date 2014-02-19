<?php
class sec extends base{
	function build( $id=false ){
		$this->addCol( __CLASS__, 'id', false, (COL_UNIQUE_ID) );
		$this->addCol( __CLASS__, 'title', false, (false) );
		$this->addCol( __CLASS__, 'link', false, (false) );
		$this->addCol( __CLASS__, 'summary', false, (false) );
		$this->addCol( __CLASS__, 'label', false, (false) );
		$this->addCol( __CLASS__, 'term', false, (false) );
		$this->setSettings('baseQuery','SELECT * FROM '.__CLASS__.' WHERE (1=1) LIMIT 0,100000');
	}

	function fromWeb(){
		$j = simplexml_load_file('http://sec.gov/cgi-bin/browse-edgar?action=getcurrent&type=&company=&dateb=&owner=include&start=0&count=100&output=atom');
		$retVal = array();
		foreach($j->entry as $key => $val){
			$obj = array();
			$obj['id'] = $val->id->__tostring();
			$obj['title'] = $val->title->__tostring();
			$obj['link'] = $val->link->attributes()->href->__tostring();
			$obj['summary'] = trim($val->summary->__tostring());
			$obj['label'] = $val->category->attributes()->label->__tostring();
			$obj['term'] = $val->category->attributes()->term->__tostring();
			$retVal[] = $obj;
		}
		return $retVal;
	}
}

?>