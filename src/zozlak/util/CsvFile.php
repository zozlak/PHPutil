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
 * High level CSV processing interface. Deals with encoding, trimming values, 
 * skipping lines lacking columns, etc.
 */
class CsvFile {

    private $handle;
    private $delimiter;
    private $enclosure;
    private $escape;
    private $header;
    private $encoding;

    public function __construct(string $fileName, string $delimiter = ',',
                                string $enclosure = '"', string $escape = '"',
                                string $encoding = null) {
        if (!is_file($fileName)) {
            throw new \InvalidArgumentException('"' . $fileName . '" is not a file');
        }
        $this->handle = @fopen($fileName, 'r');
        if ($this->handle === false) {
            throw new \RuntimeException('Could not open "' . $fileName . '"');
        }
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape    = $escape;
        $this->encoding  = $encoding;
    }

    public function readHeader($trim = false): array {
        $this->header = fgetcsv($this->handle, 0, $this->delimiter, $this->enclosure, $this->escape);
        if ($this->header === false) {
            throw new \RuntimeException('Failed to read a header');
        }
        if ($trim) {
            $this->trim($this->header);
        }
        if ($this->encoding !== null) {
            $this->toUtf8($this->header);
        }
        return $this->header;
    }

    public function getHeader(): array {
        if ($this->header === null) {
            throw new \BadMethodCallException('Read header first');
        }
        return $this->header;
    }

    public function setHeader(array $header): void {
        $this->header = $header;
    }

    public function getLine(bool $trim = false, int $minColCount = null): array {
        do {
            $l = fgetcsv($this->handle, 0, $this->delimiter, $this->enclosure, $this->escape);
            if ($l === false) {
                return false;
            }
        } while ($minColCount > 0 && count($l) < $minColCount);
        if ($trim) {
            $this->trim($l);
        }
        if ($this->encoding !== null) {
            $this->toUtf8($l);
        }
        return $l;
    }

    public function setLine(int $offset): array {
        fseek($this->handle, 0);
        while ($offset > 0) {
            $l = fgetcsv($this->handle, 0, $this->delimiter, $this->enclosure, $this->escape);
            $offset--;
        }
        return $l;
    }

    public function __destruct() {
        fclose($this->handle);
    }

    private function trim(array &$l): void {
        foreach ($l as &$i) {
            $i = trim($i);
        }
        unset($i);
    }

    private function toUtf8(array &$l): void {
        foreach ($l as &$i) {
            $i = iconv($this->encoding, 'UTF-8', $i);
        }
        unset($i);
    }

}
