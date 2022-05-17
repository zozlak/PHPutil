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

use zozlak\util\CsvFile;

class CsvFileTest extends \PHPUnit\Framework\TestCase {

    static public function setUpBeforeClass(): void {
        
    }

    protected function setUp(): void {
        $txt = <<<EOT
a| b |c
1|x|%ą|ć %
3|y
3|z|%c%%cc %
EOT;
        file_put_contents('test.csv', iconv('UTF-8', 'WINDOWS-1250', $txt));
    }

    public static function tearDownAfterClass(): void {
       unlink('test.csv');
    }

    /**
     */
    public function testBasic() {
        $csv = new CsvFile('test.csv', '|', '%', '%', 'WINDOWS-1250');
        $csv->readHeader(true);
        $this->assertEquals(['a', 'b', 'c'], $csv->getHeader());
        $this->assertEquals([1, 'x', 'ą|ć '], $csv->getLine(false));
        $this->assertEquals([3, 'z', 'c%cc'], $csv->getLine(true, 3));
    }

}
