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

namespace zozlak\util;

use OutOfRangeException;

/**
 * PHP ma to do siebie, że im dłuższy string, tym dłużej wykonywane są na nim operacje,
 * przy czym szybkość zależy też od pozycji w stringu (im bliżej początku stringu, tym szybciej).
 *
 * Wzrost czasu wykonania nosi znamiona wzrostu logarytmicznego, co z jednej strony daje nadzieje
 * na to, że wszystko się kiedyś skończy liczyć, ale z drugiej strony trzeba będzie swoje odczekać.
 * Proste testy funkcją mb_substr($tekst, $pozycja, 1) dały wyniki:
 * - dla $pozycja od 0 do 10k: ~56000 wywołań funkcji/s
 * - dla $pozycja od 10k do 20k: ~28000 wywołań funkcji/s
 * - dla $pozycja od 20k do 30k: ~19000 wywołań funkcji/s
 * - dla $pozycja od 30k do 40k: ~14000 wywołań funkcji/s
 * - dla $pozycja od 40k do 50k: ~11500 wywołań funkcji/s
 * - dla $pozycja od 50k do 60k:  ~9500 wywołań funkcji/s
 * - dla $pozycja od 60k do 70k:  ~8200 wywołań funkcji/s
 * - dla $pozycja od 70k do 80k:  ~7150 wywołań funkcji/s
 * - dla $pozycja od 80k do 90k:  ~6400 wywołań funkcji/s
 * - dla $pozycja od 90k do 100k: ~5750 wywołań funkcji/s
 *
 * Stąd, jeśli długi string przetwarzany jest liniowo i raz przetworzone znaki nie są już później
 * potrzebne, wtedy o wiele wydajniej jest go co jakiś czas obcinać z przodu, tak by nie operować
 * na zbyt dalekich indeksach.
 * Proste testy z funkcją mb_substr() dawały najlepsze rezultaty przy obcinaniu co ok. 1000 znaków.
 *
 */
class CharBuffer {

    private $buffer;
    private $offset = 0;
    private $length;

    public function __construct($string) {
        $this->buffer = $string;
        $this->length = mb_strlen($this->buffer);
    }

    public function getString(int $pos, int $count = 1): string {
        if ($pos >= $this->length) {
            throw new OutOfRangeException('Position out of string');
        } else if ($pos < $this->offset) {
            throw new OutOfRangeException('Position out of string');
        }
        return mb_substr($this->buffer, $pos - $this->offset, $count);
    }

    /**
     * Cuts off unneeded part of a string (`getString()` indexing remains 
     * unchanged).
     * @param int $pos cutting position
     * @param int $minDiff minimum difference from the last cut for the cut to 
     *   be performed (cutting to frequent degrades performance)
     * @return void
     */
    public function cut(int $pos, int $minDiff = 1000): void {
        if ($pos >= $this->offset + $minDiff) {
            $this->buffer = mb_substr($this->buffer, $pos - $this->offset);
            $this->offset = $pos;
        }
    }

    public function getLength(): int {
        return $this->length;
    }

}
