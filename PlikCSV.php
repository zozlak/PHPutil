<?php
/*
	Copyright 2012-2013 Mateusz Żółtak

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

    Niniejszy program jest wolnym oprogramowaniem; możesz go
    rozprowadzać dalej i/lub modyfikować na warunkach Mniej Powszechnej
    Licencji Publicznej GNU, wydanej przez Fundację Wolnego
    Oprogramowania - według wersji 3 tej Licencji lub (według twojego
    wyboru) którejś z późniejszych wersji.

    Niniejszy program rozpowszechniany jest z nadzieją, iż będzie on
    użyteczny - jednak BEZ JAKIEJKOLWIEK GWARANCJI, nawet domyślnej
    gwarancji PRZYDATNOŚCI HANDLOWEJ albo PRZYDATNOŚCI DO OKREŚLONYCH
    ZASTOSOWAŃ. W celu uzyskania bliższych informacji sięgnij do
    Powszechnej Licencji Publicznej GNU.

    Z pewnością wraz z niniejszym programem otrzymałeś też egzemplarz
    Powszechnej Licencji Publicznej GNU (GNU General Public License);
    jeśli nie - napisz do Free Software Foundation, Inc., 59 Temple
    Place, Fifth Floor, Boston, MA  02110-1301  USA

 */

	class PlikCSV {
		private $uchwyt;
		private $separator;
		private $tekst;
		private $ucieczka;
		private $naglowek;
		private $kodowanie;
		
		public function __construct($nazwaPliku, $separator=',', $tekst='"', $ucieczka='"', $kodowanie=null){
			if(!is_file($nazwaPliku))
				throw new PlikCSVException('"'.$nazwaPliku.'" nie jest plikiem', PlikCSVException::BRAK_PLIKU);
			$this->uchwyt = @fopen($nazwaPliku, 'r');
			if($this->uchwyt === false)
				throw new PlikCSVException('Nie udało się otworzyć pliku "'.$nazwaPliku.'"', PlikCSVException::BLAD_OTWARCIA_PLIKU);
			$this->separator = $separator;
			$this->tekst = $tekst;
			$this->ucieczka = $ucieczka;
			$this->kodowanie = $kodowanie;
		}
		
		public function wczytajNaglowek($trim=false){
			$this->naglowek = fgetcsv($this->uchwyt, 0, $this->separator, $this->tekst, $this->ucieczka);
			if($this->naglowek === false)
				throw new PlikCSVException('Nie udało się wczytać nagłówka', PlikCSVException::BLAD_WCZYTANIA_NAGLOWKA);
			if($trim)
				$this->przytnij($this->naglowek);
			if($this->kodowanie !== null)
				$this->konwertuj($this->naglowek);
			return $this->naglowek;
		}
		public function zwrNaglowek(){
			if($this->naglowek === null){
				throw new PlikCSVException('Nie wczytano nagłówka', PlikCSVException::NIE_WCZYTANO_NAGLOWKA);
			}
			return $this->naglowek;
		}
		public function ustawNaglowek($naglowek){
			$this->naglowek = $naglowek;
		}
		
		public function zwrLinie($trim=false, $minKolumn=null){
			do{
				$l = fgetcsv($this->uchwyt, 0, $this->separator, $this->tekst, $this->ucieczka);
				if($l === false)
					return false;
			}while($minKolumn > 0 && count($l) < $minKolumn);
			if($trim)
				$this->przytnij($l);
			if($this->kodowanie !== null)
				$this->konwertuj($l);
			return $l;
		}
		
		public function ustawLinie($offset){
			fseek($this->uchwyt, 0);
			while($offset > 0){
				$l = fgetcsv($this->uchwyt, 0, $this->separator, $this->tekst, $this->ucieczka);
				$offset--;
			}
			return $l;
		}
		
		public function __destruct(){
			fclose($this->uchwyt);
		}
		
		private function przytnij(array &$l){
			foreach($l as &$i){
				$i = trim($i);
			}
			unset($i);
		}
		
		private function konwertuj(array &$l){
			foreach($l as &$i){
				$i = iconv($this->kodowanie, 'UTF-8', $i);
			}
			unset($i);
		}
	}
	
	class PlikCSVException extends Exception{
		const BRAK_PLIKU = 1;
		const BLAD_OTWARCIA_PLIKU = 2;
		const BLAD_WCZYTANIA_NAGLOWKA = 3;
		const NIE_WCZYTANO_NAGLOWKA = 4;
	}
?>
