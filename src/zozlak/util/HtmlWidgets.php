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
 * Statyczna klasa ułatwiająca tworzenie kontrolek formularzy HTML
 */
class HtmlWidgets {

    public static function e($s): string {
        return htmlspecialchars($s);
    }

    public static function input(string $type, string $name, string $value,
                                 string $class = "", array $attr = []): void {
        $a = '';
        foreach ($attr as $h => $i) {
            $a .= ' ' . $h . '="' . self::e($i) . '"';
        }
        printf(
            '<input type="%s" name="%s" value="%s" class="%s" %s/>', self::e($type), self::e($name), self::e($value), self::e($class), $a
        );
    }

    public static function textarea(string $name, string $value,
                                    string $class = "", int $rows = 2,
                                    int $cols = 40): void {
        printf(
            '<textarea name="%s" rows="%d" cols="%d" class="%s">%s</textarea>', self::e($name), intval($rows), intval($cols), self::e($class), self::e($value)
        );
    }

    public static function checkbox(string $name, string $value,
                                    string $label = "", string $class = "",
                                    string $onclick = ""): void {
        printf(
            '<label><input type="checkbox" name="%s" %s class="%s" onclick="%s"/>%s</label>', self::e($name), $value ? 'checked="checked"' : '', self::e($class), self::e($onclick), $label
        );
    }

    public static function select(string $name, string $value, array $opts,
                                  string $type, string $class = "",
                                  string $onchange = ""): void {
        $multi = is_array($value);
        if (!$multi) {
            $value = [$value];
        }
        $o = '';
        foreach ($opts as $h => $i) {
            $w = $type == 'pary' ? $h : $i;
            $o .= sprintf(
                '<option value="%s" %s>%s</option>', self::e($w), in_array($w, $value, true) || $w != '' && in_array($w, $value) ? 'selected="selected"' : '', self::e($i)
            );
        }
        printf(
            '<select name="%s" class="%s" onchange="%s" %s>%s</select>', self::e($name) . ($multi ? '[]' : ''), self::e($class), self::e($onchange), $multi ? 'multiple="multiple"' : '', $o
        );
    }

    public static function radio(string $name, string $value, array $opts,
                                 string $type, string $class = "",
                                 string $onclick = "", bool $br = true): void {
        $o = '';
        foreach ($opts as $h => $i) {
            $w = $type == 'pary' ? $h : $i;
            $o .= sprintf(
                '<label><input type="radio" name="%s" class="%s" value="%s" %s onclick="%s"/>&nbsp;%s</label>%s', self::e($name), self::e($class), self::e($w), $value === $w || $w != '' && $value == $w ? 'checked="checked"' : '', self::e($onclick), self::e($i), $br ? '<br/>' : ''
            );
        }
        printf($o);
    }

    public static function button(string $value, string $onclick,
                                  string $class = ""): void {
        printf(
            '<button type="button" onclick="%s" class="%s">%s</button>', self::e($onclick), self::e($class), $value
        );
    }

    public static function submit(string $name, string $value,
                                  string $onclick = "", string $class = ""): void {
        printf(
            '<button type="submit" name="%s" onclick="%s" class="%s">%s</button>', self::e($name), self::e($onclick), self::e($class), $value
        );
    }

}
