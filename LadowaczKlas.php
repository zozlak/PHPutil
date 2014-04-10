<?php
	class LadowaczKlas{
		private $katalog;
		
		public function __construct($katalogBazowy = '.'){
			$this->katalog = $katalogBazowy;
			spl_autoload_register(array($this, 'laduj'));
		}
		
		public function laduj($klasa){
			$klasa = str_replace('\\', '/', $klasa);
			
			$tmp = $this->katalog.'/'.$klasa.'.php';
			if(file_exists($tmp)){
				require_once($tmp);
			}
		}
	}
	$ladowaczKlas = new LadowaczKlas(@$GLOBALS['KATALOG_BAZOWY']);
?>
