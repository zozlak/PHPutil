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

use zozlak\util\Date;

class DateTest extends \PHPUnit\Framework\TestCase {

    /**
     */
    public function testDate() {
        $data = array(
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
            '2010 04 08' => '2010-04-08',
            ' 2010 04 07 ' => '2010-04-07',
            ' 2010x04y06 ' => '2010-04-06',
        );
        foreach ($data as $input => $output) {
            $this->assertEquals($output, Date::getDate($input));
        }
        $this->assertEquals('1970-01-01', Date::getDate('0', false));
        $this->assertEquals('1970-01-02', Date::getDate('86400', false));
    }

}
