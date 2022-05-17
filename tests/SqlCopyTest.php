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

    static private $connStr = "host=127.0.0.1 port=5432 user=postgres password=CmPUpKTW2e";
    static private $connection;

    static public function setUpBeforeClass(): void {
        self::$connection = new \PDO("pgsql: " . self::$connStr);
        self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    protected function setUp(): void {
        self::$connection->exec("DROP TABLE IF EXISTS bbb");
        self::$connection->exec("DROP TABLE IF EXISTS aaa");
        self::$connection->exec("CREATE TABLE aaa (a int primary key, b text, c bool)");
        self::$connection->exec("CREATE TABLE bbb (a int references aaa(a), b text, c bool, primary key(a, b))");
    }

    public static function tearDownAfterClass(): void {
        self::$connection = null;
    }

    /**
     * @covers \zozlak\util\SqlCopy::__construct
     */
    public function testConstruct() {
        $tmp = new SqlCopy(self::$connStr, 'aaa');
        $tmp->end();
        
        $this->expectException(\RuntimeException::class);
        $tmp = new SqlCopy(self::$connStr . " dbname=bazaKtoraNieIstnieje", 'aaa');
    }

    /**
     * @covers \zozlak\util\SqlCopy::end
     * @covers \zozlak\util\SQLCopy::insertRow
     */
    public function testEnd() {
        $tmp = new SqlCopy(self::$connStr, 'aaa');
        $tmp->end();
        $this->expectException(\RuntimeException::class);
        $tmp->insertRow("1\t\N\t\N\n");
    }

    /**
     * @covers \zozlak\util\SqlCopy::insertRow
     */
    public function testCopyStrings() {
        $tmp = new SqlCopy(self::$connStr, 'aaa');

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
        $tmp = new SqlCopy(self::$connStr, 'aaa');

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
        $tmp = new SqlCopy(self::$connStr, 'aaa', array('null' => array('', 'NA'),
            'true' => array('T', 'true')));

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
        $this->expectException(\RuntimeException::class);
        $tmp = new SqlCopy(self::$connStr, 'aaa');
        $tmp->insertRow(array(1, '', 'Nie'));
        $tmp->end();
    }

    /**
     * @depends testCopyTables
     */
    public function testSpeed() {
        $t    = microtime(true);
        $tmp1 = new SqlCopy(self::$connStr, 'aaa');
        for ($i = 0; $i < 100000; $i++) {
            $tmp1->insertRow(array($i, 'a', null));
        }
        $tmp1->end();
        echo((microtime(true) - $t) . "\n");

        $tmp = self::$connection->query('SELECT count(*) FROM aaa');
        $this->assertEquals(100000, $tmp->fetchColumn());
    }

    /**
     * @depends testCopyTables
     */
    public function testParallelCopy() {
        $tmp1 = new SqlCopy(self::$connStr, 'aaa');
        $tmp2 = new SqlCopy(self::$connStr, 'bbb'); // tablica z kluczem obcym do aaa

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
        self::$connection->exec("CREATE SCHEMA IF NOT EXISTS s");
        self::$connection->exec("DROP TABLE IF EXISTS s.aaa");
        self::$connection->exec("CREATE TABLE s.aaa (a int primary key, b text, c bool)");
        $tmp1 = new SqlCopy(self::$connStr, 'aaa', array(), 's');
        for ($i = 0; $i < 100; $i++) {
            $tmp1->insertRow(array($i, 'a', null));
        }
        $tmp1->end();
        $tmp = self::$connection->query('SELECT count(*) FROM s.aaa');
        $this->assertEquals(100, $tmp->fetchColumn());
    }
}
