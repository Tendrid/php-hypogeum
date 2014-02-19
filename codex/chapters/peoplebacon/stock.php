<?php
class stock extends base{
	function build( $id=false ){
		$this->addCol( __CLASS__, 'symbol', false, (COL_UNIQUE_ID|COL_SEARCH_STR) );
		$this->addCol( __CLASS__, 'lasttrade', false, (false) );
		$this->addCol( __CLASS__, 'date', false, (false) );
		$this->addCol( __CLASS__, 'time', false, (false) );
		$this->addCol( __CLASS__, 'change', false, (false) );
		$this->addCol( __CLASS__, 'open', false, (false) );
		$this->addCol( __CLASS__, 'high', false, (false) );
		$this->addCol( __CLASS__, 'low', false, (false) );
		$this->addCol( __CLASS__, 'someint', false, (false) );
		$this->addCol( __CLASS__, 'dt', false, (false) );

		$this->setSettings('baseQuery','SELECT * FROM '.__CLASS__.' WHERE (1=1) order by dt desc LIMIT 0,1000');
	}

	function fromWeb(){
		$url = 'http://download.finance.yahoo.com/d/quotes.csv?s=IBM&f=sl1d1t1c1ohgv&e=.csv';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$j = curl_exec($ch);
		curl_close($ch);

		$val = str_getcsv($j);
		$out = array(	'symbol'=>$val[0],
						'lasttrade'=>$val[1],
						'date'=>$val[2],
						'time'=>$val[3],
						'change'=>$val[4],
						'open'=>$val[5],
						'high'=>$val[6],
						'low'=>$val[7],
						'someint'=>$val[8],
						'dt'=>mktime());
		return array($out);
	}
}

?>