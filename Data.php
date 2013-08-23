<?php
	/*
	 * Klasa oferująca konwersję dat w różnych formatach na postać RRRR-MM-DD
	 * Obsługiwane formaty:
	 * - R-M-D (gdy RR większy od 31)
	 * - D-M-R (przyjmowany, gdy pierwszy numer <= 31)
	 * - liczba sekund od 1582-10-14 (początek ery wg R konwertującego daty ze zbiorów danych SPSS)
	 * - liczba sekund od 1970-01-01 (początek ery wg PHP, Unix-ów, itp.)
	 * Obsługiwane są dowolne separatory pól niebędące cyframi
	 */
	class Data {
		static $korektaSPSS=12219379200; // różnica pomiędzy 1970-01-01, a 1582-10-14, czyli pomiędzy początkiem świata wg UNIX-a i wg R-a (przy wczytywaniu dat z SPSS-a)

		static public function zwrDate($data, $eraSPSS = true){
			if(is_numeric($data))
				return date('Y-m-d', intval($data) - ($eraSPSS === true ? self::$korektaSPSS : 0));
			
			$data = preg_split('/[^0-9]/', trim($data));
			if(count($data) != 3)
				return null;
			foreach($data as $i){
				if(intval($i) == 0)
					return null;
			}

			$mies = intval($data[1]);
			if($data[0] < 32){
				$rok = intval($data[2]);
				$dzien = intval($data[0]);
			}
			else{
				$rok = intval($data[0]);
				$dzien = intval($data[2]);
			}
			if($rok < 100){
				$tmp = date('Y');
				$granica = $tmp % 100;
				$tmp = intval($tmp / 100) * 100;
				if($rok > $granica)
					$rok -= 100;
				$rok += $tmp;
			}
			$data = DateTime::createFromFormat('Y-n-j', $rok.'-'.$mies.'-'.$dzien);
			if($data === false)
				return null;
			return $data->format('Y-m-d');
		}
	}
?>
