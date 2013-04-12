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

	/*
	 * Klasa realizująca wyświetlanie na konsoli paska postępu.
	 * Obiekt klasy należy utworzyć w momencie, od którego ma nastąpić zliczanie czasu.
	 * Każdy kolejny przetworzony rekord należy potwierdzić wywołaniem metody "nastepnyRekord()".
	 */
	class PasekPostepu {
		private $pocz;
		private $N=0;
		private $n=0;
		private $prog;
		private $cofnij=0; // liczba znaków w ostatnim komunikacie
		private $lRekordow;
		private $dl;
		
		public function __construct($lRekordow=null, $prog=1000, $prefiks="\t"){
			if($lRekordow<0)
				throw new Exception('ujemna liczba rekordów');
			$this->lRekordow=$lRekordow;
			$this->prog=$prog;
			echo($prefiks.str_repeat(" ", $this->dl));
			$this->pocz=self::stoper();
		}
		
		function nastepnyRekord(){
			$this->n++;
			if($this->n==$this->prog || $this->n+$this->N==$this->lRekordow){
				$this->wyswietl();
			}
		}
		
		public function zwrN(){
			return $this->N+$this->n;
		}
		
		private function wyswietl(){
			$t=self::stoper();
			$this->N+=$this->n;
			$this->n=0;
				
			echo(str_repeat(chr(8), $this->cofnij)); // wymaż poprzedni komunikat
				
			$procent="";
			$odPocz=$t-$this->pocz;
			$v=$this->N/$odPocz;
			$doKonca="?";

			if($this->lRekordow!==null){
				$procent=intval($this->N*100/$this->lRekordow)."%";
				$doKonca=sprintf('%.2f', ($this->lRekordow-$this->N)/$v);
			}

			$tmp=$this->N."    ".$procent."    ".sprintf('%.2f', $odPocz)." s    v: ".sprintf('%.2f', $v)." rek/s    ETA: ".$doKonca." s    pamięć: ".intval(memory_get_usage()/1024/1024)." MB          ";
			$this->cofnij=mb_strlen($tmp);
			echo($tmp);
		}
		
		private static function stoper(){
		    $czas=explode(" ", microtime());
		    return doubleval($czas[1].mb_substr($czas[0], 1));
		}
	}
?>
