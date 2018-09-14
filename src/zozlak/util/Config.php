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

use Iterator;

/**
 * Description of Config
 *
 * @author zozlak
 */
class Config implements Iterator {

    private $config = array();

    public function __construct($path, $sections = false) {
        $this->config = parse_ini_file($path, $sections);
    }

    public function get($key, $silent = false) {
        if ($silent && !isset($this->config[$key])) {
            return null;
        }
        return $this->config[$key];
    }

    public function set($key, $value) {
        $overwr = array_key_exists($key, $this->config);
        $this->config[$key] = $value;
        return $overwr;
    }

    public function overwrite($config) {
        $this->config = $config;
    }
    
    public function current() {
        return current($this->config);
    }

    public function key() {
        return key($this->config);
    }

    public function next() {
        next($this->config);
    }

    public function rewind() {
        reset($this->config);
    }

    public function valid() {
        return key($this->config) !== null;
    }

}
