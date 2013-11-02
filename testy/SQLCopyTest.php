<?php
# aby wykonać testy należy wejść do katalogu wyższego poziomu i wykonać "phpunit testy"

	require_once('SQLCopy.php');
	
	class SQLCopyTest extends PHPUnit_Framework_TestCase {
		static private $polaczenie;
	
		static public function setUpBeforeClass() {
			exec('dropdb -U postgres test;
					createdb -U postgres -O zozlak test;');
			self::$polaczenie = new PDO('pgsql:user=zozlak dbname=test');
			self::$polaczenie->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		
		protected function setUp(){
			self::$polaczenie->exec("DROP TABLE IF EXISTS bbb");
			self::$polaczenie->exec("DROP TABLE IF EXISTS aaa");
			self::$polaczenie->exec("CREATE TABLE aaa (a int primary key, b text, c bool)");
			self::$polaczenie->exec("CREATE TABLE bbb (a int references aaa(a), b text, c bool, primary key(a, b))");
		}

		public static function tearDownAfterClass(){
		}

		/**
		* @expectedException SQLCopyException
		* @covers SQLCopy::__construct
		*/
		public function testConstruct(){
			$tmp = new SQLCopy('user=zozlak dbname=test', 'aaa');
			$tmp->zakoncz();
			$tmp = new SQLCopy('user=zozlak dbname=bazaKtoraNieIstnieje', 'aaa');
		}

		/**
		* @expectedException SQLCopyException
		* @covers SQLCopy::zakoncz
		* @covers SQLCopy::wstawWiersz
		*/
		public function testZakoncz(){
			$tmp = new SQLCopy('user=zozlak dbname=test', 'aaa');
			$tmp->zakoncz();
			$tmp->wstawWiersz("1\t\N\t\N\n");
		}

		/**
		* @covers SQLCopy::wstawWiersz
		*/
		public function testKopiujStringi(){
			$tmp = new SQLCopy('user=zozlak dbname=test', 'aaa');

			for($i=0; $i<100; $i++)
				$tmp->wstawWiersz($i."\t\N\t\N\n");
			for($i=100; $i<200; $i++)
				$tmp->wstawWiersz($i."\tąąą bbb\t\N\n");
			for($i=200; $i<300; $i++)
				$tmp->wstawWiersz($i."\t\ttrue\n");
			$tmp->zakoncz();
			
			$tmp=self::$polaczenie->query('SELECT count(*) FROM aaa ');
			$this->assertEquals(300, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query('SELECT count(*) FROM aaa WHERE b IS NULL');
			$this->assertEquals(100, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query("SELECT count(*) FROM aaa WHERE b='ąąą bbb'");
			$this->assertEquals(100, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query("SELECT count(*) FROM aaa WHERE b=''");
			$this->assertEquals(100, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query('SELECT count(*) FROM aaa WHERE c IS NULL');
			$this->assertEquals(200, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query('SELECT count(*) FROM aaa WHERE c=true');
			$this->assertEquals(100, $tmp->fetchColumn());
		}

		/**
		* @depends testKopiujStringi
		* @covers SQLCopy::wstawWiersz
		* @covers SQLCopy::wyparsuj
		*/
		public function testKopiujTablice(){
			$tmp = new SQLCopy('user=zozlak dbname=test', 'aaa');

			for($i=0; $i<100; $i++)
				$tmp->wstawWiersz(array($i, null, null));
			for($i=100; $i<200; $i++)
				$tmp->wstawWiersz(array($i, 'ąąą bbb', null));
			for($i=200; $i<300; $i++)
				$tmp->wstawWiersz(array($i, '', true));
			$tmp->zakoncz();
			
			$tmp=self::$polaczenie->query('SELECT count(*) FROM aaa ');
			$this->assertEquals(300, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query('SELECT count(*) FROM aaa WHERE b IS NULL');
			$this->assertEquals(100, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query("SELECT count(*) FROM aaa WHERE b='ąąą bbb'");
			$this->assertEquals(100, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query("SELECT count(*) FROM aaa WHERE b=''");
			$this->assertEquals(100, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query('SELECT count(*) FROM aaa WHERE c IS NULL');
			$this->assertEquals(200, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query('SELECT count(*) FROM aaa WHERE c=true');
			$this->assertEquals(100, $tmp->fetchColumn());
		}

		/**
		* @depends testKopiujTablice
		* @covers SQLCopy::wstawWiersz
		* @covers SQLCopy::wyparsuj
		*/
		public function testKopiujNietypoweTablice(){
			$tmp = new SQLCopy('user=zozlak dbname=test', 'aaa', array('null'=>array('', 'NA'), 'true'=>array('T', 'true')));

			for($i=0; $i<100; $i++)
				$tmp->wstawWiersz(array($i, 'NA', null));
			for($i=100; $i<200; $i++)
				$tmp->wstawWiersz(array($i, 'ąąą bbb', true));
			for($i=200; $i<300; $i++)
				$tmp->wstawWiersz(array($i, '', 'T'));
			for($i=300; $i<400; $i++)
				$tmp->wstawWiersz(array($i, null, 'true'));
			$tmp->zakoncz();
			
			$tmp=self::$polaczenie->query('SELECT count(*) FROM aaa ');
			$this->assertEquals(400, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query('SELECT count(*) FROM aaa WHERE b IS NULL');
			$this->assertEquals(300, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query("SELECT count(*) FROM aaa WHERE b='ąąą bbb'");
			$this->assertEquals(100, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query("SELECT count(*) FROM aaa WHERE b=''");
			$this->assertEquals(0, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query('SELECT count(*) FROM aaa WHERE c IS NULL');
			$this->assertEquals(100, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query('SELECT count(*) FROM aaa WHERE c=true');
			$this->assertEquals(300, $tmp->fetchColumn());
		}

		/**
		* @depends testKopiujNietypoweTablice
		* @covers SQLCopy::wstawWiersz
		* @covers SQLCopy::wyparsuj
		*/
		public function testKopiujNietypoweTabliceBledy(){
			$tmp = new SQLCopy('user=zozlak dbname=test', 'aaa');
			$tmp->wstawWiersz(array(1, '', 'Nie'));
			try{
				$tmp->zakoncz();
				throw new Exception('Brak wyjątku');
			}
			catch(SQLCopyException $e){
				$this->assertEquals($e->getCode(), SQLCopyException::BLAD_KONCZENIA);
			}
		}

		/**
		* @depends testKopiujTablice
		*/
		public function testSzybkosc(){
			$t = microtime(true);
			$tmp1 = new SQLCopy('user=zozlak dbname=test', 'aaa');
			for($i=0; $i<1000000; $i++)
				$tmp1->wstawWiersz(array($i, 'a', null));
			$tmp1->zakoncz();
			echo((microtime(true)-$t)."\n");
		}

		/**
		* @depends testKopiujTablice
		*/
		public function testKopiujRownolegle(){
			$tmp1 = new SQLCopy('user=zozlak dbname=test', 'aaa');
			$tmp2 = new SQLCopy('user=zozlak dbname=test', 'bbb'); // tablica z kluczem obcym do aaa

			for($i=0; $i<100; $i++)
				$tmp1->wstawWiersz(array($i, 'a', null));
			for($i=0; $i<100; $i++){
				$tmp2->wstawWiersz(array($i, 'a', null));
				$tmp2->wstawWiersz(array($i, 'b', null));
			}
			$tmp1->zakoncz();
			$tmp2->zakoncz();
			
			$tmp=self::$polaczenie->query('SELECT count(*) FROM aaa ');
			$this->assertEquals(100, $tmp->fetchColumn());
			$tmp=self::$polaczenie->query('SELECT count(*) FROM bbb');
			$this->assertEquals(200, $tmp->fetchColumn());
		}
	}	
?>
