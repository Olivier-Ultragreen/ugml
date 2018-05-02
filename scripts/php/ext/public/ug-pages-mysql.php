<?php
function selectRawData($hArgs) {
	static $iOccurrence=0;
	global $DB;
	$sOutput='';

	// defines default values of args
	$hDefault=array(
		'query'	 => '',
		'nl2Br'	 => false
	);
	// get input vars from hArgs and complete them with reviously defined default values
	extract(array_merge($hDefault,$hArgs));

	// checks the input args and logs warnings if necessary
	if(!isset($query) or empty($query)) {
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : empty argument ".print_r($hArgs,true);
	}
	if(stristr($query,'select ')!=$query) {
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : this query is not a select query";
		$iOccurrence++;
		return '';
	}
	// if no problems detected, query db to get the data
	if(!isset($GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence])) {
		$sqlResult=mysqli_query($DB,$query);
		if($sqlResult and list($sOutput)=mysqli_fetch_row($sqlResult)) {
			if($nl2Br==true) $sOutput = nl2br($sOutput);
			mysqli_free_result($sqlResult);
		}
	}
	$iOccurrence++;
	return $sOutput;
}
function selectQueryToTpl($hArgs) {
	static $iOccurrence=0;
	global $DB;
	
	$sOutput='';
	$i=1;
	
	// defines default values of args
	$hDefault=array(
		'query'	 => '',
		'tpl'	 => '',
		'limit'	 => 0,
		'nl2br'	 => false
	);
	// get input vars from hArgs and complete them with reviously defined default values
	extract(array_merge($hDefault,$hArgs));
	
	// checks the input args and logs warnings if necessary
	if(!isset($tpl) or empty($tpl)) {
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : empty argument ".print_r($hArgs,true);
		return '';
	}
	if(!isset($query) or empty($query)) {
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : empty argument ".print_r($hArgs,true);
		return '';
	}
	if(stristr($query,'select ')!=$query) {
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : this query is not a select query";
		return '';
	}

	// parsing du template
	$tpl=parseUgml(array('file'=>$tpl));
	
	// print $query.'<br/>';
	
	// si on souhaite une pagination
	if(!empty($iLimit)) {
		// initialisation de la page de resultat affichee
		if(!empty($_GET['iPage'])) {
			$_SESSION['iPage']=$_GET['iPage'];
		}
		if(empty($_SESSION['iPage'])) {
			$_SESSION['iPage']=1;
		}
	
		$iPage=$_SESSION['iPage'];
		$_SESSION['iLimit']=$iLimit;
		
		// calcul du nombre total d'elements renvoyes par la requetes
		$pSqlRes=mysql_query($query,$DB);
		$_SESSION['iTotal']=mysql_num_rows($pSqlRes);
		mysql_free_result($pSqlRes);
		
		// ajout d'une clause limit a $query
		$query.=' LIMIT '.($iPage-1)*$iLimit.",$iLimit;";

		if($_SESSION['iPage']*$iLimit>$_SESSION['iTotal']) {
			$_SESSION['results']=((($_SESSION['iPage']-1)*$iLimit)+1).' [to] '.$_SESSION['iTotal'];
		}
		else {
			$_SESSION['results']=((($_SESSION['iPage']-1)*$iLimit)+1).' [to] '.$_SESSION['iPage']*$iLimit;
		}
	}
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]=$query;
	// print $query.'<br/>';
	$sqlResult = mysqli_query($DB,$query);
	while($hData = mysqli_fetch_assoc($sqlResult)) {
		// ajout d'un selecteur sur le premier element souvent fort utile a nos amis integrateurs
		if($i==1) {
			$hData['cssFirst']='first ';
		}
		else {
			$hData['cssFirst']='';
		}
		$sOutput.=inject(array('tpl'=>$tpl,'data'=>$hData,'nl2br'=>$nl2br));
		$i++;
	}
	mysqli_free_result($sqlResult);
	$iOccurrence++;
	return $sOutput;
}
function selectBranchFromNode($hArgs = array()) {
	static $iOccurrence=0;
	static $sTplMain='';
	static $tplSub='';

	global $DB;
	$sOutput='';

	// recuperation et verification des parametres d'entree
	$hDefault=array(
		'table'=>'pages',
		'where'=>1,
		'startingNodeId'=>0,
		'maxDepth'=>2,
		'depth'=>0,
		'tplParent'=>'../templates/faqParent.tpl',
		'tplChild'=>'../templates/faqChild.tpl',
		'lang'=>$GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['lang'],
		'loadTpl'=>true,
		'nl2br'=>false
	);
	extract(array_merge($hDefault,$hArgs));

	// recuperation des templates sans les interpréter
	if(empty($sTplMain) or $loadTpl==true) {
		// truc pour permettre la pre-injection de donnees HGPC dans les templates
		$sTplMain = inject(array('tpl'=>getTpl($tplParent),'data'=>dataHGPC()));
		$tplSub = inject(array('tpl'=>getTpl($tplChild),'data'=>dataHGPC()));
	}
	$hDataMain['children']='';

	// recuperation des donnees dont le parent est $parentId
	$sqlSelect = "SELECT * FROM `$table` WHERE $where AND `id_parent`='$startingNodeId' ORDER BY `id` ASC";
	$sqlResult = mysqli_query($DB,$sqlSelect);
	while($sqlResult and $hDataRow = mysqli_fetch_assoc($sqlResult)) {
		if(empty($hDataRow['title']) and !empty($hDataRow["label_$lang"])) {
			$hDataRow['title'] = $hDataRow["label_$lang"];
		}
		if(!empty($hDataRow["label_$lang"])) {
			$hDataRow['label'] = $hDataRow["label_$lang"];
		}
		if(!empty($hDataRow['text_txt']) && !empty($nl2br)) {
			$hDataRow['text_txt'] = nl2br($hDataRow['text_txt']);
		}
		$hDataMain['lvl']=$depth+1;
		$hDataMain['lvl+1']=$depth+2;
		$hData[$hDataRow['id']]=$hDataRow;
	}
	@mysqli_free_result($sqlResult);
	if($depth>=$maxDepth or empty($hData)) return '';

	// parcours des enregistrements recuperes
	foreach($hData as $id => $value) {
		// recuperation des enfants de l'enregistrement en cours
		$value['children'] = selectBranchFromNode(array('table'=>$table,'where'=>$where,'startingNodeId'=>$id,'maxDepth'=>$maxDepth,'depth'=>$depth+1, 'loadTpl'=>false,'nl2br'=>$nl2br));
		$hDataMain['children'].=inject(array('tpl'=>$tplSub,'data'=>$value,'nl2br'=>false));
	}
	$iOccurrence++;
	return inject(array('tpl'=>$sTplMain,'data'=>$hDataMain,'nl2br'=>false));
}

?>