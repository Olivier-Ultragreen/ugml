<?php
Class Services {
	static function makeInstance($hArgs = array()) {
		extract($hArgs);
		$GLOBALS['service'] = new $class;
	}
}
Class UGServices {
	var $aAllowedHostOrigins = array(
		'localhost',
		'www.warning-trading.com',
		'www.reputation-clean.com',
		'www.brokerdefense.net',
		'demos.brokerdefense.net'
		);
	function call($hArgs = array()) {
		extract($hArgs);
		if(!empty($_SERVER['HTTP_REFERER'])) {
			$hostOrigin = parse_url($_SERVER['HTTP_REFERER'],PHP_URL_HOST);
			if(in_array($hostOrigin,$this->aAllowedHostOrigins)) header('Access-Control-Allow-Origin: *');
		}
		else {
			header('Access-Control-Allow-Origin: *');
		}
		if(!empty($fn) && method_exists($this,$fn)) {
			return $this->$fn($hArgs);
		}
	}
	function sanitize(&$hData,$hControls) {
		$hError = array();
		$class = get_class($this);
		// supprime les valeurs non attendues
		foreach($hData as $k => $v) {
			if(!isset($hControls[$k]) || !is_array($hControls[$k])) {
				unset($hData[$k]);
			}
		}
		foreach($hControls as $k => $v) {
			if($v['mandatory']===true && empty($hData[$k]) && empty($hData[$k][0])) {
				$hError[$k]['mandatory'] = 'error';
			}
			elseif($v['mandatory']!==true && $v['mandatory']!==false) {
				if(method_exists($class,$v['mandatory'])) {
					if(!$this->$v['mandatory']($hData)) {
						$hError[$k]['mandatory'] = 'error';
					}
				}
			}
			elseif(!empty($v['type']) && !empty($hData[$k])) {
				$aControls = explode(',',$v['type']);
				foreach($aControls as $fnct) {
					if(method_exists($class,$fnct)) {
						if(!$this->$fnct($hData[$k],$k)) {
							$hError[$k]['type'] = 'error';
						}
					}
				}
			}
		}
		// cas particulier du double mail
		if(!empty($hData['mail-bis']) && $hData['mail']!=$hData['mail-bis']) $hError['mail-bis']['type'] = 'error';

		return $hError;
	}
	function is_int($var) {
		return is_numeric($var);
	}
	function is_array($var) {
		if(is_array($var)) return true;
		else return false;
	}
	function is_mail($var) {
		if(filter_var($var, FILTER_VALIDATE_EMAIL)) return true;
		else return false;
	}
	function is_phone($var) {
		$tmp = $var;
		// strips the string from classic additionnal characters
		$tmp = str_replace(array('+',' ','-','(',')'),array('','','','',''),$tmp);

		$match = preg_match('/^[0-9]{8,15}$/',$tmp);
		if($match==1) {
			return true;
		}
		else {
			return false;
		}
	}
	function is_url($var) {
		if(is_array($var)) {
			foreach($var as $v) {
				if(!empty($v) && !filter_var($v, FILTER_VALIDATE_URL)) return false;
			}
		}
		else {
			if(!filter_var($var, FILTER_VALIDATE_URL)) return false;
		}
		return true;
	}
	function is_captcha($var) {
		$offset = 3630;
		$reftime = time(); // moment auquel le serveur recoit la requête
		$vartime = strtotime($var) + $offset; // valeur du captcha envoyé + 3630 pour annuler l'offset de déstabilisation
		$diff = $reftime-$vartime;
		if($diff<0) return false; // si on a reçu la requête avant que l'article n'est pu être lu c'est qu'il y a un PB
		if($diff<30) return false; // si l'internaute à mis moins de 30 seconde à lire l'article à écrire son commentaire et à l'envoyer c'est louche
		if($diff>$offset) return false; // si l'internaute à mis plus de $offset pour valider son formulaire c'est louche aussi.
		return true;
	}
}
?>