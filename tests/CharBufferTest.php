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

use zozlak\util\CharBuffer;

class CharBufferTest extends \PHPUnit\Framework\TestCase {

    static public function setUpBeforeClass() {
        
    }

    protected function setUp() {
        
    }

    public static function tearDownAfterClass() {
        
    }

    public function testBasic() {
        $buf = new CharBuffer(str_repeat('a', 10000) . str_repeat('b', 10000));
        $this->assertEquals('a', $buf->getString(0));
        $this->assertEquals('ab', $buf->getString(9999, 2));
    }

    public function testCut() {
        $buf = new CharBuffer(str_repeat('a', 10000) . str_repeat('b', 10000));
        $buf->cut(9999);
        $this->assertEquals('ab', $buf->getString(9999, 2));
    }
    
    public function testException() {
        $this->expectException(\OutOfRangeException::class);
        $buf = new CharBuffer(str_repeat('a', 10000) . str_repeat('b', 10000));
        $buf->cut(9999);
        $buf->getString(9998);
    }
    
}
