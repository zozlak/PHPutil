<?php

/*
 * The MIT License
 *
 * Copyright 2016 zozlak.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace tests;

use zozlak\util\SqlCopy;

class SqlCopyTest extends \PHPUnit\Framework\TestCase {

    static private $connection;

    static public function setUpBeforeClass() {
        exec('
			dropdb test;
			createdb test;
		');
        self::$connection = new \PDO('pgsql:dbname=test');
        self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    protected function setUp() {
        self::$connection->exec("DROP TABLE IF EXISTS bbb");
        self::$connection->exec("DROP TABLE IF EXISTS aaa");
        self::$connection->exec("CREATE TABLE aaa (a int primary key, b text, c bool)");
        self::$connection->exec("CREATE TABLE bbb (a int references aaa(a), b text, c bool, primary key(a, b))");
    }

    public static function tearDownAfterClass() {
        self::$connection = null;
        exec('dropdb test');
    }

    /**
     * @expectedException \RuntimeException
     * @covers \zozlak\util\SqlCopy::__construct
     */
    public function testConstruct() {
        $tmp = new SqlCopy('dbname=test', 'aaa');
        $tmp->end();
        $tmp = new SqlCopy('user=zozlak dbname=bazaKtoraNieIstnieje', 'aaa');
    }

    /**
     * @expectedException \RuntimeException
     * @covers \zozlak\util\SqlCopy::end
     * @covers \zozlak\util\SQLCopy::insertRow
     */
    public function testEnd() {
        $tmp = new SqlCopy('dbname=test', 'aaa');
        $tmp->end();
        $tmp->insertRow("1\t\N\t\N\n");
    }

    /**
     * @covers \zozlak\util\SqlCopy::insertRow
     */
    public function testCopyStrings() {
        $tmp = new SqlCopy('dbname=test', 'aaa');

        for ($i = 0; $i < 100; $i++) {
            $tmp->insertRow($i . "\t\N\t\N\n");
        }
        for ($i = 100; $i < 200; $i++) {
            $tmp->insertRow($i . "\tąąą bbb\t\N\n");
        }
        for ($i = 200; $i < 300; $i++) {
            $tmp->insertRow($i . "\t\ttrue\n");
        }
        $tmp->end();

        $tmp = self::$connection->query('SELECT count(*) FROM aaa ');
        $this->assertEquals(300, $tmp->fetchColumn());
        $tmp = self::$connection->query('SELECT count(*) FROM aaa WHERE b IS NULL');
        $this->assertEquals(100, $tmp->fetchColumn());
        $tmp = self::$connection->query("SELECT count(*) FROM aaa WHERE b='ąąą bbb'");
        $this->assertEquals(100, $tmp->fetchColumn());
        $tmp = self::$connection->query("SELECT count(*) FROM aaa WHERE b=''");
        $this->assertEquals(100, $tmp->fetchColumn());
        $tmp = self::$connection->query('SELECT count(*) FROM aaa WHERE c IS NULL');
        $this->assertEquals(200, $tmp->fetchColumn());
        $tmp = self::$connection->query('SELECT count(*) FROM aaa WHERE c=true');
        $this->assertEquals(100, $tmp->fetchColumn());
    }

    /**
     * @depends testCopyStrings
     * @covers \zozlak\util\SqlCopy::insertRow
     * @covers \zozlak\util\SqlCopy::escape
     */
    public function testCopyTables() {
        $tmp = new SqlCopy('dbname=test', 'aaa');

        for ($i = 0; $i < 100; $i++) {
            $tmp->insertRow(array($i, null, null));
        }
        for ($i = 100; $i < 200; $i++) {
            $tmp->insertRow(array($i, 'ąąą bbb', null));
        }
        for ($i = 200; $i < 300; $i++) {
            $tmp->insertRow(array($i, '', true));
        }
        $tmp->end();

        $tmp = self::$connection->query('SELECT count(*) FROM aaa ');
        $this->assertEquals(300, $tmp->fetchColumn());
        $tmp = self::$connection->query('SELECT count(*) FROM aaa WHERE b IS NULL');
        $this->assertEquals(100, $tmp->fetchColumn());
        $tmp = self::$connection->query("SELECT count(*) FROM aaa WHERE b='ąąą bbb'");
        $this->assertEquals(100, $tmp->fetchColumn());
        $tmp = self::$connection->query("SELECT count(*) FROM aaa WHERE b=''");
        $this->assertEquals(100, $tmp->fetchColumn());
        $tmp = self::$connection->query('SELECT count(*) FROM aaa WHERE c IS NULL');
        $this->assertEquals(200, $tmp->fetchColumn());
        $tmp = self::$connection->query('SELECT count(*) FROM aaa WHERE c=true');
        $this->assertEquals(100, $tmp->fetchColumn());
    }

    /**
     * @depends testCopyTables
     * @covers \zozlak\util\SqlCopy::insertRow
     * @covers \zozlak\util\SqlCopy::escape
     */
    public function testCopyUnusualTables() {
        $tmp = new SqlCopy('dbname=test', 'aaa', array('null' => array('', 'NA'), 'true' => array('T', 'true')));

        for ($i = 0; $i < 100; $i++) {
            $tmp->insertRow(array($i, 'NA', null));
        }
        for ($i = 100; $i < 200; $i++) {
            $tmp->insertRow(array($i, 'ąąą bbb', true));
        }
        for ($i = 200; $i < 300; $i++) {
            $tmp->insertRow(array($i, '', 'T'));
        }
        for ($i = 300; $i < 400; $i++) {
            $tmp->insertRow(array($i, null, 'true'));
        }
        $tmp->end();

        $tmp = self::$connection->query('SELECT count(*) FROM aaa ');
        $this->assertEquals(400, $tmp->fetchColumn());
        $tmp = self::$connection->query('SELECT count(*) FROM aaa WHERE b IS NULL');
        $this->assertEquals(300, $tmp->fetchColumn());
        $tmp = self::$connection->query("SELECT count(*) FROM aaa WHERE b='ąąą bbb'");
        $this->assertEquals(100, $tmp->fetchColumn());
        $tmp = self::$connection->query("SELECT count(*) FROM aaa WHERE b=''");
        $this->assertEquals(0, $tmp->fetchColumn());
        $tmp = self::$connection->query('SELECT count(*) FROM aaa WHERE c IS NULL');
        $this->assertEquals(100, $tmp->fetchColumn());
        $tmp = self::$connection->query('SELECT count(*) FROM aaa WHERE c=true');
        $this->assertEquals(300, $tmp->fetchColumn());
    }

    /**
     * @depends testCopyUnusualTables
     * @covers \zozlak\util\SqlCopy::insertRow
     * @covers \zozlak\util\SqlCopy::escape
     */
    public function testCopyUnusualTablesErrors() {
        $tmp = new SqlCopy('dbname=test', 'aaa');
        $tmp->insertRow(array(1, '', 'Nie'));
        try {
            $tmp->end();
            throw new \Exception('Brak wyjątku');
        } catch (\RuntimeException $e) {
            
        }
    }

    /**
     * @depends testCopyTables
     */
    public function testSpeed() {
        $t = microtime(true);
        $tmp1 = new SqlCopy('dbname=test', 'aaa');
        for ($i = 0; $i < 100000; $i++) {
            $tmp1->insertRow(array($i, 'a', null));
        }
        $tmp1->end();
        echo((microtime(true) - $t) . "\n");
    }

    /**
     * @depends testCopyTables
     */
    public function testParallelCopy() {
        $tmp1 = new SqlCopy('dbname=test', 'aaa');
        $tmp2 = new SqlCopy('dbname=test', 'bbb'); // tablica z kluczem obcym do aaa

        for ($i = 0; $i < 100; $i++) {
            $tmp1->insertRow(array($i, 'a', null));
        }
        for ($i = 0; $i < 100; $i++) {
            $tmp2->insertRow(array($i, 'a', null));
            $tmp2->insertRow(array($i, 'b', null));
        }
        $tmp1->end();
        $tmp2->end();

        $tmp = self::$connection->query('SELECT count(*) FROM aaa ');
        $this->assertEquals(100, $tmp->fetchColumn());
        $tmp = self::$connection->query('SELECT count(*) FROM bbb');
        $this->assertEquals(200, $tmp->fetchColumn());
    }

    /**
     * @depends testCopyTables
     */
    public function testCopyInSchema() {
        self::$connection->exec("CREATE SCHEMA s");
        self::$connection->exec("CREATE TABLE s.aaa (a int primary key, b text, c bool)");
        $tmp1 = new SqlCopy('dbname=test', 'aaa', array(), 's');
        for ($i = 0; $i < 100; $i++) {
            $tmp1->insertRow(array($i, 'a', null));
        }
        $tmp1->end();
    }

}
