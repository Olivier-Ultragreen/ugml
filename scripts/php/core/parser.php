<?php
$aBannedIPAddresses=array('666.666.666.666');
if(in_array($_SERVER['REMOTE_ADDR'],$aBannedIPAddresses)) {
	die('This connexion as been used for hacking purposes ! If you are not the author of the hacking we are sorry for the inconvenience');
}
// path_translated doesn't always exists if not (uses $_SERVER['SCRIPT_FILENAME'])
if(empty($_SERVER['PATH_TRANSLATED'])) $_SERVER['PATH_TRANSLATED'] = $_SERVER['SCRIPT_FILENAME'];

include("../scripts/php/core/define.php");
include("../scripts/php/core/tools.php");
include("../scripts/php/core/api.php");

// gets the extension of the requested file
$sExt = strrchr($_SERVER['PATH_TRANSLATED'],'.');
if(!empty($_GET['f'])) {
	$sRequestedUgmlFile = str_replace(basename($_SERVER['PATH_TRANSLATED']),$_GET['f'],$_SERVER['PATH_TRANSLATED']);
}
elseif($sExt!='.ugml') {
	$sRequestedUgmlFile = str_replace($sExt,'.ugml',$_SERVER['PATH_TRANSLATED']);
}
else {
	$sRequestedUgmlFile = $_SERVER['PATH_TRANSLATED'];
}

// Saves the extension of the file that has been requested. 
// It might be PHP* OR UGML might be used for building navigation compatible with both parsing mode.
list($url) = explode('?',$_SERVER['REQUEST_URI']);
$GLOBALS['UGML']['HTTP_REQUEST_EXT'] = strrchr($url,'.');

// memorizes the requested ugml file data in $GLOBALS['UGML']
$GLOBALS['UGML']['REQUESTED_UGML_FILE']['FILENAME']=realpath($sRequestedUgmlFile);

// tries to open the file
// print($sRequestedUgmlFile);
$pFile=@fopen(realpath($sRequestedUgmlFile),'r');
if(!$pFile) {
	// logs error for debug
	$GLOBALS['UGML']['DEBUG']="ERROR : ".sprintf(ERR_FILE_OPEN, $sRequestedUgmlFile);
	$iOccurrence++;
	return '';
}
// memorizes the requested ugml file data in $GLOBALS['UGML']
$GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']=getHeaderInfo(getHeader($pFile));

include(PROJECT_DIRECTORY."/scripts/php/core/conf.php");
include(PROJECT_DIRECTORY."/settings/conf.php");

// closing $sRequestedUgmlFile file pointer
@fclose($pFile);

// actually parsing the $sRequestedUgmlFile
$sOutput = parseUgml(array('file'=>$sRequestedUgmlFile));

// injecting header information into the $sOutput string
$sOutput=inject(array('tpl'=>$sOutput,'data'=>$GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']));

// injecting debug informations in $sOutput string if in debug or function restricted debug mode
if(DEBUG or !empty($_GET['debug'])) {
	if(!empty($GLOBALS['UGML']['DEBUG'])) $GLOBALS['UGML']['DEBUG'] = print_r($GLOBALS['UGML']['DEBUG'],true);
	$GLOBALS['UGML']['DEBUG']	 = cleanDebugOutput($GLOBALS['UGML']['DEBUG']);
	$sOutput					 = inject(array('tpl'=>$sOutput,'data'=>$GLOBALS['UGML']));
}
elseif(!empty($_GET['fdebug'])) {
	if(!function_exists($_GET['fdebug'])) $GLOBALS['UGML']['DEBUG']="SORRY function \"{$_GET['fdebug']}\" doesn't exist";
	elseif(empty($GLOBALS['UGML']['DEBUG'][$_GET['fdebug']])) $GLOBALS['UGML']['DEBUG']="SORRY function \"{$_GET['fdebug']}\" is not called or doesn't trace any data";
	else $GLOBALS['UGML']['DEBUG']=print_r($GLOBALS['UGML']['DEBUG'][$_GET['fdebug']],true);
	
	$GLOBALS['UGML']['DEBUG']=cleanDebugOutput($GLOBALS['UGML']['DEBUG']);
	$sOutput=inject(array('tpl'=>$sOutput,'data'=>$GLOBALS['UGML']));
}
// last minute (re)injection of translation strings
$sOutput=inject(array('tpl'=>$sOutput,'data'=>$hTranslation));

// sending $sOutput to internet browser
print $sOutput;
?>