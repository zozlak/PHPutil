<?php
/*
 * Statyczna klasa ułatwiająca tworzenie kontrolek formularzy HTML
 */
class KontrolkiHTML {
	public static function e($s){
		return htmlspecialchars($s);
	}

	public static function input($type, $name, $value, $class="", $attr=array()){
		$a = '';
		foreach($attr as $h => $i){
			$a .= ' ' . $h . '="' . self::e($i) . '"';
		}
		printf(
			'<input type="%s" name="%s" value="%s" class="%s" onchange="%s" %s/>',
			self::e($type),
			self::e($name),
			self::e($value),
			self::e($class),
			self::e($onchange),
			$a
		);
	}

	public static function textarea($name, $value, $class="", $rows=2, $cols=40){
		printf(
			'<textarea name="%s" rows="%d" cols="%d" class="%s">%s</textarea>',
			self::e($name),
			intval($rows),
			intval($cols),
			self::e($class),
			self::e($value)
		);
	}
	
	public static function checkbox($name, $value, $label="", $class="", $onclick=""){
		printf(
			'<label><input type="checkbox" name="%s" %s class="%s" onclick="%s"/>%s</label>',
			self::e($name),
			$value ? 'checked="checked"' : '',
			self::e($class),
			self::e($onclick),
			$label
		);
	}
	
	public static function select($name, $value, array $opcje, $typ, $class="", $onchange=""){
		$multi = is_array($value);
		if(!$multi){
			$value = array($value);
		}
		$o = '';
		foreach($opcje as $h => $i){
			$w = $typ == 'pary' ? $h : $i;
			$o .= sprintf(
				'<option value="%s" %s>%s</option>',
				self::e($w),
				in_array($w, $value, true) || $w != '' && in_array($w, $value) ? 'selected="selected"' : '',
				self::e($i)
			);
		}
		printf(
			'<select name="%s" class="%s" onchange="%s" %s>%s</select>',
			self::e($name) . ($multi ? '[]' : ''),
			self::e($class),
			self::e($onchange),
			$multi ? 'multiple="multiple"' : '',
			$o
		);
	}
	
	public static function radio($name, $value, array $opcje, $typ, $class="", $onclick="", $br=true){
		$o = '';
		foreach($opcje as $h => $i){
			$w = $typ == 'pary' ? $h : $i;
			$o .= sprintf(
				'<label><input type="radio" name="%s" value="%s" %s onclick="%s"/>&nbsp;%s</label>%s',
				self::e($name),
				self::e($w),
				$value === $w || $w != '' && $value == $w ? 'checked="checked"' : '',
				self::e($onclick),
				self::e($i),
				$br ? '<br/>' : ''
			);
		}
		printf($o);
	}
	
	public static function button($value, $onclick, $class=""){
		printf(
			'<button type="button" onclick="%s" class="%s">%s</button>',
			self::e($onclick),
			self::e($class),
			$value
		);
	}
	
	public static function submit($name, $value, $onclick="", $class=""){
		printf(
			'<button type="submit" name="%s" onclick="%s" class="%s">%s</button>',
			self::e($name),
			self::e($onclick),
			self::e($class),
			$value
		);
	}
}
