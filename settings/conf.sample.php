<?php
// TIMEZONE DEFINITION
date_default_timezone_set('Europe/Paris');

// ABOUT THE WEBSITE
define('SITE_TITLE','UGML - Ultragreen Markup Language');

if($_SERVER['HTTP_HOST']=='localhost') {
	// DEV
	define('HOST', '');
	define('USER', '');
	define('PWD', '');
	define('DB', '');
	define('ERROR_REPORTING_LVL', E_ALL);
	ini_set('display_errors',1);
}
else {
	// PROD
	define('HOST', '');
	define('USER', '');
	define('PWD', '');
	define('DB', '');
	define('ERROR_REPORTING_LVL', 0);
	ini_set('display_errors',0);
}

// loading standard "home made" libraries
include($GLOBALS['UGML']['CONF']['WEB_ROOT'].'/scripts/php/lib/lib.tools.php');
include($GLOBALS['UGML']['CONF']['WEB_ROOT'].'/scripts/php/lib/lib.mysql.php');

// loading project specific "free" libraries

error_reporting(ERROR_REPORTING_LVL);
if(strpos($_SERVER['REQUEST_URI'],'/bckffc/')!==false) {
	include("{$GLOBALS['UGML']['CONF']['LANG_PATH']}bckffc-lang-fr.php");
	session_start();
}
include("{$GLOBALS['UGML']['CONF']['LANG_PATH']}lang-fr.php");
$hTranslation['site.title'] = SITE_TITLE;

// ABOUT COLORS
// colors definition for injection into CSS files
$hCSSColors = array();
$hCSSColors['black']		 = "black";

// ABOUT FONTS
$hCSSFonts = array();
$hCSSFonts['basic-font'] = 'Verdana, sans-serif';
?>