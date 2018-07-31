<?php
// gets the header of an ugml file using a file descriptor (aka first line of the file)
function getHeader($pFile) {
	// gets the first line of the file
	$sHeader=fgets($pFile,4096);
	// roughly checks if the first line is a UGML header
	if(!strstr($sHeader,CLOSING_HEADER_DELIMITER)) {
		// if not resets the file pointer
		fseek($pFile,0);
		// and generates a default header
		$sHeader = OPENING_HEADER_DELIMITER.'ugd=|_void.ugd|'.CLOSING_HEADER_DELIMITER;
	}
	// returns the header real or "fake"
	return $sHeader;
}

// returns the data of the header in hash
// reminder header format is OPENING_HEADER_DELIMITERHEADER_PARAM_SPLITERkey1=value1HEADER_PARAM_SPLITERkey2=value2HEADER_PARAM_SPLITERCLOSING_HEADER_DELIMITER
// with default values of constants it is like [HEADER]|key1=value1|key2=value2|key3=value3|/]
// NOTE it might be possible to extract the dates more eleganly ??? efficiently throught regular expression feel free to submit me one if you have.
function getHeaderInfo($sHeader) {
	$sHeader = str_replace(array(OPENING_HEADER_DELIMITER,CLOSING_HEADER_DELIMITER),array('',''),$sHeader);
	// remove the special chars. That allows the use of multiline ugml tag
	$sHeader = str_replace(array("\n","\r","\t",'=|','|'),array('','','','(-x-)','(-x-)'),$sHeader);
	$a = explode('(-x-)',$sHeader);
	$i=0;
	while(!empty($a[$i]) && !empty($a[$i+1])) {
		$hHeaderAttr[trim($a[$i])]=trim($a[$i+1]);
		$i+=2;
	}
	return $hHeaderAttr;
}

// gets UGML file content using a file descriptor placed after the header
function getBodyFromHere($pFile){
	return fread($pFile, MAX_FILE_SIZE);
}

// gets the content of a template file
function getTpl($tpl) {
	static $iOccurrence=0;
	static $hAlreadyOpened = array();
	// checks if the template file has already been opened once before
	if(!isset($hAlreadyOpened[$tpl])) {
		// if the file has not been open before checks if the file exists
		$file = realpath(translatePath($tpl));
		if($file) {
			// if the file exists, get its content and stores it for faster access later
			$hAlreadyOpened[$tpl] = file_get_contents($file);
		}
		else {
			// if the file does not exist stores the error message for faster access later
			$hAlreadyOpened[$tpl] = "ERROR : $tpl could not be found";
			// log the opening attempt in the debug stack
			$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence] = $hAlreadyOpened[$tpl];
		}
	}
	// returns the content of the static file
	$iOccurrence++;
	return $hAlreadyOpened[$tpl];
}

// gets the list of extension defined in the ugd $sFilename
// and returns it in a matrix array(array('tag'=>$tag, 'function'=>$function, 'extensionFile'=>$file))
function getExtensionFromUgd($sUgdFilename) {
	static $iOccurrence=0;
	$mExtensionList=array();
	
	// gets the content of the ugd file $sUgdFilename
	$sContent=file_get_contents(realpath($GLOBALS['UGML']['CONF']['UGD_PATH'].$sUgdFilename));
	// returns error if could not get the file content
	if($sContent===false) {
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : ".sprintf(ERR_FILE_OPEN, $sUgdFilename);
		$iOccurrence++;
		return '';
	}

	// organizes trhe content of the return matrix
	$mMatches=array();
	preg_match_all("/".DEFEXT_STRUCTURE."/", $sContent, $mMatches);
	foreach($mMatches[1] as $k => $sExt) {
		$mExtensionList[$k]['tag']=$sExt;
		@list($mExtensionList[$k]['function'],$mExtensionList[$k]['extensionFile'])=explode(TAG_SPLITER,trim($mMatches[2][$k]));
	}
	$iOccurrence++;
	return $mExtensionList;
}

// gets the list of tag defined in the ugd $sFilename
// and returns it in a hash array('tag1'=>'replacement1','tag2'=>'replacement2'...)
function getTagFromUgd($sUgdFilename) {
	static $iOccurrence=0;
	$hListTag=array();

	// gets the content of the ugd file $sUgdFilename
	$sContent=file_get_contents(realpath($GLOBALS['UGML']['CONF']['UGD_PATH'].$sUgdFilename));
	// returns error if could not get the fiel content
	if($sContent===false) {
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : ".sprintf(ERR_FILE_OPEN, $sUgdFilename);
		$iOccurrence++;
		return '';
	}

	$mMatches=array();
	// parsing du cointenu du fichier a la recherche des definitions de tag
	preg_match_all("/".DEFTAG_STRUCTURE."/", $sContent, $mMatches);
	foreach($mMatches[1] as $k => $sTag) {
		$hListTag[$sTag]=trim($mMatches[2][$k]);
	}
	return $hListTag;
}

// gets the xml like attributs of the $sAttributs and return them into a hash array('attr1'=>'value1',attr2'=>'value2' ...)
function getAttrFromTag($sAttributs) {
	$hTagAttr=array();
	$i=0;
	// remove the special chars. That allows the use of multiline ugml tag
	$s=str_replace(array("\n","\r","\t",'=|','|'),array('','','','(-x-)','(-x-)'),$sAttributs);
	$a=explode('(-x-)',$s);

	while(!empty($a[$i]) && !empty($a[$i+1])) {
		$hTagAttr[trim($a[$i])]=trim($a[$i+1]);
		$i+=2;
	}
	return $hTagAttr;
}

// returns the UGML header part of $sStr if it exists or the default header refering to the _void.ugd file
function getHeaderFromString($sStr) {
	$iHeaderStart=strpos($sStr,OPENING_HEADER_DELIMITER);
	$iHeaderEnd=strpos($sStr,CLOSING_HEADER_DELIMITER);
	if($iHeaderStart!==false && $iHeaderEnd!==false && $iHeaderStart===0 && $iHeaderEnd>0) {
		// if header exists returns it
		return substr($sStr,0,$iHeaderEnd+strlen(CLOSING_HEADER_DELIMITER));
	}
	else {
		// returns default header 
		return OPENING_HEADER_DELIMITER."|ugd=_void.ugd|".CLOSING_HEADER_DELIMITER;
	}
}

// gets the "body" of the UGML file
function getBodyFromString($sStr) {
	// check if header exists in $sStr
	$iHeaderStart=strpos($sStr,OPENING_HEADER_DELIMITER);
	$iHeaderEnd=strpos($sStr,CLOSING_HEADER_DELIMITER);
	// if there is a header
	if($iHeaderStart!==false && $iHeaderEnd!==false && $iHeaderStart===0 && $iHeaderEnd>0) {
		// removes the header from $sStr
		return substr($sStr,$iHeaderEnd+strlen(CLOSING_HEADER_DELIMITER));
	}
	else {
		// 
		return $sStr;
	}
}
// It is often usefull to inject data from header, get, post, session into your ugml file.
// This function provides both slash-added and slash-striped version of those for injection.
// You should always use the first one when the data will be used for SQL query.
function dataHGPC() {
	global $DB;
	$hReturn=array();
	if(!empty($GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER'])) {
		foreach($GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER'] as $k => $v) {
			if(!is_array($v)) {
				// IS IT USEFULL ????
				if(stripos($v,'select')===false && stripos($v,'union')===false && stripos($v,'information_schema')===false) {
					if(get_magic_quotes_gpc()) {
						$hReturn["HEADER*:$k"]=mysqli_real_escape_string($DB,$v);
						$hReturn["HEADER:$k"]=stripslashes($v);
					}
					else {
						$hReturn["HEADER:$k"]=$v;
						$hReturn["HEADER*:$k"]=addslashes(mysqli_real_escape_string($DB,$v));
					}
				}
			}
		}
	}
	if(!empty($_GET)) {
		foreach($_GET as $k => $v) {
			if(!is_array($v)) {
				if(stripos($v,'select')===false && stripos($v,'union')===false && stripos($v,'information_schema')===false) {
					if(get_magic_quotes_gpc()) {
						$hReturn["GET*:$k"]=mysqli_real_escape_string(mysqli_real_escape_string($DB,$v));
						$hReturn["GET:$k"]=stripslashes($v);
					}
					else {
						$hReturn["GET:$k"]=$v;
						$hReturn["GET*:$k"]=addslashes(mysqli_real_escape_string($DB,$v));
					}
				}
			}
		}
	}
	if(!empty($_POST)) {
		foreach($_POST as $k => $v) {
			if(!is_array($v)) {
				if(stripos($v,'select')===false && stripos($v,'union')===false && stripos($v,'information_schema')===false) {
					if(get_magic_quotes_gpc()) {
						$hReturn["POST*:$k"]=mysqli_real_escape_string($DB,$v);
						$hReturn["POST:$k"]=stripslashes($v);
					}
					else {
						$hReturn["POST:$k"]=$v;
						$hReturn["POST*:$k"]=addslashes(mysqli_real_escape_string($DB,$v));
					}
				}
			}
		}
	}
	if(!empty($_SESSION)) {
		foreach($_SESSION as $k => $v) {
			if(!is_array($v)) {
				if(stripos($v,'select')===false && stripos($v,'union')===false && stripos($v,'information_schema')===false) {
					if(get_magic_quotes_gpc()) {
						$hReturn["SESSION*:$k"]=mysqli_real_escape_string($DB,$v);
						$hReturn["SESSION:$k"]=stripslashes($v);
					}
					else {
						$hReturn["SESSION:$k"]=$v;
						$hReturn["SESSION*:$k"]=addslashes(mysqli_real_escape_string($DB,$v));
					}
				}
			}
		}
	}
	return $hReturn;
}

// Tnjects data from $data into $tpl
function inject($hArgs = array()) {
	static $iOccurrence=0;
	// defines default values of args
	$hDefault=array('tpl'=>'','data'=>array(),'nl2br'=>false);
	// get input vars from hArgs and complete them with reviously defined default values
	extract(array_merge($hDefault,$hArgs));
	
	$aNeedle=array();
	$aReplace=array();
	
	// checks the input args and logs warnings if necessary
	if(empty($tpl)) $GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="NOTICE : empty argument ".print_r($hArgs,true);
	if(empty($data)) $GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="NOTICE : empty argument ".print_r($hArgs,true);
	
	foreach($data as $sKey=> $sValue) {
		if(!empty($sKey) and !is_array($sValue)) {
			if($nl2br==true) $sValue=nl2br($sValue);
			$aNeedle[]="[$sKey]";
			$aReplace[]=$sValue;
		}
	}
	$iOccurrence++;
	return str_replace($aNeedle, $aReplace, $tpl);
}

// This function is depreacated you should use "inject" function instead
// This just a wrapper for the inject function kept for backward compatibility and developper habits
function hashIntoTpl($tpl, $data, $nl2br=false) {
	return inject(array('data'=>$data,'tpl'=>$tpl,'nl2br'=>$nl2br));
}

function selectUgmlFiles($hArgs = array()) {
	static $iOccurrence = 0;
	$hDefault = array(
		'from'	 	 => array('./'),
		'retrieve'	 => array(),
		'where'		 => array(),
		'operator'	 => 'AND',
		'order'		 => 'title',
		'direction'	 => 'ASC'
	);
	extract(array_merge($hDefault,$hArgs));
	
	$aTmp = array();
	$hTmp = array();
	$hOutput = array();
	$i=0;
	// checking all the directories
	foreach($from as $path) {
		$dir = opendir(realpath(translatePath($path)));
		while($file = readdir($dir)) {
			// checking that the current file as ugml extension
			if(in_array(strrchr($file,'.'),explode('|',UGML_EXTENSION))) {
				$filename = realpath(translatePath($path))."/$file";
				// if the file can be open
				if($fd = @fopen($filename,"r")) {
					// getting the header from the file
					$sHeader = getHeader($fd);
					// closing the file descriptor since the rest of the file is un-interesting
					fclose($fd);
	
					if(!empty($where)) {
						switch($operator) {
							case 'AND': {
								$bMatchCondition = true;
								foreach($where as $key => $condition) {
									if(!stripos($sHeader, $condition)) {
										$bMatchCondition = false;
										break;
									}
								}
								break;
							}
							case 'OR': {
								$bMatchCondition = false;
								foreach($where as $key => $condition) {
									if(stripos($sHeader, $condition)) {
										$bMatchCondition = true;
										break;
									}
								}
								break;
							}
							default : {
								$bMatchCondition = true;
								foreach($where as $key => $condition) {
									if(!stripos($sHeader, $condition)) {
										$bMatchCondition = false;
										break;
									}
								}
								break;
							}
						}
					}
					else {
						$bMatchCondition = true;
					}
					
					// checking files header matches the "where" condition 
					if($bMatchCondition) {
						$hHeader = getHeaderInfo($sHeader);
						$hTmp[$i]['filename']	 = $filename;
						$hTmp[$i]['order']		 = 0;
						if(!empty($hHeader[$order])) $hTmp[$i]['order'] = $hHeader[$order];
						$aTmp[$i] = $hTmp[$i]['order'];
						// if retrieve is left empty gets all the data from the header
						if(empty($retrieve)) {
							$hTmp[$i] = array_merge($hHeader,$hTmp[$i]);
						}
						else {
							foreach($retrieve as $key) {
								$hTmp[$i][$key] = '';
								if(!empty($hHeader[$key])) $hTmp[$i][$key] = $hHeader[$key];
							}
						}
					}
					$i++;
				}
				else {
					@fclose($fd);
					$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence] = '';
					if(empty($GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence])) {
						$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence] = "ERROR : arguments ".print_r($hArgs,true)."\n";
					}
					$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence] .= "ERROR : ".sprintf(ERR_FILE_OPEN, $filename)."\n";
				}
			}
		}
		closedir($dir);
	}
	if($direction=='DESC') {
		arsort($aTmp);
	}
	else {
		asort($aTmp);
	}
	// ordering the results by order ASC
	foreach($aTmp as $key => $value) {
		$hOutput[] = $hTmp[$key];
	}
	return $hOutput;
}

function parseStringAsUGML($sUGML) {
	static $iOccurrence=0;
	
	$sHeader=getHeaderFromString($sUGML);
	
	// saves header info into $GLOBALS['UGML']['CURRENT_UGML_FILE']['HEADER']
	$GLOBALS['UGML']['CURRENT_UGML_FILE']['HEADER']=getHeaderInfo($sHeader);

	$sOutput=getBodyFromString($sUGML);
	
	// checks if header contains reference to some ugd file
	if(empty($GLOBALS['UGML']['CURRENT_UGML_FILE']['HEADER']['ugd'])) {
		// logs error for debug
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : NO UGD DEFINED IN THIS STRING $sUGML";
		$iOccurrence++;
		return '';
	}
	
	// injects header,get,post,session for a first time
	$sOutput=inject(array('tpl'=>$sOutput,'data'=>dataHGPC()));
	
	$hExtFunction=getExtensionFromUgd($GLOBALS['UGML']['CURRENT_UGML_FILE']['HEADER']['ugd']);
	
	// processes each extension
	foreach($hExtFunction as $i=>$hValue) {
		$a=array();
		if(!empty($hValue)) {
			if(!empty($hValue['extensionFile'])) require_once($GLOBALS['UGML']['CONF']['EXTENSION_PATH'].$hValue['extensionFile']);
			$sTag=$hValue['tag'];
			$sFunction=$hValue['function'];
		}

		
		// creates regexp to find extension calls in $sUGML
		$sRegExp = sprintf(str_replace(array('/','[',']'), array('\/','\[','\]'), OPENING_EXT_STRUCTURE), $sTag);
		$sRegExp.= '(.*)'.sprintf(str_replace(array('/','[',']'), array('\/','\[','\]'), CLOSING_EXT_STRUCTURE), $sTag);
		// find occurrences of extension calls
		preg_match_all("/$sRegExp/",$sOutput,$a);
		
		if(!empty($a[1])) {
			foreach($a[1] as $iMatch => $sValue) {
				// for each calls gets the arguments to be passed on to the extension
				$hArgs=getAttrFromTag($sValue);
				
				// hack to be able to call methods of Object Instance
				if(strstr($function,'::') and !empty($hArgs['instanceName'])) {
					$function=explode('::',$function);
					$function[0]=$GLOBALS[$hArgs['instanceName']];
				}
				// calls the corresponding function with the proper args
				$sResult=call_user_func($function, $hArgs);
				
				// gets back the header of the current ugml "file" for it might have been changed through out a recursive call to the parser
				$GLOBALS['UGML']['CURRENT_UGML_FILE']['HEADER']=getHeaderInfo($sHeader);
				
				// remplace les appels aux extensions par le resultat correspondant
				$sOutput=str_replace(sprintf(OPENING_EXT_STRUCTURE, $sTag).$sValue.sprintf(CLOSING_EXT_STRUCTURE, $sTag), $sResult, $sOutput);
			}
		}
	}
	
	// injects header,get,post,session for a second time assuming some values might have been created it might have changed since the first injection
	// it often happens that session data are created in an extension
	$sOutput=inject(array('tpl'=>$sOutput,'data'=>dataHGPC()));
	$iOccurrence++;
	
	$hTags=getTagFromUgd($GLOBALS['UGML']['CURRENT_UGML_FILE']['HEADER']['ugd']);
	
	$aItem2Replace=array();
	$aReplaceBy=array();
	
	// replaces tags at once
	foreach($hTags as $sTag=>$sValue) {
		$a=array();
		$aItem2Replace[]=sprintf(OPENING_TAG_STRUCTURE, $sTag);
		$aItem2Replace[]=sprintf(CLOSING_TAG_STRUCTURE, $sTag);
		$a=explode(TAG_SPLITER, $sValue);
		
		if(!empty($tmp[0]))
			$aReplaceBy[]=trim($a[0]);
		else
			$aReplaceBy[]='';
		
		if(!empty($tmp[1]))
			$aReplaceBy[]=trim($a[1]);
		else
			$aReplaceBy[]='';
	}

	return str_replace($aItem2Replace, $aReplaceBy, $sOutput);

}

function parseUgml($hArgs) {
	static $iOccurrence=0;

	// defines default values of args
	$hDefault=array('file'=>$GLOBALS['UGML']['REQUESTED_UGML_FILE']);
	// get input vars from hArgs and complete them with reviously defined default values
	extract(array_merge($hDefault,$hArgs));

	if(empty($file))  {
		// logs error for debug
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : NO UGML FILE TO PARSE ".print_r($hArgs,true);
		$iOccurrence++;
		return '';
	}
	
	// tries to open the file
	$pFile=@fopen(realpath(translatePath($file)),'r');
	if(!$pFile) {
		// logs error for debug
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : ".sprintf(ERR_FILE_OPEN, $file);
		$iOccurrence++;
		return '';
	}
	
	// saves the file that is currently parsed
	$GLOBALS['UGML']['CURRENT_UGML_FILE']['FILENAME']=$file;
	
	$sHeader=getHeader($pFile);
	
	$GLOBALS['UGML']['CURRENT_UGML_FILE']['HEADER'] = getHeaderInfo($sHeader);
	
	// checks if header contains reference to some ugd file
	if(empty($GLOBALS['UGML']['CURRENT_UGML_FILE']['HEADER']['ugd'])) {
		// logs error for debug
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : NO UGD DEFINED IN THIS STRING $sUGML";
		$iOccurrence++;
		return '';
	}
	
	$sOutput=getBodyFromHere($pFile);
	// closing the file ...
	@fclose($pFile);

	// injetcs header,get,post,session for a first time
	$sOutput=inject(array('tpl'=>$sOutput,'data'=>dataHGPC()));

	// recuepration des fonctions definies dans l'ugd
	$hExtFunction = getExtensionFromUgd($GLOBALS['UGML']['CURRENT_UGML_FILE']['HEADER']['ugd']);
	
	// processes each extension
	foreach($hExtFunction as $i=>$hValue) {
		$a=array();
		if(!empty($hValue)) {
			if(!empty($hValue['extensionFile'])) require_once($GLOBALS['UGML']['CONF']['EXTENSION_PATH'].$hValue['extensionFile']);
			$sTag=$hValue['tag'];
			$sFunction=$hValue['function'];
		}

		// creates regexp to find extension calls in $sUGML
		// $sRegExp = sprintf(str_replace('/', '\/', OPENING_EXT_STRUCTURE), $sTag).'(.*)'.sprintf(str_replace('/', '\/', CLOSING_EXT_STRUCTURE), $sTag);
		$sRegExp = sprintf(str_replace(array('/','[',']'), array('\/','\[','\]'), OPENING_EXT_STRUCTURE), $sTag);
		$sRegExp.= '(.*)'.sprintf(str_replace(array('/','[',']'), array('\/','\[','\]'), CLOSING_EXT_STRUCTURE), $sTag);
		
		
		// find occurrences of extension calls
		preg_match_all("/$sRegExp/msU",$sOutput,$a);
		if(!empty($a[1])) {
			foreach($a[1] as $iMatch => $sValue) {
				// for each calls gest the arguments to be bassed on to the extension
				$hArgs = getAttrFromTag($sValue);
				// printArray($hArgs);
				// hack to be able to call methods of Object Instance
				if(strstr($sFunction,'::') and !empty($hArgs['instanceName'])) {
					$sFunction=explode('::',$sFunction);
					$sFunction[0]=$GLOBALS[$hArgs['instanceName']];
				}
				// calls the corresponding function with the proper args
				$sResult=call_user_func($sFunction, $hArgs);
				
				// gets back the header of the current ugml "file" for it might have been changed through out a recursive call to the parser
				// gets back the header of the current ugml "file" for it might have been changed through out a recursive call to the parser
				$GLOBALS['UGML']['CURRENT_UGML_FILE']['FILENAME']=$file;
				$GLOBALS['UGML']['CURRENT_UGML_FILE']['HEADER']=getHeaderInfo($sHeader);
				
				// remplace les appels aux extesions par le resultat correspondant
				$sOutput=str_replace(sprintf(OPENING_EXT_STRUCTURE, $sTag).$sValue.sprintf(CLOSING_EXT_STRUCTURE, $sTag), $sResult, $sOutput);
			}
		}
	}
	
	// injects header,get,post,session for a second time assuming some values might have been created it might have changed since the first injection
	// it often happens that session data are created in an extension
	$sOutput=inject(array('tpl'=>$sOutput,'data'=>dataHGPC()));
	$iOccurrence++;
	
	$hTags=getTagFromUgd($GLOBALS['UGML']['CURRENT_UGML_FILE']['HEADER']['ugd']);
	
	$aItem2Replace=array();
	$aReplaceBy=array();
	
	// replaces tags at once
	foreach($hTags as $sTag=>$sValue) {
		$a=array();
		$aItem2Replace[]=sprintf(OPENING_TAG_STRUCTURE, $sTag);
		$aItem2Replace[]=sprintf(CLOSING_TAG_STRUCTURE, $sTag);
		$a = explode(TAG_SPLITER, $sValue);
		
		if(!empty($tmp[0]))
			$aReplaceBy[]=trim($a[0]);
		else
			$aReplaceBy[]='';
		
		if(!empty($tmp[1]))
			$aReplaceBy[]=trim($a[1]);
		else
			$aReplaceBy[]='';
	}

	return str_replace($aItem2Replace, $aReplaceBy, $sOutput);
}
?>