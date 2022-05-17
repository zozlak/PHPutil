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

use BadMethodCallException;
use Iterator;

/**
 * Description of Config
 *
 * @author zozlak
 */
class Config implements Iterator {

    const NOTICE    = 1;
    const EXCEPTION = 2;
    const NULL      = 3;

    private $config = [];
    private $mode;

    public function __construct(string $path, bool $sections = false,
                                int $mode = self::NOTICE) {
        $this->config = parse_ini_file($path, $sections);
        $this->mode   = $mode;
    }

    public function get(string $key, int $mode = null) {
        $mode = $mode === null ? $this->mode : $mode;
        if (!in_array($mode, [self::NOTICE, self::EXCEPTION, self::NULL])) {
            throw new BadMethodCallException('Wrong mode');
        }

        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        switch ($mode) {
            case self::NULL:
                return null;
            case self::EXCEPTION:
                throw new BadMethodCallException('No such key');
            default:
                return $this->config[$key];
        }
    }

    public function set(string $key, string $value): bool {
        $overwr             = array_key_exists($key, $this->config);
        $this->config[$key] = $value;
        return $overwr;
    }

    public function overwrite(array $config): void {
        $this->config = $config;
    }

    public function current(): mixed {
        return current($this->config);
    }

    public function key(): mixed {
        return key($this->config);
    }

    public function next(): void {
        next($this->config);
    }

    public function rewind(): void {
        reset($this->config);
    }

    public function valid(): bool {
        return key($this->config) !== null;
    }

}
