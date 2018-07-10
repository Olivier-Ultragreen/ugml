<?php
// Setting up connexion to the DB
$DB = mysqli_connect(HOST, USER, PWD, DB);
// Specifies mysql that data are transmitted as utf8
mysqli_set_charset($DB, "utf8");

$hFieldTypeDefinition=array(
	'varchar'=> 				array('fieldNameMatches'=>'',				'fieldTypeMatches'=>'/^varchar(.*)?$/',	'fieldKeyMatches'=>''),
	'int'=> 					array('fieldNameMatches'=>'',				'fieldTypeMatches'=>'/^int(.*)?$/',		'fieldKeyMatches'=>''),
	'float'=> 					array('fieldNameMatches'=>'',				'fieldTypeMatches'=>'/^float$/',		'fieldKeyMatches'=>''),
	'primaryKey'=> 				array('fieldNameMatches'=>'/^id$/',			'fieldTypeMatches'=>'',					'fieldKeyMatches'=>'PRI'),
	'foreignKey'=> 				array('fieldNameMatches'=>'/^id_(.*)?$/',	'fieldTypeMatches'=>'',					'fieldKeyMatches'=>''),
	'parent' =>					array('fieldNameMatches'=>'/^id_parent$/', 'fieldTypeMatches'=>'','fieldKeyMatches'=>''),
	'enumList'=> 				array('fieldNameMatches'=>'',				'fieldTypeMatches'=>'/^enum(.*)?$/',	'fieldKeyMatches'=>''),
	'foreignKeyList'=> 			array('fieldNameMatches'=>'/^id_(.*)?_list$/',				'fieldTypeMatches'=>'',	'fieldKeyMatches'=>''),
	'uploadedFileReference'=> 	array('fieldNameMatches'=>'/^file_(.*)?/',	'fieldTypeMatches'=>'',					'fieldKeyMatches'=>''),
	'plainText'=> 				array('fieldNameMatches'=>'/(.*)?_txt$|(.*)?_html$|^online_version$/',	'fieldTypeMatches'=>'/^text$/',					'fieldKeyMatches'=>''),
	'password'=>				array('fieldNameMatches'=>'/^pwd$/', 'fieldTypeMatches'=>'','fieldKeyMatches'=>''),
);

function updatePassword($hArgs = array()) {
	global $hResizeRules;
	$sOutput = '';
	$hDefault=array(
		'table'=>'',
		'id'=>'',
		'hFieldInfo'=>'',
		'value'=>''
	);
	extract(array_merge($hDefault,$hArgs));
	
	if(!empty($_POST['pwd'])) {
		$sOutput = "`pwd`='".md5($_POST['pwd'])."', ";
	}

	if(empty($sOutput) && !empty($value)) $sOutput = "`{$hFieldInfo['name']}`='".$value."', ";
	return $sOutput;
	
}

function updateUploadedFileReference($hArgs = array()) {
	$sOutput = '';
	$hDefault=array(
		'table'=>'',
		'id'=>'',
		'hFieldInfo'=>'',
		'value'=>''
	);
	extract(array_merge($hDefault,$hArgs));
	if(!empty($_FILES[$hFieldInfo['name']]) && !empty($_FILES[$hFieldInfo['name']]['tmp_name']) && $_FILES[$hFieldInfo['name']]['error']==0) {
		// gets the significant part of the field's name aka deprived from the file_ type marker
		$sFieldShortName= substr($hFieldInfo['name'],5);
		
		$fileType 		= strtolower(strrchr($_FILES[$hFieldInfo['name']]['name'],'.'));
		$fileFuturName = "{$GLOBALS['UGML']['CONF']['UPLOAD_PATH']}{$table}.{$id}.{$sFieldShortName}{$fileType}";
		
		// renames the uploaded file and stores it into the proper folder
		rename($_FILES[$hFieldInfo['name']]['tmp_name'],$fileFuturName);
		// adjust file permission
		chmod($fileFuturName,0644);
		// build the sql statement 
		$sOutput = "`{$hFieldInfo['name']}`='".basename($fileFuturName)."', ";
	}
	if(empty($_FILES['file_student_id']) && $hFieldInfo['name']=='file_student_id') {
		$sOutput = "`{$hFieldInfo['name']}`='{$_SESSION['file_student_id']}', ";
	}
	return $sOutput;
}

/*********************************************************
functions degined to interact with mysql DB more freely
**********************************************************/
// gets the table structure and field type as described in $hFieldTypeDefinition
function getTableInfo($sqlTable) {
	$hFields=array();
	global $DB,$hTranslation,$hFieldTypeDefinition;
	
	$sqlShow	 = "SHOW COLUMNS FROM `$sqlTable`";
	$sqlResult	 = mysqli_query($DB, $sqlShow);
	$i=0;
	while($hRow=mysqli_fetch_assoc($sqlResult)) {
		$hFields[$i]['name']=$hRow['Field'];
		if(true || !empty($hRow['Default'])) $hFields[$i]['default']=$hRow['Default'];
		if(isset($hTranslation["{$sqlTable}.{$hRow['Field']}.alias"])) $hFields[$i]['alias'] = $hTranslation["{$sqlTable}.{$hRow['Field']}.alias"];
		else $hFields[$i]['alias'] = $hRow['Field'];
		
		// matches Field && Type against definition
		foreach($hFieldTypeDefinition as $sFieldType => $hTest) {
			if(empty($hTest['tableNameMatches']) || preg_match($hTest['tableNameMatches'],$sqlTable)==1) {
				if(!empty($hTest['fieldNameMatches']) && preg_match($hTest['fieldNameMatches'],$hRow['Field'])==1) {
					$hFields[$i]['type']=$sFieldType;
				}
				elseif(!empty($hTest['fieldTypeMatches']) && preg_match($hTest['fieldTypeMatches'],$hRow['Type'])==1) {
					$hFields[$i]['type']=$sFieldType;
				}
				elseif(!empty($hTest['fieldKeyMatches']) && @preg_match($hTest['fieldKeyMatches'],$hRow['Key'])==1) {
					$hFields[$i]['type']=$sFieldType;
				}
				elseif(!empty($hTest['fieldTypeMatches']) && $hTest['fieldTypeMatches']==$hRow['Type']) {
					$hFields[$i]['type']=$sFieldType;
				}
			}
		}
		// if no match found type will be as described by mysql
		if(empty($hFields[$i]['type'])) {
			if(strpos($hRow['Type'], '(')>0)
				$hFields[$i]['type']=substr($hRow['Type'], 0, strpos($hRow['Type'], '('));
			else
        		$hFields[$i]['type']=$hRow['Type'];
		}
		// get the length of the field 0 if meaningless
		if(strpos($hRow['Type'], '(')) {
			$hFields[$i]['len']=substr($hRow['Type'], strpos($hRow['Type'], '('));
			$hFields[$i]['len']=str_replace(array('(',')'),array('',''),$hFields[$i]['len']);
		}
		else {
			$hFields[$i]['len']=0;
		}
		// if the field is enum type (show column retuns enum('value1','value2',...)) get the list of values and number of values
		if($hFields[$i]['type']=='enumList') {
			$hFields[$i]['values']=$hFields[$i]['len'];
			$hFields[$i]['len']=count(explode("','",$hFields[$i]['len']));
		}
		else {
			$hFields[$i]['values']='';
		}
		$i++;
	}
	mysqli_free_result($sqlResult);
	return $hFields;
}

// get the liste of the table fields
function getFields($sqlTable) {
	// Déclaration de variables
	$aFields=array();
	global $DB;
	
	$sqlShow="SHOW COLUMNS FROM `$sqlTable`";
	$sqlResult=mysqli_query($DB, $sqlShow);
	while($hRow=mysqli_fetch_assoc($sqlResult)) {
		$aFields[]=$hRow['Field'];
	}
	mysqli_free_result($sqlResult);
	return $aFields;
}

// gets the list of the table fields that are involved in creating a unique index on the table
function getUniqueIndexes($sqlTable) {
	global $DB;
	$aFields = array();
	$sqlIndexes = "SHOW INDEXES FROM `$sqlTable` WHERE `non_unique` = 0 AND `key_name`<>'PRIMARY'";
	$sqlResult=mysqli_query($DB, $sqlIndexes);
	while($hRow=mysqli_fetch_assoc($sqlResult)) {
		$aFields[]=$hRow['Column_name'];
	}
	mysqli_free_result($sqlResult);
	return $aFields;
}

// create a new record in $sqlTable
// this function creates an empty record before calling to update function
// said update function will inject the data form $hData in the corresponding
// fields of $sqlTable 
function insert($sqlTable,$hData = array()) {
	global $DB;

	$sqlInsert = "INSERT INTO `$sqlTable` (`%s`) VALUES(NULL,'%s')";
	$aFields = getFields($sqlTable);
	$hTableDescription = getTableInfo($sqlTable);
	$aValues = array();
	$fields = implode('`,`',$aFields);
	foreach($aFields as $k => $field) {
		if($field!='id') {
			if(isset($hData[$field]) && !is_array($hData[$field])) {
				if(!get_magic_quotes_gpc()) {
					$aValues[] = addslashes($hData[$field]);
				}
				else {
					$aValues[] = $hData[$field];
				}
			}
			else {
				if(!empty($hTableDescription[$k]['default']) || $hTableDescription[$k]['default']==0) {
					$aValues[] = $hTableDescription[$k]['default'];
				}
				else {
					$aValues[]='';
				}
			}
		}
	}
	$values = implode("','",$aValues);
	$sqlInsert = sprintf($sqlInsert,$fields,$values);
	$bSuccess = mysqli_query($DB, $sqlInsert);
	if($bSuccess === TRUE) {
		$hData4Update = $hData;
		$hData4Update['id'] = mysqli_insert_id($DB);
		update($sqlTable,$hData4Update);
		return $hData['id'];
	}
	else {
		return FALSE;
	}
}

// inject $hData into an existing database record
// injection will be done according to the table's description
// $hData['id'] MUST contain the id of the record to be updated
// entries that do not correspoond with a table field will be ignored
function update($sqlTable,$hData = array()) {
	global $DB;

	$hTableDescription = getTableInfo($sqlTable);
	// building the update request
	$sqlUpdate="UPDATE `$sqlTable` SET ";
	foreach($hTableDescription as $hField) {
		if(isset($hData[$hField['name']])) {
			if(!get_magic_quotes_gpc() && !is_array($hData[$hField['name']])) {
				$hData[$hField['name']] = addslashes($hData[$hField['name']]);
			}
			if(function_exists('update'.ucfirst($hField['type']))) {
				$sqlUpdate.= call_user_func('update'.ucfirst($hField['type']),array('table'=>$sqlTable,'id'=>$hData['id'],'hFieldInfo'=>$hField,'value'=>$hData[$hField['name']]));
			}
			else {
				$sqlUpdate.="`{$hField['name']}`='{$hData[$hField['name']]}', ";
			}
		}
	}
	if(is_int($hData['id'])) {
		$sqlUpdate.="WHERE id={$hData['id']}";
	}
	else {
		$sqlUpdate.="WHERE id='{$hData['id']}'";
	}
	$sqlUpdate=str_replace(', WHERE', ' WHERE', $sqlUpdate);
	mysqli_query($DB, $sqlUpdate);
}

// supprime l'enregistrement de $sqlTable dont l'id est passe en parametre
function delete($sqlTable,$id) {
	global $DB;
	$sqlDelete="DELETE FROM `$sqlTable` WHERE id='$id'";
	mysqli_query($DB, $sqlDelete);
}

// modifie la valeur du champs position pour l'enregistrement
// de $sqlTable dont l'id est passe en parametre de plus
// les positions des enregistrements definis limite par la clause where
// voient leurs positions mises a jour.
function updateRecordPosition($sqlTable,$id,$action,$where='') {
	global $DB;
	
	// récupération de la valeur max de position
	// $query="SELECT COUNT(id) as max FROM $sqlTable WHERE $where";
	$query="SELECT MAX(position) as max FROM `$sqlTable` WHERE $where";
	$pSqlRes=mysqli_query($DB, $query,$DB);
	if($hRow=@mysqli_fetch_assoc($pSqlRes)) $max=$hRow['max'];

	// gestion des position nulles
	$query="SELECT id FROM $sqlTable WHERE position=0";
	$pSqlRes=mysqli_query($DB, $query,$DB);
	while($hRow=@mysqli_fetch_assoc($pSqlRes)) {
		$max++;
		$update="UPDATE `$sqlTable` SET position=$max WHERE id=".$hRow['id'];
		mysqli_query($DB, $update,$DB);
	}
	
	// récupération de l'enregistrement en cours
	$query="SELECT position FROM `$sqlTable` WHERE id=$id";

	$pSqlRes=mysqli_query($DB, $query,$DB);
	if($hRow=@mysqli_fetch_assoc($pSqlRes)) $old=$hRow['position'];
	// récupération de la position du ou des enregistrements a modifier
	if($action=='up') {
		$query="SELECT id,position FROM `$sqlTable` WHERE position<$old AND $where ORDER BY position DESC LIMIT 0,1";
	}
	if($action=='down') {
		$query="SELECT id, position FROM `$sqlTable` WHERE position>$old AND $where ORDER BY position ASC LIMIT 0,1";
	}
	$pSqlRes=mysqli_query($DB, $query,$DB);
	if($hRow=@mysqli_fetch_assoc($pSqlRes)) {
		$new=$hRow['position'];
		// mise en position temporaire de l'enregistrement en cours
		$update="UPDATE `$sqlTable` SET position=$new WHERE id=$id";
		mysqli_query($DB, $update,$DB);
		
		// mise à jour des enregistrement de rang $new sauf celui sur lequel on travaille
		$update="UPDATE `$sqlTable` SET position=$old WHERE position=$new AND NOT(id=$id) AND $where";
		mysqli_query($DB, $update,$DB);
	}
}

// mofication des positions des contenu des champs file_*[*]
// attention prevoir un renommage des fichiers afin de conserver
// la coherence de nommage des fichiers
function updateFieldContentPosition($sqlTable, $id, $field, $action) {

	global $DB;
	// récuperation de la base du nom du champ
	@list($fieldBaseName,$fieldOldIndex)=explode('[',$field);
	
	$fieldOldName=$field;
	$fieldOldIndex=substr($fieldOldIndex,0,-1);
	
	// récupération des champs à intervertir
	if($action=='down') {
		$fieldNewIndex=$fieldOldIndex+1;
	}
	elseif($action=='up') {
		$fieldNewIndex=$fieldOldIndex-1;
	}

	$fieldNewName=$fieldBaseName."[$fieldNewIndex]";

	// récupération des données de l'enregistrement
	$query="SELECT * from `$sqlTable` where id=$id";
	$pSqlRes=mysqli_query($DB, $query,$DB);
	$hData=mysqli_fetch_assoc($pSqlRes);
	mysqli_free_result($pSqlRes);

	// récupération des extensions des references des fichiers contenus dans les deux champs
	$fieldOldExtension=strrchr($hData[$fieldOldName],'.');
	$fieldNewExtension=strrchr($hData[$fieldNewName],'.');

	$fileNameStructure='%s%s.%s.%s.%s.%s';
	$path=$GLOBALS['UGML']['CONF']['UPLOAD_PATH'];
	$fieldBaseName=substr($fieldBaseName,5);

	if(empty($hData[$fieldOldName]) and empty($hData[$fieldNewName])) {
		return "";
	}
	elseif(empty($hData[$fieldOldName])) {
		rename($GLOBALS['UGML']['CONF']['UPLOAD_PATH'].$hData[$fieldNewName],$GLOBALS['UGML']['CONF']['UPLOAD_PATH']."$sqlTable.$id.$fieldBaseName.$fieldOldIndex$fieldNewExtension");
		$update="UPDATE `$sqlTable` SET `$fieldNewName`='', `$fieldOldName`='$sqlTable.$id.$fieldBaseName.$fieldOldIndex$fieldNewExtension' WHERE id=".$id;
	}
	elseif(empty($hData[$fieldNewName])) {
		if($fieldNewIndex<0 or !isset($hData[$fieldNewName])) return "";
		rename($GLOBALS['UGML']['CONF']['UPLOAD_PATH'].$hData[$fieldOldName],$GLOBALS['UGML']['CONF']['UPLOAD_PATH']."$sqlTable.$id.$fieldBaseName.$fieldNewIndex$fieldOldExtension");
		$update="UPDATE `$sqlTable` SET `$fieldOldName`='', `$fieldNewName`='$sqlTable.$id.$fieldBaseName.$fieldNewIndex$fieldOldExtension' WHERE id=".$id;
	}
	else {
		rename($GLOBALS['UGML']['CONF']['UPLOAD_PATH'].$hData[$fieldOldName],$GLOBALS['UGML']['CONF']['UPLOAD_PATH']."tmp");
		rename($GLOBALS['UGML']['CONF']['UPLOAD_PATH'].$hData[$fieldNewName],$GLOBALS['UGML']['CONF']['UPLOAD_PATH']."$sqlTable.$id.$fieldBaseName.$fieldOldIndex$fieldNewExtension");
		rename($GLOBALS['UGML']['CONF']['UPLOAD_PATH']."tmp",$GLOBALS['UGML']['CONF']['UPLOAD_PATH']."$sqlTable.$id.$fieldBaseName.$fieldNewIndex$fieldOldExtension");
		$update="UPDATE `$sqlTable` SET `$fieldOldName`='$sqlTable.$id.$fieldBaseName.$fieldOldIndex$fieldNewExtension', `$fieldNewName`='$sqlTable.$id.$fieldBaseName.$fieldNewIndex$fieldOldExtension' WHERE id=".$id;
	}
	mysqli_query($DB, $update,$DB);
}

// exports data from a mysql table into a csv file
// said file will be saved in DOWNLOAD_PATH
// if file already existed before call, it will be overwritten
function exportCsv($hArgs) {
	global $DB;
	global $hTranslation;
	
	$hDefault=array(
		'delimiter'=>';',
		'enclosure'=>'"',
		'withIds'=>false,
		'withFieldNames'=>true
		);
		
	extract(array_merge($hDefault,$hArgs));
	
	$pFile=fopen("{$GLOBALS['UGML']['CONF']['DOWNLOAD_PATH']}{$table}.csv",'w');
	
	if(true===$withFieldNames) {
		$aRow=getFields($table);
		for($i=0;$i<count($aRow);$i++) {
			if(!empty($hTranslation["{$table}.{$aRow[$i]}.alias"])) $aRow[$i] = $hTranslation["{$table}.{$aRow[$i]}.alias"];
		}
		if(false===$withIds) {
			array_shift($aRow);
		}
		fputcsv($pFile,$aRow,$delimiter,$enclosure);
	}
	
	$sqlSelect="SELECT * FROM `$table`";
	$sqlResult=mysqli_query($DB, $sqlSelect);
	while($aRow=mysqli_fetch_row($sqlResult)) {
		if(false===$withIds) {
			array_shift($aRow);
		}
		fputcsv($pFile,$aRow,$delimiter,$enclosure);
	}
	fclose($pFile);
}

// import data from csv file into a single mysql table
// first row (potentialy fieldnames) can be ignored
// first column (potentialy auto incremental id) can be ignored as well
function importCsv($hArgs) {
	global $DB;
	$iCurrentLine=0;
	$iStartingLine=0;
	
	$hDefault=array(
		'file'=>$_FILES['import']['tmp_name'],
		'delimiter'=>';',
		'enclosure'=>'"',
		'withIds'=>false,
		'withFieldNames'=>false
		);
	extract(array_merge($hDefault,$hArgs));
	
	// sets the starting line to one in order to ignore the first one if it contains column names
	if(true===$withFieldNames) $iStartingLine=1;
	
	$hFieds=getTableInfo($table);
	$pFile=fopen($file,"r");
	while($aRow=fgetcsv($pFile,10000,$delimiter,$enclosure)) {
		// procedes from the starting line
		if($iCurrentLine>=$iStartingLine) {
			// ignores the first column if necessary
			if(true===$withIds) array_shift($aRow);
			$sqlInsert="INSERT INTO `$table` SET ";
			foreach($aRow as $i=>$sValue) {
				$sqlInsert.="`{$hFieds[$i+1]['name']}`='".addslashes(trim($sValue))."', ";
			}
			// cleaning request from extra ", " string
			$sqlInsert=substr($sqlInsert,0,-2).';';
			// running request
			mysqli_query($DB, $sqlInsert);
			// die($sqlInsert);
		}
		$iCurrentLine++;
	}
}

function showIndexFullText($sqlTable) {
	global $DB;
	$indexes=array();
	$sqlQuery="SHOW INDEXES FROM `$sqlTable` WHERE Index_type='FULLTEXT'";
	$pSqlRes=mysqli_query($DB, $sqlQuery,$DB);
	while($hData=@mysqli_fetch_assoc($pSqlRes)) {
		$indexes[]=$hData['Column_name'];
	}
	return $indexes;
}
?>