<?php
function selectUgmlFiles($headerInfo, $from, $sOrderBy, $sens, $where='') {
	// SELECTS INFORMATIONS FROM UGML FILE $headerInfo FOR FILES IN WHICH $where CONDITION IS
	// FILLED IN $from DIRECTORY
	// SORTED BY $sOrderBy CRITERIA IN $sens DIRECTION
	// THE verif STATUS INFO CAN BE BYPASS OR NOT
	
	// print sprintf("SELECT %s FROM %s WHERE %s ORDER BY %s %s<br/>\n", $headerInfo, $from, $where, $sOrderBy, $sens);

	// TO BE SURE THE FUNCTION RETURN AN ARRAY AS EXPECTED
	$tab=array();
	// CHECKING ALL THE DIRECTORIES
	$dirs=explode(FUNCTION_PARAM_SPLITER, $from);
	foreach($dirs as $dir) {
		$rep=opendir(realpath(translatePath($dir)));
		while(($lecture = readdir($rep))) {
			// CHECKING THAT THE CURRENT FILE AS UGML EXTENSION
			// if(strrchr($lecture,'.')==UGML_EXTENSION) {
			if(in_array(strrchr($lecture,'.'),explode('|',UGML_EXTENSION))) {
				// die($lecture);
				$filename=realpath(translatePath($dir))."/$lecture";
				// die($filename);
				// IF THE FILE CAN BE OPEN
				if($pFile=@fopen($filename,"r")) {
					// GETTING THE HEADER FROM THE FILE
					$header=getHeader($pFile);
					// CLOSING THE FILE DESCRIPTOR
					fclose($pFile);
					// CHECKING FILE TYPE IF NECESSARY
					if((empty($where) or strpos($header, HEADER_PARAM_SPLITER.$where.HEADER_PARAM_SPLITER))) {
						// STORING THE FILE'S NAME
						$tab['filename'][]=$filename;
						$headerParams=getHeaderInfo($header);
						// GETTING THE VALUE OF THE PARAMETER USED FOR SORTING PURPOSE
						$tab['sOrderBy'][]=$headerParams[$sOrderBy];
						
						// GETTING THE VALUE OF THE SELECTED PARAMETERS FROM THE HEADER
						$infos=explode(FUNCTION_PARAM_SPLITER, $headerInfo);
						foreach($infos as $info) if(!empty($headerParams[$info])) $tab[$info][]=$headerParams[$info];
					}
				}
				else {
					@fclose($pFile);
					print ERR_FILE_OPEN.$filename.'<br/>'.ERR_PERMISSION_DENIED."\n";
					return array();
				}
			}
		}
	}
	// CLOSING DIRECTORY POINTER
	closedir($rep);
	
	// ORDERING THE INFORMATION
	if($sens=='ASC' && isset($tab['sOrderBy']) && is_array($tab['sOrderBy'])) {
		asort($tab['sOrderBy']);
	}
	elseif($sens=='DESC' && is_array($tab['sOrderBy'])) {
		arsort($tab['sOrderBy']);
	}
	return $tab;
}
?>