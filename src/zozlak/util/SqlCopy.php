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
 * PHP's PDO class doesn't support a streaming COPY support for Postgresql.
 * This class fills the gap allowing to provide data rows one by one.
 */
class SqlCopy {

    private $connection;
    private $options = ['null' => [], 'false' => [false], 'true' => []];

    /**
     * 
     * @param string $conString Postgresql connection string 
     *   (e.g. 'host=my.host port=5432 dbname=myDb user=me password=myPswd').
     *   PDO connection strings are also supported.
     * @param string $tableName table name
     * @param array $options an array providing special value (NULL, TRUE, 
     *   FALSE) mappings.
     * @param type $schema schema name - use if the table is not in the default 
     *   search path (which is typically equivalent to "not in the public schema")
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct(string $conString, string $tableName,
                                array $options = [], string $schema = 'public') {
        foreach ($options as $h => $i) {
            if (!isset($this->options[$h])) {
                throw new \InvalidArgumentException('Wrong options. Available options: ' . implode(', ', array_keys($this->options)));
            }
            if (!is_array($i)) {
                throw new \InvalidArgumentException('Options must be arrays');
            }
            $this->options[$h] = $i;
        }

        $conString        = str_replace('pgsql:', '', $conString); // pozbaw ew. dane w formacie PDO przedrostka typu bazy
        // pg_pconnect() nie przyspiesza operacji (najwyraźniej COPY i tak trzyma otwarte połączenie), 
        // natomiast uniemożliwia równoległe kopiowanie kilku tablic - stąd połącz za pomocą zwykłego pg_connect()
        $this->connection = @pg_connect($conString, \PGSQL_CONNECT_FORCE_NEW);
        if ($this->connection === false) {
            throw new \RuntimeException('Connection failed (' . $conString . ')');
        }
        $schema    = pg_escape_identifier($this->connection, $schema);
        $tableName = pg_escape_identifier($this->connection, $tableName);
        $res       = @pg_query("COPY " . $schema . "." . $tableName . " FROM stdin");
        if ($res === false) {
            throw new \RuntimeException('Failed to execute: COPY ' . $schema . '.' . $tableName . ' FROM stdin');
        }
    }

    /**
     * Ingests a row of data.
     * @param type $row row to be ingested into the database (an array or an 
     *   already properly escaped input row)
     * @return void
     * @throws \RuntimeException
     */
    public function insertRow($row): void {
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

    public function end(): void {
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

    private function escape(array &$row): string {
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
