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

/**
 * Klasa oferująca konwersję dat w różnych formatach na postać RRRR-MM-DD
 * Obsługiwane formaty:
 * - R-M-D (gdy RR większy od 31)
 * - D-M-R (przyjmowany, gdy pierwszy numer <= 31)
 * - liczba sekund od 1582-10-14 (początek ery wg R konwertującego daty ze zbiorów danych SPSS)
 * - liczba sekund od 1970-01-01 (początek ery wg PHP, Unix-ów, itp.)
 * Obsługiwane są dowolne separatory pól niebędące cyframi
 */
class Date {

    static $spssBase = 12219379200; // różnica pomiędzy 1970-01-01, a 1582-10-14, czyli pomiędzy początkiem świata wg UNIX-a i wg R-a (przy wczytywaniu dat z SPSS-a)

    static public function getDate($date, $spss = true) {
        $date = trim($date);
        if ($date == '') {
            return null;
        }

        if (is_numeric($date)) {
            return date('Y-m-d', intval($date) - ($spss === true ? self::$spssBase : 0));
        }

        $date = preg_split('/[^0-9]/', $date);
        if (count($date) !== 3) {
            return null;
        }
        for ($i = 0; $i < 3; $i++) {
            if (intval($date[$i]) == 0) {
                return null;
            }
        }

        $month = intval($date[1]);
        if ($date[0] < 32) {
            $year = intval($date[2]);
            $day = intval($date[0]);
        } else {
            $year = intval($date[0]);
            $day = intval($date[2]);
        }
        if ($year < 100) {
            $tmp = date('Y');
            $treshold = $tmp % 100;
            $tmp = intval($tmp / 100) * 100;
            if ($year > $treshold) {
                $year -= 100;
            }
            $year += $tmp;
        }
        $date = \DateTime::createFromFormat('Y-n-j', $year . '-' . $month . '-' . $day);
        if ($date === false) {
            return null;
        }
        return $date->format('Y-m-d');
    }

}
