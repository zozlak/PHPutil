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
 * Klasa uzupełniająca braki w bibliotece PDO uniemożliwające skorzystanie z SQL COPY
 *
 * Obsługuje dostarczanie kolejnych rekordów w postaci tablicy i dba o to, aby przetłumaczyć
 * zadane wartości na typy NULL/true/false SQL-a
 */
class SQLCopy {

    private $connection;
    private $options = array('null' => array(), 'false' => array(false), 'true' => array(true));

    public function __construct($conString, $tableName, array $options = array(), $schema = 'public') {
        foreach ($options as $h => $i) {
            if (!isset($this->options[$h])) {
                throw new \InvalidArgumentException('Wrong options. Available options: ' . implode(', ', array_keys($this->options)));
            }
            if (!is_array($i)) {
                throw new \InvalidArgumentException('Options must be arrays');
            }
            $this->options[$h] = $i;
        }

        $conString = str_replace('pgsql:', '', $conString); // pozbaw ew. dane w formacie PDO przedrostka typu bazy
        // pg_pconnect() nie przyspiesza operacji (najwyraźniej COPY i tak trzyma otwarte połączenie), 
        // natomiast uniemożliwia równoległe kopiowanie kilku tablic - stąd połącz za pomocą zwykłego pg_connect()
        $this->connection = @pg_connect($conString, \PGSQL_CONNECT_FORCE_NEW);
        if ($this->connection === false) {
            throw new \RuntimeException('Connection failed (' . $conString . ')');
        }
        $schema = pg_escape_identifier($this->connection, $schema);
        $tableName = pg_escape_identifier($this->connection, $tableName);
        $res = @pg_query("COPY " . $schema . "." . $tableName . " FROM stdin");
        if ($res === false) {
            throw new \RuntimeException('Failed to execute: COPY ' . $schema . '.' . $tableName . ' FROM stdin');
        }
    }

    public function insertRow($row) {
        if ($this->connection === null) {
            throw new \RuntimeException('Copying already ended. Create a new object');
        }
        if (is_array($row)) {
            $row = $this->escape($row);
        }
        $wynik = @pg_put_line($this->connection, $row);
        if ($wynik === false) {
            throw new \RuntimeException('Failed to insert the row');
        }
    }

    public function end() {
        if ($this->connection) {
            $res = @pg_put_line($this->connection, "\\.\n");
            if ($res === false) {
                $error = pg_last_error($this->connection);
            }
            $res = @pg_end_copy($this->connection);
            if ($res === false && !isset($error)) {
                $error = pg_last_error($this->connection);
            }
            $res = @pg_close($this->connection);
            if ($res === false && !isset($error)) {
                $error = pg_last_error($this->connection);
            }
            $this->connection = null;

            if (isset($error)) {
                throw new \RuntimeException('Failed to end copying: "' . $error . '"');
            }
        }
    }

    private function escape(array &$row) {
        foreach ($row as &$i) {
            if ($i === null || in_array($i, $this->options['null'], true)) {
                $i = '\N';
            } else if (in_array($i, $this->options['false'], true)) {
                $i = 'false';
            } else if (in_array($i, $this->options['true'], true)) {
                $i = 'true';
            }
        }
        unset($i);
        return implode("\t", $row) . "\n";
    }

}
