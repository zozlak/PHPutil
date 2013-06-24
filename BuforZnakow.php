<?php
	/*
	 * PHP ma to do siebie, że im dłuższy string, tym dłużej wykonywane są na nim operacje,
	 * przy czym szybkość zależy też od pozycji w stringu (im bliżej początku stringu, tym szybciej).
	 *
	 * Wzrost czasu wykonania nosi znamiona wzrostu logarytmicznego, co z jednej strony daje nadzieje
	 * na to, że wszystko się kiedyś skończy liczyć, ale z drugiej strony trzeba będzie swoje odczekać.
	 * Proste testy funkcją mb_substr($tekst, $pozycja, 1) dały wyniki:
	 * - dla $pozycja od 0 do 10k: ~56000 wywołań funkcji/s
	 * - dla $pozycja od 10k do 20k: ~28000 wywołań funkcji/s
	 * - dla $pozycja od 20k do 30k: ~19000 wywołań funkcji/s
	 * - dla $pozycja od 30k do 40k: ~14000 wywołań funkcji/s
	 * - dla $pozycja od 40k do 50k: ~11500 wywołań funkcji/s
	 * - dla $pozycja od 50k do 60k:  ~9500 wywołań funkcji/s
	 * - dla $pozycja od 60k do 70k:  ~8200 wywołań funkcji/s
	 * - dla $pozycja od 70k do 80k:  ~7150 wywołań funkcji/s
	 * - dla $pozycja od 80k do 90k:  ~6400 wywołań funkcji/s
	 * - dla $pozycja od 90k do 100k: ~5750 wywołań funkcji/s
	 *
	 * Stąd, jeśli długi string przetwarzany jest liniowo i raz przetworzone znaki nie są już później
	 * potrzebne, wtedy o wiele wydajniej jest go co jakiś czas obcinać z przodu, tak by nie operować
	 * na zbyt dalekich indeksach.
	 * Proste testy z funkcją mb_substr() dawały najlepsze rezultaty przy obcinaniu co ok. 1000 znaków.
	 *
	 */
	 
	 class BuforZnakow {
	 	private $bufor;
	 	private $offset=0;
	 	private $dl;
	 	
	 	public function __construct($string){
	 		$this->bufor=$string;
	 		$this->dl=mb_strlen($this->bufor);
	 	}
	 	
	 	public function zwrString($pozycja, $lZnakow=1){
	 		if($pozycja >= $this->dl)
	 			throw new Exception('Pozycja poza stringiem');
	 		else if($pozycja < $this->offset)
	 			throw new Exception('Pozycja przed offsetem');
	 		return mb_substr($this->bufor, $pozycja-$this->offset, $lZnakow);
	 	}
	 	
	 	public function skroc($pozycja, $minRoznica=1000){
	 		if($pozycja >= $this->offset+$minRoznica){
	 			$this->bufor=mb_substr($this->bufor, $pozycja-$this->offset);
	 			$this->offset=$pozycja;
	 		}
	 	}
	 	
	 	public function zwrDl(){
	 		return $this->dl;
	 	}
	 }
?>
