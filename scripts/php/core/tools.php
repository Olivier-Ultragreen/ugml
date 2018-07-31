<?php
function printArray($h) {
	printf('<pre>%s</pre>',print_r($h,true));
}
function translatePath($fileRelativePath) {
	/************ DEPREACATED DO NOT USE ****************/
	/****** PLEASE USE NATIVE PHP 5 realpath function instead **********/
	
	
	$sOutput=$fileRelativePath;
	$fileAbsolutePath=dirname($_SERVER['PATH_TRANSLATED']);
	
	// HANDLING FILES FROM CURRENT DIRECTORY
	if(substr($fileRelativePath,0,2)=='./')  return $fileAbsolutePath.substr($fileRelativePath,1);
	
	// HANDLING FILES FROM UPPER DIRECTORIES
	if(substr($fileRelativePath,0,3)=='../') {
		while(substr($fileRelativePath,0,3)=='../') {
			$fileRelativePath=substr($fileRelativePath,3);
			$fileAbsolutePath=dirname($fileAbsolutePath);
		}
		$sOutput=$fileAbsolutePath.'/'.$fileRelativePath;
	}
	return $sOutput;
}
function cleanDebugOutput($sPrintR) {
	$sOutput=$sPrintR;
	$sOutput=str_replace(array("Array\n","(\n",")\n"),array("\n",'',''),$sOutput);
	$sOutput=htmlentities(utf8_decode($sOutput));
	
	return $sOutput;
}
?>
