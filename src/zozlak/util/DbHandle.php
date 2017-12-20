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

use PDO;

/**
 * Description of DbHandle
 *
 * @author zozlak
 */
class DbHandle {

    /**
     *
     * @var \PDO
     */
    static private $PDO;
    
    /**
     *
     * @var string
     */
    static private $connParam;

    static public function setHandle($connParam, $persistent = false) {
        $attr = $persistent ? array(PDO::ATTR_PERSISTENT => true) : array();
        self::$connParam = $connParam;
        self::$PDO = new PDO($connParam, null, null, $persistent);
        self::$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$PDO->beginTransaction();
    }

    /**
     * 
     * @return \PDO
     */
    static public function getHandle() {
        return self::$PDO;
    }

    /**
     * 
     * @return string
     */
    static public function getConnHandle() {
        return self::$connParam;
    }

        /**
     * 
     */
    static public function commit() {
        if(self::$PDO->inTransaction()){
            self::$PDO->commit();
        }
    }

}
