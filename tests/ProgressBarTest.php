<?php
# aby wykonać testy należy wejść do katalogu wyższego poziomu i wykonać "phpunit testy"

require_once('PasekPostepu.php');

class PasekPostepuTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
	}

	public function testInit(){
		$pasekPostepu=new PasekPostepu();
		$this->assertEquals(0, $pasekPostepu->zwrN());
	}
	
	/**
	* @depends testInit
	*/
	public function testZwrN(){
		$prog=5;
		
		$pasekPostepu=new PasekPostepu(null, $prog);
		
		for($i=1; $i<=2*$prog; $i++){
			$pasekPostepu->nastepnyRekord();
			$this->assertEquals($i, $pasekPostepu->zwrN());
		}
	}

	/**
	* @depends testZwrN
	*/
	public function testOutput1(){
		$prog=5;
		$prefiks='XXX';
		$wzorzec='        [0-9]+[.][0-9][0-9] s    v: [0-9]+[.][0-9][0-9] rek/s    ETA: [?] s    pamięć: [1-9][0-9]* MB          ';
		
		$pasekPostepu=new PasekPostepu(null, $prog, $prefiks);
		$this->setOutputCallback(function($wyjscie){return str_replace(chr(8), '', $wyjscie);});
		
		$wyjscie=$prefiks;
		for($i=1; $i<=2*$prog; $i++){
			$pasekPostepu->nastepnyRekord();
			if($i%$prog==0)
				$wyjscie.=$i.$wzorzec;
			$this->expectOutputRegex('|^'.$wyjscie.'|');
		}
	}

	/**
	* @depends testZwrN
	*/
	public function testOutput2(){
		$prog=5;
		$n=100;
		$prefiks='XXX';
		$wzorzec='    [0-9]+[%]    [0-9]+[.][0-9][0-9] s    v: [0-9]+[.][0-9][0-9] rek/s    ETA: [0-9]+[.][0-9][0-9] s    pamięć: [1-9][0-9]* MB          ';
		
		$pasekPostepu=new PasekPostepu($n, $prog, $prefiks);
		$this->setOutputCallback(function($wyjscie){return str_replace(chr(8), '', $wyjscie);});
		
		$wyjscie=$prefiks;
		for($i=1; $i<=2*$prog; $i++){
			$pasekPostepu->nastepnyRekord();
			if($i%$prog==0)
				$wyjscie.=$i.$wzorzec;
			$this->expectOutputRegex('|^'.$wyjscie.'|');
		}
	}		
}

