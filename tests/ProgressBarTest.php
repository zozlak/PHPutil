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

use zozlak\util\ProgressBar;

class ProgressBarTest extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void {
        
    }

    public function testInit() {
        $pb = new ProgressBar();
        $this->assertEquals(0, $pb->getN());
    }

    /**
     * @depends testInit
     */
    public function testGetN() {
        $step = 5;

        $pb = new ProgressBar(null, $step);

        for ($i = 1; $i <= 2 * $step; $i++) {
            $pb->next();
            $this->assertEquals($i, $pb->getN());
        }
    }

    /**
     * @depends testGetN
     */
    public function testOutput1() {
        $step = 5;
        $prefix = 'XXX';
        $pattern = '        [0-9]+[.][0-9][0-9] s    v: [0-9]+[.][0-9][0-9] rec/s    ETA: [?] s    memory: [1-9][0-9]* MB          ';

        $pb = new ProgressBar(null, $step, $prefix);
        $this->setOutputCallback(function($output) {
            return str_replace(chr(8), '', $output);
        });

        $output = $prefix;
        for ($i = 1; $i <= 2 * $step; $i++) {
            $pb->next();
            if ($i % $step == 0) {
                $output .= $i . $pattern;
            }
            $this->expectOutputRegex('|^' . $output . '|');
        }
    }

    /**
     * @depends testGetN
     */
    public function testOutput2() {
        $step = 5;
        $n = 100;
        $prefix = 'XXX';
        $pattern = '    [0-9]+[%]    [0-9]+[.][0-9][0-9] s    v: [0-9]+[.][0-9][0-9] rec/s    ETA: [0-9]+[.][0-9][0-9] s    memory: [1-9][0-9]* MB          ';

        $pb = new ProgressBar($n, $step, $prefix);
        $this->setOutputCallback(function($output) {
            return str_replace(chr(8), '', $output);
        });

        $output = $prefix;
        for ($i = 1; $i <= 2 * $step; $i++) {
            $pb->next();
            if ($i % $step == 0) {
                $output .= $i . $pattern;
            }
            $this->expectOutputRegex('|^' . $output . '|');
        }
    }

}
