<?php
class a2parking extends base{
	function build( $id=false ){
		$this->addCol( __CLASS__, 'id', false, (COL_AUTOINC|COL_UNIQUE_ID|COL_SEARCH_INT) );
		$this->addCol( __CLASS__, 'parking', REGEX_INT, (false) );
		$this->addCol( __CLASS__, 'dt', REGEX_INT, (false) );
		$this->addCol( __CLASS__, 'spots', REGEX_INT, (false) );
		$this->addCol( __CLASS__, 'title', REGEX_TEXT, (false) );
		$this->setSettings('baseQuery','SELECT * FROM '.__CLASS__.' WHERE (1=1) LIMIT 0,100');
	}

	function fromWeb(){
		$structures = connect::$_codex->parking->all();
		$struc = array();
		foreach($structures as $key => $val){
			$struc[$val->attr('title')] = $val->attr('id');
		}
		//var_dump($struc);
		$j = simplexml_load_file('http://a2dda.org/feed/parking-rss.php');
		$retVal = Array();
		$i=0;

		foreach($j->channel->item as $key => $val){
			$struct = ($val->title->__toString() == 'Forest Ave') ? 'Forest' : $val->title->__toString();
			$retVal[$i]['dt'] = strtotime($val->pubDate->__toString());
			$retVal[$i]['spots'] = intval($val->description->__toString());
			$retVal[$i]['title'] = $struct;
			$retVal[$i]['parking'] = $struc[$struct];
			$i++;
		}
		return $retVal;
	}

	function getDistinct(){
		$this->_query = 'select MAX(id) AS id, title, spots, dt FROM a2parking group by title order by dt';
		//$this->_query = 'select * from a2parking where id in(select MAX(id) AS id FROM a2parking group by title)';
		// 'select * from a2parking where id in(select MAX(id) AS id FROM a2parking group by title)';
		//select * from a2parking where id in(91035,91033,91032,91039,91037,87324,91034,84740,91036,91038)
		$objs = $this->_doSearch();
		$retVal = array();
		foreach($objs as $key => $val){
			$retVal[] = connect::$_codex->a2parking->create($val);
		}
		return $retVal;
	}
}

?>