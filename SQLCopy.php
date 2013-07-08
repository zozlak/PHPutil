<?php
	/*
	 * Klasa uzupełniająca braki w bibliotece PDO uniemożliwające skorzystanie z SQL COPY
	 *
	 * Obsługuje dostarczanie kolejnych rekordów w postaci tablicy i dba o to, aby przetłumaczyć
	 * zadane wartości na typy NULL/true/false SQL-a
	 */
	class SQLCopy {
		private $polaczenie;
		private $opcje = array('null'=>array(), 'false'=>array(false), 'true'=>array(true));

//null='', 'NA', null
//false='nie', 'N', false
//true='tak', 'T', true
		public function __construct($danePolaczenia, $tablica, array $opcje = array()){
			$this->polaczenie = @pg_pconnect($danePolaczenia, PGSQL_CONNECT_FORCE_NEW);
			if($this->polaczenie === false)
				throw new SQLCopyException('Nie udało się połączyć z bazą danych ('.$danePolaczenia.')');
			pg_query("COPY ".pg_escape_identifier($this->polaczenie, $tablica)." FROM stdin");
			foreach($opcje as $h=>$i){
				if(!isset($this->opcje[$h]))
					throw new SQLCopyException('Niewłaściwa opcja. Dostępne opcje: '.implode(', ', array_keys($this->opcje)));
				if(!is_array($i))
					throw new SQLCopyException('Opcje muszą być tablicami');
				$this->opcje[$h]=$i;
			}
		}
		
		public function wstawWiersz($wiersz){
			if($this->polaczenie === null)
				throw new SQLCopyException('Wstawianie zostało zakończone. Stwórz nowy obiekt klasy.');
			if(is_array($wiersz))
				$wiersz=$this->wyparsuj($wiersz);
			pg_put_line($this->polaczenie, $wiersz);
		}
		
		public function zakoncz(){
			pg_put_line($this->polaczenie, "\\.\n");
			pg_end_copy($this->polaczenie);
			pg_close($this->polaczenie);
			$this->polaczenie = null;
		}
		
		private function wyparsuj(array &$wiersz){
			foreach($wiersz as &$i){
				if($i===null || in_array($i, $this->opcje['null'], true))
					$i = '\N';
				else if(in_array($i, $this->opcje['false'], true))
					$i = 'false';
				else if(in_array($i, $this->opcje['true'], true))
					$i = 'true';
			}
			unset($i);
			return implode("\t", $wiersz)."\n";
		}
	}

	class SQLCopyException extends Exception{}
?>
