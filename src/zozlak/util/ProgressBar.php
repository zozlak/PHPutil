<?php

/*
 * The MIT License
 *
 * Copyright 2012-2016 zozlak.
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

namespace zozlak\util;

/**
 * Klasa realizująca wyświetlanie na konsoli paska postępu.
 * Obiekt klasy należy utworzyć w momencie, od którego ma nastąpić zliczanie czasu.
 * Każdy kolejny przetworzony rekord należy potwierdzić wywołaniem metody "nastepnyRekord()".
 */
class ProgressBar {

    private $start;
    private $N = 0;
    private $n = 0;
    private $step;
    private $charLen = 0; // liczba znaków w ostatnim komunikacie
    private $totalCount;
    private $len;

    /**
     * 
     * @param integer $recordsCount
     * @param integer $step
     * @param string $prefix
     * @throws Exception
     */
    public function __construct($recordsCount = null, $step = 1000, $prefix = "\t") {
        if ($recordsCount < 0) {
            throw new Exception('ujemna liczba rekordów');
        }
        $this->totalCount = $recordsCount;
        $this->step = $step;
        echo($prefix . str_repeat(" ", $this->len));
        $this->start = self::stopwatch();
    }

    /**
     * 
     */
    function next() {
        $this->n++;
        if ($this->n == $this->step || $this->n + $this->N == $this->totalCount) {
            $this->show();
        }
    }

    /**
     * 
     * @return integer
     */
    public function getN() {
        return $this->N + $this->n;
    }

    /**
     * 
     */
    public function finish() {
        $this->show();
    }

    /**
     * 
     */
    private function show() {
        $t = self::stopwatch();
        $this->N += $this->n;
        $this->n = 0;

        echo(str_repeat(chr(8), $this->charLen)); // wymaż poprzedni komunikat

        $percentage = "";
        $fromStart = $t - $this->start;
        $v = $this->N / $fromStart;
        $left = "?";

        if ($this->totalCount !== null) {
            $percentage = intval($this->N * 100 / $this->totalCount) . "%";
            $left = sprintf('%.2f', ($this->totalCount - $this->N) / $v);
        }

        $tmp = sprintf(
            '%d    %s    %.2f s    v: %.2f rec/s    ETA: %s s    memory: %d MB          ', 
            $this->N, $percentage, $fromStart, $v, $left, 
            intval(memory_get_usage(true) / 1024 / 1024)
        );
        $this->charLen = mb_strlen($tmp);
        echo($tmp);
    }

    /**
     * 
     * @return float
     */
    private static function stopwatch() {
        return microtime(true);
    }

}
