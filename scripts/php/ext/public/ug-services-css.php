<?php
include_once($GLOBALS['UGML']['CONF']['WEB_ROOT'].'/scripts/php/ext/public/ug-services.php');
Class UGServicesCSSInjector extends UGServices {
	function get($hArgs = array()) {
		global $hCSSColors,$hCSSFonts;
		extract($hArgs);
		$sOutput = '';
		
		$tpl = getTpl("../styles/$stylesheet");
		header('Content-type: text/css');
		$aFiles = explode(',',$stylesheet);
		foreach($aFiles as $file) {
			$sOutput .= getTpl("../styles/$file");
		}
		
		$hStyle = array_merge($hCSSColors,$hCSSFonts);
		return inject(array('data' => $hStyle, 'tpl' => $sOutput));
	}
	function load($hArgs = array()) {
		global $hCSSColors,$hCSSFonts;
		extract($hArgs);
		$sOutput = '';
		$tpl = "\t".'<link rel="stylesheet" href="'.HTTP_ROOT.'services/ug-css-generator.php?stylesheet=%s" type="text/css" charset="utf-8"/>'."\n";
		// en dev on garde les fichiers séparés pour une meilleure lecture debuggage
		if($_SERVER['HTTP_HOST']=='localhost') {
			$aFiles = explode(',',$files);
			foreach($aFiles as $file) {
				$sOutput .= sprintf($tpl,$file);
			}
		}
		else {
			// en prod on compile les fichiers
			$sOutput .= sprintf($tpl,$files);
		}
		return $sOutput;
	}
}
?>