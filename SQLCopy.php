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

		public function __construct($danePolaczenia, $tablica, array $opcje = array()){
			foreach($opcje as $h=>$i){
				if(!isset($this->opcje[$h])){
					throw new SQLCopyException('Niewłaściwa opcja. Dostępne opcje: '.implode(', ', array_keys($this->opcje)), SQLCopyException::NIEWLASCIWA_OPCJA);
				}
				if(!is_array($i)){
					throw new SQLCopyException('Opcje muszą być tablicami', SQLCopyException::NIEWLASCIWA_OPCJA);
				}
				$this->opcje[$h]=$i;
			}

			$danePolaczenia = str_replace('pgsql:', '', $danePolaczenia); // pozbaw ew. dane w formacie PDO przedrostka typu bazy
			// pg_pconnect() nie przyspiesza operacji (najwyraźniej COPY i tak trzyma otwarte połączenie), 
			// natomiast uniemożliwia równoległe kopiowanie kilku tablic - stąd połącz za pomocą zwykłego pg_connect()
			$this->polaczenie = @pg_connect($danePolaczenia, PGSQL_CONNECT_FORCE_NEW);
			if($this->polaczenie === false){
				throw new SQLCopyException('Nie udało się połączyć z bazą danych ('.$danePolaczenia.')', SQLCopyException::BLAD_POLACZENIA);
			}
			$wynik = @pg_query("COPY ".pg_escape_identifier($this->polaczenie, $tablica)." FROM stdin");
			if($wynik === false){
				throw new SQLCopyException('Nie udało się wykonać polecenia COPY', SQLCopyException::BLAD_ROZPOCZECIA);
			}
		}
		
		public function wstawWiersz($wiersz){
			if($this->polaczenie === null){
				throw new SQLCopyException('Wstawianie zostało zakończone. Stwórz nowy obiekt klasy.', SQLCopyException::KOPIOWANIE_ZAKONCZONE);
			}
			if(is_array($wiersz)){
				$wiersz=$this->wyparsuj($wiersz);
			}
			$wynik = @pg_put_line($this->polaczenie, $wiersz);
			if($wynik === false){
				throw new SQLCopyException('Nie udało się wstawić wiersza', SQLCopyException::BLAD_WSTAWIANIA);
			}
		}
		
		public function zakoncz(){
			if($this->polaczenie){
				$wynik = @pg_put_line($this->polaczenie, "\\.\n");
				if($wynik === false){
					$blad = pg_last_error($this->polaczenie);
				}
				$wynik = @pg_end_copy($this->polaczenie);
				if($wynik === false && !isset($blad)){
					$blad = pg_last_error($this->polaczenie);
				}
				$wynik = @pg_close($this->polaczenie);
				if($wynik === false && !isset($blad)){
					$blad = pg_last_error($this->polaczenie);
				}
				$this->polaczenie = null;
				
				if(isset($blad)){
					throw new SQLCopyException('Nie udało się poprawnie zakończyć kopiowania - "'.$blad.'"', SQLCopyException::BLAD_KONCZENIA);
				}
			}
		}
		
		private function wyparsuj(array &$wiersz){
			foreach($wiersz as &$i){
				if($i===null || in_array($i, $this->opcje['null'], true)){
					$i = '\N';
				}
				else if(in_array($i, $this->opcje['false'], true)){
					$i = 'false';
				}
				else if(in_array($i, $this->opcje['true'], true)){
					$i = 'true';
				}
			}
			unset($i);
			return implode("\t", $wiersz)."\n";
		}
	}

	class SQLCopyException extends Exception{
		const NIEWLASCIWA_OPCJA = 1;
		const KOPIOWANIE_ZAKONCZONE = 2;
		const BLAD_ROZPOCZECIA = 3;
		const BLAD_POLACZENIA = 4;
		const BLAD_WSTAWIANIA = 5;
		const BLAD_KONCZENIA = 6;
	}
?>
