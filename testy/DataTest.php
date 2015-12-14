<?php
# aby wykonać testy należy wejść do katalogu wyższego poziomu i wykonać "phpunit testy"

require_once('Data.php');

class DataTest extends PHPUnit_Framework_TestCase {
	/**
	*/
	public function testData(){
		$testy = array(
			'2010-04-05' => '2010-04-05',
			'98-05-20' => '1998-05-20',
			'096-07-08' => '1996-07-08',
			'10-03-25' => '1925-03-10',
			'30-12-2006' => '2006-12-30',
			'29-12-06' => '2006-12-29',
			'30-12-25' => '1925-12-30',
			'98-3-2' => '1998-03-02',
			'98-1-32' => '1998-02-01', // mktime jest wyjątkowo odporne
			'0' => '1582-10-14',
			'86400' => '1582-10-15',
			'2010a-04-05' => null,
			'2010-04a-05' => null,
			'2010-04-05a' => null,
			'2010-04-05a' => null,
			'2010 04 08' => '2010-04-08',
			' 2010 04 07 ' => '2010-04-07',
			' 2010x04y06 ' => '2010-04-06',
		);
		foreach($testy as $we=>$wy){
			$this->assertEquals($wy, Data::zwrDate($we));
		}
		$this->assertEquals('1970-01-01', Data::zwrDate('0', false));
		$this->assertEquals('1970-01-02', Data::zwrDate('86400', false));	
	}
}	

