<?php
// TIMEZONE DEFINITION
date_default_timezone_set('Europe/Paris');

// DEFINING WEBSITE HTTP ROOT URL
define('HTTP_ROOT','http://'.$_SERVER['HTTP_HOST'].str_replace('//','/',dirname(dirname($_SERVER['SCRIPT_NAME']))).'/');

// FILE PATH ON THE SERVER's DISK
$GLOBALS['UGML']['CONF']['WEB_ROOT']		 = PROJECT_DIRECTORY;
$GLOBALS['UGML']['CONF']['UGD_PATH']		 = PROJECT_DIRECTORY."/ugd/";
$GLOBALS['UGML']['CONF']['INCLUDED_PATH']	 = PROJECT_DIRECTORY."/include/";
$GLOBALS['UGML']['CONF']['UPLOAD_PATH']		 = PROJECT_DIRECTORY."/media/_upload/";
$GLOBALS['UGML']['CONF']['DOWNLOAD_PATH']	 = PROJECT_DIRECTORY."/media/_download/";
$GLOBALS['UGML']['CONF']['IMAGE_PATH']		 = PROJECT_DIRECTORY."/media/_images/";
$GLOBALS['UGML']['CONF']['TEMPLATE_PATH']	 = PROJECT_DIRECTORY."/template/";

$GLOBALS['UGML']['CONF']['LANG_PATH']		 = PROJECT_DIRECTORY."/scripts/php/lang/";
$GLOBALS['UGML']['CONF']['EXTENSION_PATH']	 = PROJECT_DIRECTORY."/scripts/php/ext/";

// defining a few usefull elements that might be usefull so that they can be injected directly into templates
// through translation process.
// As global translation is operated in the end of the parsing process you might need to force translatePath using 
// hashIntoTpl function 
$hTranslation=array();
$hTranslation['site.root.url']		 = HTTP_ROOT;
$hTranslation['site.root.directory'] = PROJECT_DIRECTORY;
$hTranslation['image.path']			 = $GLOBALS['UGML']['CONF']['IMAGE_PATH'];
$hTranslation['QUERY_STRING']		 = $_SERVER['QUERY_STRING'];
$hTranslation['REQUEST_URI']		 = $_SERVER['REQUEST_URI'];
$hTranslation['SCRIPT_NAME']		 = $_SERVER['SCRIPT_NAME'];
$hTranslation['EXT']				 = $GLOBALS['UGML']['HTTP_REQUEST_EXT'];
if(!empty($_SERVER['HTTP_REFERER'])) $hTranslation['HTTP_REFERER']=$_SERVER['HTTP_REFERER'];

$hCSSColors = array();
$hCSSColors['ultragreen']	 = "green";
$hCSSColors['supergreen']	 = "#e0f0e0";

// loading language file
$lang = 'fr'; // default language
// listing available languages 
$aLanguages = array('fr','en');

// determines which language is to be used for display
$hMainHeader = $GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER'];
if(!empty($hMainHeader['lang']) && in_array($hMainHeader['lang'],$aLanguages)) {
	$lang = $hMainHeader['lang'];
}
else {
	$lang='fr';
}
// loads the translation file of the backoffice if necessary
if(strpos($_SERVER['PATH_TRANSLATED'],'/'.BCKFFC_DIR.'/')) {
	include("{$GLOBALS['UGML']['CONF']['LANG_PATH']}bckffc-lang-{$lang}.php");
}
// printArray($_SERVER);
?>