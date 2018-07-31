<?php
function paging($hArgs = array()) {
	// recuperation et verification des parametres d'entree
	$hDefault=array(
		'table'=>'',
		'limit'=>'10',
		'where'=>'1=1',
		'tpl'=>'../templates/bckffc/bckffc-list-paging.tpl'
		);
	extract(array_merge($hDefault,$hArgs));
	
	$sOutput=getTpl($tpl);
	
	global $DB;
	
	$hData=array();
	$hDataMain['script']='./'.basename($_SERVER['PATH_TRANSLATED']);

	// si la limit est 'no' on renvoi une chaine vide
	if($limit==0) return '';

	// calcul du nombre total de page
	$sqlForCounting	 ="SELECT `id` FROM `$table` WHERE $where";
	$sqlResult		 =mysqli_query($DB, $sqlForCounting);
	$iTotal			 =mysqli_num_rows($sqlResult);
	
	$hDataMain['iTotal'] = ceil($iTotal/$limit);
	mysqli_free_result($sqlResult);
	
	// recuperation de la page en cours
	if(!empty($_GET['bckffc-mysql-paging']) && $_GET['bckffc-mysql-paging']<=$hDataMain['iTotal']) {
		$hDataMain['iPage']=$_GET['bckffc-mysql-paging'];
	}
	elseif(!empty($_SESSION['bckffc-mysql']['iPage']) && $_SESSION['bckffc-mysql']['iPage']<=$hDataMain['iTotal']) {
		$hDataMain['iPage']=$_SESSION['bckffc-mysql']['iPage'];
	}
	else {
		$hDataMain['iPage']=1;
	}
	
	// stockage de la page en cours en session pour conservation du contexte
	$_SESSION['bckffc-mysql']['iPage']=$hDataMain['iPage'];
	
	// recuperation des sDataCells restant a injecter dans le template
	// $sOutput=parseUgml(array('file'=>$tplMain));
	if($hDataMain['iPage']==1 && $hDataMain['iTotal']==$hDataMain['iPage']) {
		$hDataMain['sCssClassPreviousPage']='invisible';
		$hDataMain['iPreviousPage']=1;
		
		$hDataMain['sCssClassNextPage']='invisible';
		$hDataMain['iNextPage']=1;
	}
	elseif($hDataMain['iPage']==1 && $hDataMain['iTotal']>$hDataMain['iPage']) {
		$hDataMain['sCssClassPreviousPage']='invisible';
		$hDataMain['iPreviousPage']=1;
		
		$hDataMain['sCssClassNextPage']='visible';
		$hDataMain['iNextPage']=2;
	}
	elseif($hDataMain['iPage']==$hDataMain['iTotal'] && $hDataMain['iPage']>1) {
		$hDataMain['sCssClassPreviousPage']='visible';
		$hDataMain['iPreviousPage']=$hDataMain['iPage']-1;
		
		$hDataMain['sCssClassNextPage']='invisible';
		$hDataMain['iNextPage']=$hDataMain['iPage'];
	}
	else {
		$hDataMain['sCssClassPreviousPage']='visible';
		$hDataMain['iPreviousPage']=$hDataMain['iPage']-1;
		
		$hDataMain['sCssClassNextPage']='visible';
		$hDataMain['iNextPage']=$hDataMain['iPage']+1;
	}
	$sOutput=inject(array('data'=>$hDataMain,'tpl'=>$sOutput));
	
	return $sOutput;
}
function showTable($hArgs = array()) {
	// recuperation et verification des parametres d'entree
	$hDefault=array(
		'table'=>'',
		'limit'=>'10',
		'itemActions'=>'',
		'groupActions'=>'',
		'where'=>'1=1'
		);
	extract(array_merge($hDefault,$hArgs));

	$sOutput=getTpl('../templates/bckffc/bckffc-list.tpl');
	$hDataMain=array('header'=>'','data'=>'','globalActions'=>'','paging'=>'');
	
	global $DB;
	$lang=$GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['lang'];

	$aItemActions=explode(',',$itemActions);
	$aGlobalActions=explode(',',$globalActions);
	$aGroupActions = explode(',',$groupActions);

	$mFields=getTableInfo($table);

	// determines which field on which the records will be ordered by
	if(!empty($_GET['sOrderBy']) && in_array($_GET['sOrderBy'],getFields($table))) $sOrderBy=$_GET['sOrderBy'];
	elseif(!empty($_SESSION['sOrderBy']) && in_array($_SESSION['sOrderBy'],getFields($table))) $sOrderBy=$_SESSION['sOrderBy'];
	else $sOrderBy='id';

	$_SESSION['sOrderBy']=$sOrderBy;
	
	// determines whether the records will be displayed in ascending or descending way
	if(!empty($_GET['sOrderDirection']) && $_GET['sOrderDirection']=='DESC') $sOrderDirection='DESC';
	elseif(!empty($_GET['sOrderDirection']) && $_GET['sOrderDirection']=='ASC') $sOrderDirection='ASC';
	elseif(!empty($_SESSION['sOrderDirection']) && $_SESSION['sOrderDirection']=='ASC') $sOrderDirection='ASC';
	elseif(!empty($_SESSION['sOrderDirection']) && $_SESSION['sOrderDirection']=='DESC') $sOrderDirection='DESC';
	else $sOrderDirection='ASC';
	
	$_SESSION['sOrderDirection']=$sOrderDirection;

	$hDataMain['paging']=paging($hArgs);
	
	// construction du header du tableau
	$tplTableHeader=getTpl('../templates/bckffc/bckffc-list-table-header-row.tpl');
	$tplTableHeaderCell=getTpl('../templates/bckffc/bckffc-list-table-header-cell.tpl');
	$hDataHeader=array('sColumnTitles'=>'','bckffcActions'=>'');
	
	foreach($mFields as $i => $hFieldInfo) {
		$hDataTableHeaderCell=array();
		if(empty($GLOBALS['UGML']['CONF'][$table.'.'.$hFieldInfo['name'].'.hidden'])) {
			if($hFieldInfo['name']==$_SESSION['sOrderBy'] and $sOrderDirection=='ASC') {
				$hDataTableHeaderCell['sCssClassOrder']="orderDirectionAsc";
				$hDataTableHeaderCell['sOrderBy']=$hFieldInfo['name'];
				$hDataTableHeaderCell['sOrderDirection']='DESC';
				$hDataTableHeaderCell['sFieldName']=$hFieldInfo['alias'];
			}
			elseif($hFieldInfo['name']==$_SESSION['sOrderBy'] and $sOrderDirection=='DESC') {
				$hDataTableHeaderCell['sCssClassOrder']="orderDirectionDesc";
				$hDataTableHeaderCell['sOrderBy']=$hFieldInfo['name'];
				$hDataTableHeaderCell['sOrderDirection']='ASC';
				$hDataTableHeaderCell['sFieldName']=$hFieldInfo['alias'];
			}
			else {
				$hDataTableHeaderCell['sCssClassOrder']="orderDirectionDesc";
				$hDataTableHeaderCell['sOrderBy']=$hFieldInfo['name'];
				$hDataTableHeaderCell['sOrderDirection']='ASC';
				$hDataTableHeaderCell['sFieldName']=$hFieldInfo['alias'];
			}
			$hDataHeader['sColumnTitles'].=hashIntoTpl($tplTableHeaderCell,$hDataTableHeaderCell);
		}
	}
	if($itemActions!='none') $hDataHeader['bckffcActions']='<th class="action">[bckffcActions]</th>';
	if($groupActions!='none') {
		$hDataHeader['recordSelector'] = '<th align="left"><input type="checkbox" class="reverse" name="selection" value="all"></th>';
	}
	else {
		$hDataHeader['recordSelector'] = '';
	}
	
	$hDataMain['header']=hashIntoTpl($tplTableHeader,$hDataHeader);

	// construction de la requête
	$sqlSelect="SELECT * FROM `$table` WHERE $where";
	$sqlLimit='';
	if($limit!='none' and !empty($_SESSION['bckffc-mysql']['iPage'])) {
		$start=($_SESSION['bckffc-mysql']['iPage']-1)*$limit;
		$sqlLimit="LIMIT $start, $limit";
	}
	$sqlSelect.=" ORDER BY `$sOrderBy` $sOrderDirection $sqlLimit";
	
	$tplRow=			getTpl('../templates/bckffc/bckffc-list-table-data-row.tpl');
	$tplCell=			getTpl('../templates/bckffc/bckffc-list-table-data-cell.tpl');
	$tplActionForm=		getTpl('../templates/bckffc/bckffc-list-table-action-link.tpl');
	
	// execution de la requete
	$sqlResult = mysqli_query($DB, $sqlSelect);
	// construction du tableau et affichage des données
	while($sqlResult && $hRow = mysqli_fetch_assoc($sqlResult)) {
		$hDataRow=array('sDataCells'=>'','actions'=>'');
		
		$hDataRow['sCssClassRow']='';
		// en fonction de l'enregistrement traite
		if((!empty($_GET['id']) and $hRow['id']==$_GET['id']) or (!empty($_POST['id']) and $hRow['id']==$_POST['id'])) {
			$hDataRow['sCssClassRow']=' class="selected"';
		}

		
		// recuperation de l'id en cours pour injection ulterieure
		$hDataRow['id']=$hRow['id'];
		foreach($mFields as $i => $hFieldInfo) {
			$hFieldInfo['value']=$hRow[$hFieldInfo['name']];
			// gestion conditionnelle de l'affichage des champs
			$hDataCell['classCss']='';
			$hDataCell['sData']='';
			if(empty($GLOBALS['UGML']['CONF'][$table.'.'.$hFieldInfo['name'].'.hidden'])) {
				if($hFieldInfo['type']=='primaryKey' && $groupActions!="none") {
					$hFieldInfo['id']=$hRow['id'];
					$hFieldInfo['value']=$hFieldInfo['value'];
					$hDataCell['sData']=call_user_func("showCellDataPrimaryKey",array('table'=>$table,'hFieldInfo'=>$hFieldInfo));
				}
				elseif($hFieldInfo['type']!='primaryKey' && function_exists("showCellData".ucfirst($hFieldInfo['type']))) {
					$hFieldInfo['id']=$hRow['id'];
					$hFieldInfo['value']=$hFieldInfo['value'];
					$hDataCell['sData']=call_user_func("showCellData".ucfirst($hFieldInfo['type']),array('table'=>$table,'hFieldInfo'=>$hFieldInfo));
				}
				else {
					$hDataCell['sData']=excerpt(strip_tags($hFieldInfo['value']),100);
				}
				
				// gestion des class css des cellule appartenant a la colonne sur laquelle le tri en cours est fait
				if($hFieldInfo['name']==$sOrderBy and $sOrderDirection=='DESC' and !empty($hDataCell['classCss'])) {
					$class.=' order_desc"';
				}
				elseif($hFieldInfo['name']==$sOrderBy and $sOrderDirection=='ASC' and !empty($hDataCell['classCss'])) {
					$class.=' order_asc"';
				}
				elseif($hFieldInfo['name']==$sOrderBy and $sOrderDirection=='DESC') {
					$class='class="order_desc"';
				}
				elseif($hFieldInfo['name']==$sOrderBy and $sOrderDirection=='ASC') {
					$class='class="order_asc"';
				}
				
				$hDataRow['sDataCells'].=hashIntoTpl($tplCell,$hDataCell);
			}
		}
		
		if($itemActions!='none') {
			$hDataAction=array();
			// afin de revenir sur la page en cours
			// réintroduction du numero de page dans l'URL 
			if(!empty($_GET['bckffc-mysql-paging'])) $hDataActions['iPage']=$_GET['bckffc-mysql-paging'];
			else $hDataActions['iPage']='1';
			
			$hDataActions['id']=$hRow['id'];
			
			// affichage des liens actions
			foreach($aItemActions as $sAction) {
				if(function_exists('showCellAction'.ucfirst($sAction))) {
					$hDataRow['actions'].=call_user_func('showCellAction'.ucfirst($sAction),$hRow);
				}
				else {
					$hDataAction['label']='[bckffcAction'.ucfirst($sAction).']';
					$hDataAction['classCss']='bckffcAction'.ucfirst($sAction);
					$hDataAction['code']=$sAction;
					if($limit!='none' and !empty($_SESSION['bckffc-mysql']['iPage'])) $hDataAction['iPage']=$_SESSION['bckffc-mysql']['iPage'];
					$hDataRow['actions'].=hashIntoTpl($tplActionForm,$hDataAction);
				}
			}
			$hDataRow['actions']='<td class="action">'.$hDataRow['actions'].'</td>';
		}
		$hDataMain['data'].=hashIntoTpl($tplRow,$hDataRow);
	}
	if($sqlResult) mysqli_free_result($sqlResult);
	
	$hDataMain['globalActions']='';
	foreach($aGlobalActions as $sAction) {
		if(!empty($sAction)) {
			$tplActionForm				 = getTpl("../templates/bckffc/bckffc-action-$sAction.tpl");
			$hDataAction['sTable']		 = $table;
			$hDataAction['label']		 = '[bckffcAction'.ucfirst($sAction).']';
			$hDataAction['classCss']	 = '[bckffcAction'.ucfirst($sAction).']';
			$hDataAction['code']		 = $sAction;
			$hDataMain['globalActions']	.=hashIntoTpl($tplActionForm,$hDataAction);
		}
	}
	// dealing with group actions
	$hDataMain['groupActions']='';
	if($groupActions!='none') {
		$tplGroupActionForm					 = getTpl("../templates/bckffc/bckffc-list-group-actions.tpl");
		$tplGroupActionItem					 = getTpl("../templates/bckffc/bckffc-form-input-select-option.tpl");
		$hGroupActionList['groupActionList'] = '';
		foreach($aGroupActions as $sAction) {
			if(!empty($sAction)) {
				$hGroupActionListItem = array('value'=>$sAction,'label'=>'[bckffcGroupAction'.ucfirst($sAction).']');
				$hGroupActionList['groupActionList'] .= inject(array('data'=>$hGroupActionListItem,'tpl'=>$tplGroupActionItem));
			}
		}
		$hDataMain['groupActions'] = inject(array('data'=>$hGroupActionList,'tpl'=>$tplGroupActionForm));
	}
	return hashIntoTpl($sOutput,$hDataMain);
}
function showForm($hArgs = array()) {
	$hDefault = array('table'=>'','id'=>(isset($_GET['id']))?$_GET['id']:'');
	extract(array_merge($hDefault,$hArgs));
	
	$sOutput=			getTpl('../templates/bckffc/bckffc-form.tpl');
	
	global $DB,$hTranslation;
	
	$lang=$GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['lang'];
	
	$hDataMain=			array('fieldsets'=>'');
	
	$tplInputHidden=	getTpl('../templates/bckffc/bckffc-form-input-hidden.tpl');
	$tplInputFile=		getTpl('../templates/bckffc/bckffc-form-input-file.tpl');
	$tplInputCheck=		getTpl('../templates/bckffc/bckffc-form-input-checkbox.tpl');

	$tplSelect=			getTpl('../templates/bckffc/bckffc-form-input-select.tpl');
	$tplForeignKey=		getTpl('../templates/bckffc/bckffc-form-input-foreignkey.tpl');
	$tplSelectOption=	getTpl('../templates/bckffc/bckffc-form-input-select-option.tpl');
	
	$tplInputText=		getTpl('../templates/bckffc/bckffc-form-input-text.tpl');
	$tplInputDate=		getTpl('../templates/bckffc/bckffc-form-input-date.tpl');
	
	$tplTextarea=		getTpl('../templates/bckffc/bckffc-form-input-textarea.tpl');
	$tplWysiwyg=		getTpl('../templates/bckffc/bckffc-form-input-wysiwyg.tpl');
	
	if(!empty($id)) {
		$sqlSelect="SELECT * FROM `$table` WHERE `id`='$id'";
		$sqlResult=mysqli_query($DB, $sqlSelect);
		$hFieldValues=mysqli_fetch_assoc($sqlResult);
	}
	$mFields=getTableInfo($table);
		
	foreach($mFields as $i => $hFieldInfo) {
		if(!empty($hFieldValues[$hFieldInfo['name']])) {
			$hFieldInfo['value']=$hFieldValues[$hFieldInfo['name']];
		}
		elseif(!empty($hFieldInfo['default']) || $hFieldInfo['default']==0) {
			$hFieldInfo['value']=$hFieldInfo['default'];
		}
		else $hFieldInfo['value']='';
		// display the form elements correspondaing to the field type 
		// debug($hFieldInfo,'showForm');
		switch($hFieldInfo['type']) {
			case 'richText': {
				$hDataWysiwyg['idHtml']=$hFieldInfo['name'];
				$hDataWysiwyg['label']=$hFieldInfo['alias'];
				$hDataWysiwyg['rte']=newRte($hFieldInfo['name'],rteSafe(trim($hFieldInfo['value'])));
				$hDataMain['fieldsets'].=hashIntoTpl($tplWysiwyg,$hDataWysiwyg);
				break;
			}
			case 'plainText': {
				$tags=array();
				$hDataTextarea['idHtml']=$hFieldInfo['name'];
				$hDataTextarea['label']=$hFieldInfo['alias'];
				$hDataTextarea['name']=$hFieldInfo['name'];
				$hDataTextarea['value']=$hFieldInfo['value'];
				$hDataTextarea['buttons']='';
				foreach($tags as $tag=>$value) {
					$hDataTextarea['buttons'].=hashIntoTpl('<input type="button" class="buttonStyle" name="[tag]" value="[tag]"/>',array('tag'=>$tag));
				}
				$hDataMain['fieldsets'].=hashIntoTpl($tplTextarea,$hDataTextarea);
				break;
			}
			case 'int': {
				$hDataInputText['idHtml']=$hFieldInfo['name'];
				$hDataInputText['name']=$hFieldInfo['name'];
				$hDataInputText['label']=$hFieldInfo['alias'];
				$hDataInputText['value']=$hFieldInfo['value'];
				$hDataInputText['class']=' class="int"';
				$hDataMain['fieldsets'].=hashIntoTpl($tplInputText,$hDataInputText);
				break;
			}
			case 'check': {
				$hDataInputCheck['idHtml']=$hFieldInfo['name'];
				$hDataInputCheck['name']=$hFieldInfo['name'];
				$hDataInputCheck['label']=$hFieldInfo['alias'];
				if(!empty($hFieldInfo['value'])) {
					$hDataInputCheck['checked']=' checked="checked"';
				}
				else {
					$hDataInputCheck['checked']='';
				}
				$hDataMain['fieldsets'].=hashIntoTpl($tplInputCheck,$hDataInputCheck);
				break;
			}
			case 'float': {
				$hDataInputText['idHtml']=$hFieldInfo['name'];
				$hDataInputText['label']=$hFieldInfo['alias'];
				$hDataInputText['value']=$hFieldInfo['value'];
				$hDataInputText['class']=' class="float"';
				$hDataMain['fieldsets'].=hashIntoTpl($tplInputText,$hDataInputText);
				break;
			}
			case 'date': {
				$hDataInputText['idHtml']=$hFieldInfo['name'];
				$hDataInputText['label']=$hFieldInfo['alias'];
				$hDataInputText['value']=$hFieldInfo['value'];
				$hDataMain['fieldsets'].=hashIntoTpl($tplInputDate,$hDataInputText);
				break;
			}
			default: {
				if(function_exists("showFormItem".ucfirst($hFieldInfo['type']))) {
					if(!empty($hFieldValues['id'])) $hFieldInfo['id']=$hFieldValues['id'];
					$hFieldInfo['value']=$hFieldInfo['value'];
					$hDataMain['fieldsets'].=call_user_func("showFormItem".ucfirst($hFieldInfo['type']),array('table'=>$table,'hFieldInfo'=>$hFieldInfo));
				}
				else {
					$hDataInputText['idHtml']=$hFieldInfo['name'];
					$hDataInputText['label']=$hFieldInfo['alias'];
					$hDataInputText['value']=$hFieldInfo['value'];
					$hDataMain['fieldsets'].=hashIntoTpl($tplInputText,$hDataInputText);
				}
				break;
			}
		}
	}
	return inject(array('data'=>$hDataMain,'tpl'=>$sOutput));
}

if(!function_exists('bckffcStd')) {
	/* deprecated used for backward compatibility use bckffcInterface function instead */
	function bckffcStd($hArgs = array()) {bckffcInterface($hArgs);}
}

if(!function_exists('bckffcInterface')) {
/* this is the "main" function of the extension */ 
/* it is the one that should be referenced in the ugd file and used as tag in the ugml page */
function bckffcInterface($hArgs = array()) {
	$hDefault=array(
		'table'			 => '',
		'limit'			 => '10',
		'globalActions'	 => 'add',
		'itemActions'	 => 'edit,delete',
		'defaultAction'	 => 'list',
		'where'			 => '1=1',
		'orderBy'		 => 'id',
		'orderDirection' => 'desc'
	);
	extract(array_merge($hDefault,$hArgs));
	$sOutput = '';
	
	$aGlobalActions = explode(',', $globalActions);
	$aItemActions = explode(',', $itemActions);
	$aGroupActions = explode(',', $groupActions);
	$aFullActionList = array_merge($aGlobalActions,$aItemActions,$aGroupActions);
	
	if(!empty($_REQUEST['action'])) $sCurrentAction = $_REQUEST['action'];
	else $sCurrentAction = $defaultAction;
	
	// checks if the requiered action is within the defined ones
	if(in_array($sCurrentAction,$aFullActionList) || $sCurrentAction == $defaultAction) {
		$sOutput.=call_user_func('bckffcAction'.ucfirst($sCurrentAction),$hArgs);
	}
	return $sOutput;
}
}
if(!function_exists('bckffcActionLogin')) {
function bckffcActionLogin($hArgs = array()) {
	global $DB;
	$hDefault=array(
		'user'		 => 'bckffc_user',
		'tpl'		 => 'bckffc-action-login.tpl',
		'onsuccess'	 => 'bckffc-help'
	);
	extract(array_merge($hDefault,$hArgs));

	$sOutput='';
	$bBckffcLoginFailure = false;
	$onsuccess .= $GLOBALS['UGML']['HTTP_REQUEST_EXT'];
	// TREATMENT MODE
	if(!empty($_POST['action']) && $_POST['action']=='login') {
		// treats data 
		if(!empty($_POST['login']) and !empty($_POST['pwd'])) {
			$sTest		 = $_POST['login'].md5($_POST['pwd']);
			$sqlSelect	 = "SELECT id, `profile` FROM `$user` WHERE concat(login,pwd)='$sTest'";
			$sqlResult	 = mysqli_query($DB,$sqlSelect);
			if($sqlResult && $h = mysqli_fetch_assoc($sqlResult)) {
				mysqli_free_result($sqlResult);
				$_SESSION["_{$user}_id"] = $h['id'];
				$_SESSION["_profile_id"] = $h["profile"];
				header("Location: $onsuccess");
				exit();
			}
			else {
				$bBckffcLoginFailure = true;
			}
		}
	}
	// DISPLAY MODE
	$hInject = array('error-msg'=>'');
	$tpl	 = getTpl($tpl);
	if($bBckffcLoginFailure) {
		$hInject['error-msg'] = '[bckffcLoginFailure]';
	}
	$sOutput = inject(array('data'=>$hInject,'tpl'=>$tpl));
	return $sOutput;
}
}

if(!function_exists('bckffcActionCheckSession')) {
/* 
SECURITY NOTE 
THE FOLLOWING FUNCTIONS DO NOT CHECK IF THE USER EXIST IN THE DB
THIS IS A POTENTIAL SECURITY ISSUE
*/
function bckffcActionCheckSession($hArgs = array()) {
	global $DB;
	$hDefault=array(
		'user'		 => 'bckffc_user',
		'onfailure'	 => 'bckffc-login'
	);
	extract(array_merge($hDefault,$hArgs));

	$sOutput='';
	$bBckffcLoginFailure = false;
	$onfailure .= $GLOBALS['UGML']['HTTP_REQUEST_EXT'];
	// if no credentials are registered in the session
	// die(basename($_SERVER['REQUEST_URI'].' : '.$onfailure) );
	if((empty($_SESSION["_{$user}_id"]) or empty($_SESSION["_profile_id"])) && basename($_SERVER['REQUEST_URI'])!=$onfailure) {
		header("Location: $onfailure");
		exit();
	}
	// if the registered user belongs to a group that does not have access to the file
	if(!empty($GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['group']) and $GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['group']!=$_SESSION["_profile_id"]) {
		header("Location: $onfailure");
		exit();
	}
	return $sOutput;
}
}

if(!function_exists('bckffcActionACLRestrictedNav')) {
function bckffcActionACLRestrictedNav($hArgs = array()) {
	$hDefault = array(
		'path'		 => PROJECT_DIRECTORY.'/'.BCKFFC_DIR,
		'type'		 => $GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['type'],
		'tpl'		 => '../templates/bckffc/bckffc-nav-list-item.tpl',
		'on'		 => 'selected' // css class for currently viewed page
	);
	extract(array_merge($hDefault,$hArgs));
	
	$sOutput='';
	
	$tpl = getTpl($tpl);
	
	$hFiles = selectUgmlFiles(array(
		'from'		 => array($path),
		'retrieve'	 => array('nav','title','group'),
		'where'		 => array("nav=|ok|","type=|$type|"),
		'operator'	 => 'AND',
		'order'		 => 'position',
		'dir'		 => 'ASC'
	));
	foreach($hFiles as $hFile) {
		if(empty($hFile['group']) or (!empty($_SESSION["_profile_id"]) and $hFile['group']==$_SESSION["_profile_id"])) {
			$hFile['classCss'] = '';
			if(basename($GLOBALS['UGML']['REQUESTED_UGML_FILE']['FILENAME'])==basename($hFile['filename'])) {
				$hFile['classCss']=" class=\"$on\""; 
			}
			list($hFile['url']) = explode('.',basename($hFile['filename']));
			$sOutput .= inject(array('data'=>$hFile,'tpl'=>$tpl));
		}
	}
	
	return $sOutput;
}
}

if(!function_exists('bckffcActionList')) {
// displays the list of records in the DB table
function bckffcActionList($hArgs = array()) {
	return showTable($hArgs);
}
}

if(!function_exists('bckffcActionAdd')) {
function bckffcActionAdd($hArgs = array()) {
	$hDefault = array(
		'table'=>'',
		'limit'=>'10',
		'globalActions'=>'add',
		'itemActions'=>'edit,delete',
		'defaultAction'=> 'list',
		'where'=>'1=1',
		'orderBy'=>'id',
		'orderDirection'=>'desc'
	);
	extract(array_merge($hDefault,$hArgs));

	$sOutput='';
	
	// TREATMENT MODE
	if(!empty($_POST['action'])) {
		// treats data 
		if(empty($_POST['id'])) {
			$hData = $_POST;
			$hData['inserted_at']=date('Y-m-d H:i:s');
			insert($table,array_merge($hData,$_FILES));
		}
		header("Location: {$_SERVER['REQUEST_URI']}");
	}
	// DISPLAY MODE
	if(!empty($_GET['action'])) {
		// displays action specific interface case in point add
		$sOutput.=showForm($hArgs);
	}
	return $sOutput;
}
}

if(!function_exists('bckffcActionImport')) {
	function bckffcActionImport($hArgs = array()) {
		global $aCryptedFields;
		$hDefault=array();
		extract(array_merge($hDefault,$hArgs));
		$sOutput='';
		// TREATMENT MODE
		if(!empty($_POST['action'])) {
			if(!empty($_FILES['import']) and $_FILES['import']['error']==0) {
				$hFields = getTableInfo($table);
				$pFile = fopen($_FILES['import']['tmp_name'],"r");
				while($aRow=fgetcsv($pFile,10000,';','"')) {
					$hData = array();
					// mise sous forme de tableau associatif
					foreach($aRow as $i => $value) {
						if(empty($value) && !empty($hFields[$i+1]['default'])) $value = $hFields[$i+1]['default'];
						$hData[$hFields[$i+1]['name']] = $value;
					}
					foreach($hFields as $i => $hField) {
						if($hField['name']!='id' && empty($hData[$hField['name']])) {
							$hData[$hField['name']] = $hField['default'];
						}
					}
					// filtrage des données pour injection en base
					// printArray($hData);
					$hData['inserted_at'] = date('Y-m-d H:i:s');
					insert($table,$hData);
				}
			}
			header("Location: {$_SERVER['REQUEST_URI']}");
		}
	}
}

if(!function_exists('bckffcActionExport')) {
function bckffcActionExport($hArgs = array()) {
	$hDefault = array(
		'table'=>'',
		'delimiter'=>';',
		'enclosure'=>'"',
		'where'=>'1=1'
	);
	extract(array_merge($hDefault,$hArgs));
	global $DB,$aCryptedFields;
	if(!empty($_GET['action'])) {
		$mFields = getTableInfo($table);
		array_shift($mFields); // suppression de l'ID
		$aFieldNames = array();
		foreach($mFields as $i => $hFieldInfo) {
			$aFieldNames[] = $hFieldInfo['alias'];
		}
		$pFile = fopen("{$GLOBALS['UGML']['CONF']['DOWNLOAD_PATH']}{$table}.csv",'w');
		fputcsv($pFile,$aFieldNames,$delimiter,$enclosure);
		
		$sqlSelect="SELECT * FROM `$table` WHERE $where";
		$sqlResult = mysqli_query($DB, $sqlSelect);
		// construction du tableau et affichage des données
		while($sqlResult && $hRow = mysqli_fetch_assoc($sqlResult)) {
			array_shift($hRow); // suppression de l'ID
			$aRow = array();
			foreach($mFields as $i => $hFieldInfo) {
				$hFieldInfo['value'] = $hRow[$hFieldInfo['name']];
				if(function_exists("showCellData".ucfirst($hFieldInfo['type']))) {
					$aRow[] = call_user_func("showCellData".ucfirst($hFieldInfo['type']),array('table'=>$table,'hFieldInfo'=>$hFieldInfo));
				}
				else {
					$aRow[] = $hFieldInfo['value'];
				}
			}
			fputcsv($pFile,$aRow,$delimiter,$enclosure);
		}
		mysqli_free_result($sqlResult);
		fclose($pFile);
		
		header("Content-Disposition: attachment; filename=$table.csv");
		header("Content-Type: application/force-download");
		print str_replace(array('&nbsp;'),array(' '),utf8_decode(file_get_contents($GLOBALS['UGML']['CONF']['DOWNLOAD_PATH']."$table.csv")));
		unlink($GLOBALS['UGML']['CONF']['DOWNLOAD_PATH']."$table.csv");
		exit();
	}
}
}

// actions on existing records
if(!function_exists('bckffcActionEdit')) {
function bckffcActionEdit($hArgs = array()) {
	static $iOccurrence=0;
	$sOutput='';

	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);
	
	$hDefault=array(
		'table'=>'',
		'limit'=>'10',
		'globalActions'=>'add',
		'itemActions'=>'edit,delete',
		'defaultAction'=> 'list',
		'where'=>'1=1',
		'orderBy'=>'id',
		'orderDirection'=>'desc'
	);
	extract(array_merge($hDefault,$hArgs));

	// place your smart code here

	// TREATMENT MODE
	if(!empty($_POST['action'])) {
		// treats data 
		if(!empty($_POST['id'])) {
			$hData = array_merge($_POST,$_FILES);
			$hData['updated_at']=date('Y-m-d H:i:s');
			update($table,$hData);
		}
		header("Location: {$_SERVER['REQUEST_URI']}");
		exit();
	}
	// DISPLAY MODE
	if(!empty($_GET['action'])) {
		// displays action specific interface case in point edit
		$sOutput.=showForm($hArgs);
	}
	
	// end of your smart code
	
	$iOccurrence++;
	return $sOutput;
}
}

if(!function_exists('bckffcActionCopy')) {
function bckffcActionCopy($hArgs = array()) {
	global $DB;
	static $iOccurrence=0;
	$sOutput='';

	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);
	
	$hDefault=array(
		'table'=>'',
		'limit'=>'10',
		'globalActions'=>'add',
		'itemActions'=>'edit,delete',
		'defaultAction'=> 'list',
		'where'=>'1=1',
		'orderBy'=>'id',
		'orderDirection'=>'desc'
	);
	extract(array_merge($hDefault,$hArgs));

	// place your smart code here
	if(!empty($_GET['action']) && !empty($_GET['id'])) {
		$sqlSelect="SELECT * FROM `{$table}` WHERE `id`='{$_GET['id']}'";
		$sqlResult = mysqli_query($DB,$sqlSelect);
		if($sqlResult && $hData = mysqli_fetch_assoc($sqlResult)) {
			unset($hData['id']);
			$hData['inserted_at'] = date('Y-m-d H:i:s');
			$hData['updated_at'] = '0000-00-00 00:00:00';
			insert($table,$hData);
			header("Location: {$_SERVER['HTTP_REFERER']}");
			exit();
		}
	}
	// end of your smart code
	$iOccurrence++;
	return $sOutput;
}
}

if(!function_exists('bckffcActionDelete')) {
function bckffcActionDelete($hArgs = array()) {
	global $DB;
	static $iOccurrence=0;
	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);
	
	$hDefault=array(
		'table'=>'',
		'limit'=>'10',
		'globalActions'=>'add',
		'itemActions'=>'edit,delete',
		'defaultAction'=> 'list',
		'where'=>'1=1',
		'orderBy'=>'id',
		'orderDirection'=>'desc'
	);
	extract(array_merge($hDefault,$hArgs));

	$sOutput='';
	
	// place your smart code here
	// TREATMENT MODE
	if(!empty($_REQUEST['action'])) {
		// treats data 
		if(!empty($_POST['ids'])) {
			$sqlDelete = "DELETE FROM `$table` WHERE `id` IN({$_POST['ids']})";
			mysqli_query($DB,$sqlDelete);
		}
		if(!empty($_GET['id'])) {
			delete($table,$_GET['id']);
		}
		header("Location: {$_SERVER['HTTP_REFERER']}");
		exit();
	}
	// end of your smart code
	$iOccurrence++;
	return $sOutput;
}
}

if(!function_exists('showCellDataPrimaryKey')) {
// standard functions to display data in list mode
function showCellDataPrimaryKey($hArgs = array()) {
	global $DB;
	static $iOccurrence=0;
	$sOutput='';

	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);

	$hDefault=array();
	extract(array_merge($hDefault,$hArgs));
	// gets the current language
	$lang = $GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['lang'];
	
	// place your smart code here
	if(!empty($hFieldInfo['value'])) {
		$sOutput = '<input class="id" type="checkbox" name="'.$hFieldInfo['name'].'[]" value="'.$hFieldInfo['value'].'"/></td><td>'.$hFieldInfo['value'];
	}
	else {
		$sOutput = '';
	}
	// end of your smart code
	
	$iOccurrence++;
	return $sOutput;
}
}

if(!function_exists('showCellDataForeignKey')) {
function showCellDataForeignKey($hArgs = array()) {
	global $DB;
	static $iOccurrence=0;
	$sOutput='';

	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);

	$hDefault=array();
	extract(array_merge($hDefault,$hArgs));
	// gets the current language
	$lang = $GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['lang'];
	
	// place your smart code here
	if(!empty($hFieldInfo['value'])) {
		// gets the table name from the field's name
		$sqlTable=substr($hFieldInfo['name'],3);
		// if table contains language specific marker
		if(substr($sqlTable,-3)=="_$lang")
			$sqlSelectParent	 = "SELECT `label` as label FROM `$sqlTable` WHERE `id` IN({$hFieldInfo['value']})";
		else
			$sqlSelectParent	 = "SELECT `label_$lang` as label FROM `$sqlTable` WHERE `id` IN({$hFieldInfo['value']})";
		$sqlResult = mysqli_query($DB,$sqlSelectParent);
		if($sqlResult) {
			list($sOutput)=mysqli_fetch_row($sqlResult);
			mysqli_free_result($sqlResult);
		}
	}
	else {
		$sOutput='';
	}
	// end of your smart code
	
	$iOccurrence++;
	return $sOutput;
}
}

if(!function_exists('showCellDataParent')) {
function showCellDataParent($hArgs) {
	global $DB;
	static $iOccurrence=0;
	$sOutput='';

	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);

	$hDefault=array();
	extract(array_merge($hDefault,$hArgs));
	// gets the current language
	$lang = $GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['lang'];
	
	// place your smart code here
	if(!empty($hFieldInfo['value'])) {
		// if table contains language specific marker
		if(substr($table,-3)=="_$lang")
			$sqlSelectParent	 = "SELECT `label` as label FROM `$table` WHERE `id`='{$hFieldInfo['value']}'";
		else
			$sqlSelectParent	 = "SELECT `label_$lang` as label FROM `$table` WHERE `id`='{$hFieldInfo['value']}'";
		$sqlResult = mysqli_query($DB,$sqlSelectParent);
		if($sqlResult) {
			list($sOutput)=mysqli_fetch_row($sqlResult);
			mysqli_free_result($sqlResult);
		}
	}
	else {
		$sOutput='';
	}
	// end of your smart code
	
	$iOccurrence++;
	return $sOutput;
}	
}

if(!function_exists('showCellDataEnumList')) {
function showCellDataEnumList($hArgs = array()) {
	global $DB,$hTranslation;
	static $iOccurrence=0;
	$sOutput='';

	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);
	$hDefault=array();
	extract(array_merge($hDefault,$hArgs));
	
	// place your smart code here
	if(!empty($hTranslation["$table.{$hFieldInfo['name']}.{$hFieldInfo['value']}.alias"])) 
		$sOutput = $hTranslation["$table.{$hFieldInfo['name']}.{$hFieldInfo['value']}.alias"];
	else
		$sOutput = $hFieldInfo['value'];
	// end of your smart code
	
	$iOccurrence++;
	return $sOutput;
}
}

if(!function_exists('showCellDataForeignKeyList')) {
function showCellDataForeignKeyList($hArgs = array()) {
	global $DB;
	static $iOccurrence=0;
	$sOutput='';

	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);

	$hDefault=array();
	extract(array_merge($hDefault,$hArgs));
	// gets the current language
	$lang = $GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['lang'];
	// place your smart code here
	if(!empty($hFieldInfo['value'])) {
		// gets the table name from the field's name
		$sqlTable	 = substr($hFieldInfo['name'],3,-5);
		// if table contains language specific marker
		if(substr($sqlTable,-3)=="_$lang")
			$sqlSelect	 = "SELECT `label` as label FROM `$sqlTable` WHERE `id` IN({$hFieldInfo['value']})";
		else
			$sqlSelect	 = "SELECT `label_$lang` as label FROM `$sqlTable` WHERE `id` IN({$hFieldInfo['value']})";
		$sqlResult	 = mysqli_query($DB,$sqlSelect);
		if($sqlResult) {
			$a = array();
			while($sqlResult && list($label) = mysqli_fetch_row($sqlResult)) {
				$a[] = $label;
			}
			mysqli_free_result($sqlResult);
			if(!empty($a)) {
				$sOutput = implode(',',$a);
			}
		}
	}
	// end of your smart code
	
	$iOccurrence++;
	return $sOutput;
}
}

if(!function_exists('showCellDataUploadedFileReference')) {
function showCellDataUploadedFileReference($hArgs = array()) {
	global $DB;
	static $iOccurrence=0;
	$sOutput='';

	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);

	$hDefault=array();
	extract(array_merge($hDefault,$hArgs));

	// place your smart code here
	if(!empty($hFieldInfo['value'])) {
		$sOutput = "<a href=\"../media/_upload/{$hFieldInfo['value']}\" target=\"_blank\">[viewUploadedFile]</a>";
	}
	// end of your smart code
	
	$iOccurrence++;
	return $sOutput;
}
}

// standard functions to display data in form mode
if(!function_exists('showFormItemPrimaryKey')) {
function showFormItemPrimaryKey($hArgs = array()) {
	static $iOccurrence=0;
	$sOutput='';

	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);

	$hDefault=array();
	extract(array_merge($hDefault,$hArgs));
	
	$tplMain=	getTpl('../templates/bckffc/bckffc-form-input-hidden.tpl');
	$hMain=	array();

	// place your smart code below
	$hMain				 = array();
	$hMain['name']		 = $hFieldInfo['name'];
	$hMain['value']		 = $hFieldInfo['value'];
	$sOutput.= inject(array('data'=>$hMain,'tpl'=>$tplMain));
	// end of your smart code
	
	$iOccurrence++;
	return $sOutput;
}
}

if(!function_exists('showFormItemForeignKey')) {
function showFormItemForeignKey($hArgs = array()) {
	static $iOccurrence=0;
	$sOutput='';

	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);
	global $DB;
	$hDefault=array();
	extract(array_merge($hDefault,$hArgs));

	$lang = $GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['lang'];
	
	$tplMain	 = getTpl('../templates/bckffc/bckffc-form-input-select.tpl');
	$tplSub		 = getTpl('../templates/bckffc/bckffc-form-input-select-option.tpl');
	$hMain		 = array();
	$hSub		 = array();

	// place your smart code below
	$hMain					 = array();
	// extracts the table name from the fieldname
	$sqlTable				 = substr($hFieldInfo['name'], 3);
	// fills the data necesssary for the main template
	$hMain['name']			 = $hFieldInfo['name'];
	$hMain['value']			 = $hFieldInfo['value'];
	$hMain['alias']			 = $hFieldInfo['alias'];
	$hMain['options']		 = '';
	// fills inject the "zero" value 
	$hSub['value']			 = '';
	$hSub['label']			 = '[NONE_SELECTED]';
	$hMain['options']		 = inject(array('tpl'=>$tplSub,'data'=>$hSub));
	
	
	if(substr($sqlTable,-3)=="_$lang")
		$sqlSelect	 = "SELECT `id`,`label` FROM `$sqlTable` WHERE `id` IN({$hFieldInfo['value']})";
	else
		$sqlSelect	 = "SELECT `id`,`label_$lang` as `label` FROM `$sqlTable` WHERE `id` IN({$hFieldInfo['value']})";
	
	// gets the list of available values (id/primary key from $sqlTable) and label for better understanding
	$sqlSelect = "SELECT id as value, label_$lang as label FROM $sqlTable ORDER BY label_$lang ASC;";
	$sqlResult = mysqli_query($DB, $sqlSelect);
	if($sqlResult) {
		while($hSub = mysqli_fetch_assoc($sqlResult)) {
			$hMain['options'].=inject(array('tpl'=>$tplSub,'data'=>$hSub));
		}
		mysqli_free_result($sqlResult);
	}
	$sOutput.= inject(array('data'=>$hMain,'tpl'=>$tplMain));
	// end of your smart code
	
	$iOccurrence++;
	return $sOutput;
}
}

if(!function_exists('showFormItemParent')) {
function selectChildren($hArgs = array()) {
	static $iOccurrence=0;
	static $sTplMain='';
	static $tplSub='';

	global $DB;
	$sOutput='';

	// recuperation et verification des parametres d'entree
	$hDefault=array(
		'where'=>1,
		'startingNodeId'=>0,
		'maxDepth'=>2,
		'depth'=>0,
		'lang'=>$GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['lang'],
	);
	extract(array_merge($hDefault,$hArgs));

	// recuperation des templates sans les interpréter
	// truc pour permettre la pre-injection de donnees HGPC dans les templates
	$sTplMain = inject(array('tpl'=>getTpl($tplParent),'data'=>dataHGPC()));
	$tplSub = inject(array('tpl'=>getTpl($tplChild),'data'=>dataHGPC()));
	$hDataMain['children']='';

	// recuperation des donnees dont le parent est $parentId
	$sqlSelect = "SELECT * FROM `$table` WHERE $where AND `id_parent`='$startingNodeId' ORDER BY `id` ASC";
	$sqlResult = mysqli_query($DB,$sqlSelect);
	while($sqlResult and $hDataRow = mysqli_fetch_assoc($sqlResult)) {
		if(!empty($hDataRow["label_$lang"])) {
			$hDataRow['label'] = $hDataRow["label_$lang"];
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
		$value['children'] = selectChildren(array('table'=>$table,'where'=>$where,'startingNodeId'=>$id,'tplParent'=>$tplParent,'tplChild'=>$tplChild,'maxDepth'=>$maxDepth,'depth'=>$depth+1));
		$hDataMain['children'].=inject(array('tpl'=>$tplSub,'data'=>$value));
	}
	$iOccurrence++;
	return inject(array('tpl'=>$sTplMain,'data'=>$hDataMain));
}
function showFormItemParent($hArgs = array()) {
	static $iOccurrence=0;
	$sOutput='';

	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);

	$hDefault=array();
	extract(array_merge($hDefault,$hArgs));
	
	$tplMain=	getTpl('../templates/bckffc/bckffc-form-parent-trunk.tpl');
	$hDataMain=	array();
	// place your smart code below
	$hFieldInfo['options'] = selectChildren(array('table'=>$table, 'startingNodeId'=>0,'maxDepth'=>'4','tplParent'=>"../templates/bckffc/bckffc-form-parent-branch.tpl", 'tplChild'=>"../templates/bckffc/bckffc-form-parent-leaf.tpl"));

	$sOutput = hashIntoTpl($tplMain,$hFieldInfo);
	// end of your smart code
	
	$iOccurrence++;
	return $sOutput;
}
}

if(!function_exists('showFormItemEnumList')) {
function showFormItemEnumList($hArgs = array()) {
	global $hTranslation;
	static $iOccurrence=0;
	$sOutput='';

	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);
	$hDefault=array();
	extract(array_merge($hDefault,$hArgs));

	$lang = $GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['lang'];
	
	$tplMain	 = getTpl('../templates/bckffc/bckffc-form-input-select.tpl');
	$tplSub		 = getTpl('../templates/bckffc/bckffc-form-input-select-option.tpl');
	$hMain		 = array();
	$hSub		 = array();

	// place your smart code below
	$hMain['name']		 = $hFieldInfo['name'];
	$hMain['value']		 = $hFieldInfo['value'];
	$hMain['alias']		 = $hFieldInfo['alias'];
	$hMain['options']	 = '';
	
	// gets the list of possible values
	$hFieldInfo['values']	 = substr($hFieldInfo['values'], 1, -1);
	$aOptionValues			 = explode("','", $hFieldInfo['values']);

	foreach($aOptionValues as $value) {
		$hSub['value']		 = $value;
		if(!empty($value)) {
			if(!empty($hTranslation["{$table}.{$hFieldInfo['name']}.{$value}.alias"]))
				$hSub['label'] = $hTranslation["{$table}.{$hFieldInfo['name']}.{$value}.alias"];
			else
				$hSub['label'] = $value;
		}
		else {
			$hSub['label'] = "[NONE_SELECTED]";
		}
		$hMain['options']	.= inject(array('data'=>$hSub,'tpl'=>$tplSub));
	}
	$sOutput.= inject(array('data'=>$hMain,'tpl'=>$tplMain));
	// end of your smart code
	
	$iOccurrence++;
	return $sOutput;
}
}

if(!function_exists('showFormItemForeignKeyList')) {
function showFormItemForeignKeyList($hArgs = array()) {
	static $iOccurrence=0;
	$sOutput='';

	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);
	global $DB;
	$hDefault=array();
	extract(array_merge($hDefault,$hArgs));

	$lang = $GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['lang'];
	
	$tplMain	 = getTpl('../templates/bckffc/bckffc-form-input-checklist.tpl');
	$tplSub		 = getTpl('../templates/bckffc/bckffc-form-input-checklist-item.tpl');
	$hMain		 = array();
	$hSub		 = array();

	// place your smart code below
	$hMain					 = array();
	// extracts the table name from the fieldname
	$sqlTable				 = substr($hFieldInfo['name'], 3, -5);
	// fills the data necesssary for the main template
	$hMain['name']			 = $hFieldInfo['name'];
	$hMain['value']			 = $hFieldInfo['value'];
	$hMain['alias']			 = $hFieldInfo['alias'];
	$hMain['options']		 = '';
	
	// gets the list of available values (id/primary key from $sqlTable) and label for better understanding
	$sqlSelect = "SELECT id as value, label_$lang as label FROM $sqlTable ORDER BY label_$lang ASC;";
	$sqlResult = mysqli_query($DB, $sqlSelect);
	if($sqlResult) {
		while($hSub = mysqli_fetch_assoc($sqlResult)) {
			$hSub['name']		 = $hMain['name'];
			$hMain['options']	.= inject(array('tpl'=>$tplSub,'data'=>$hSub));
		}
		mysqli_free_result($sqlResult);
	}
	$sOutput.= inject(array('data'=>$hMain,'tpl'=>$tplMain));
	// end of your smart code
	$iOccurrence++;
	return $sOutput;
}
}

if(!function_exists('showFormItemUploadedFileReference')) {
function showFormItemUploadedFileReference($hArgs = array()) {
	static $iOccurrence=0;
	$sOutput='';

	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);
	global $DB;
	$hDefault=array();
	extract(array_merge($hDefault,$hArgs));
	// printArray($hArgs);
	$lang = $GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['lang'];
	
	$tplMain	 = getTpl('../templates/bckffc/bckffc-form-input-file.tpl');
	$hMain		 = array();

	// place your smart code below
	$hMain					 = array();
	// fills the data necesssary for the main template
	$hMain['name']			 = $hFieldInfo['name'];
	$hMain['file']			 = '#';
	if(!empty($hFieldInfo['value'])) {
		$hMain['file']			 = "../media/_upload/{$hFieldInfo['value']}";
		$hMain['value']			 = "{$hFieldInfo['value']}";
	}
	$hMain['alias']			 = $hFieldInfo['alias'];
	
	$sOutput.= inject(array('data'=>$hMain,'tpl'=>$tplMain));
	// end of your smart code
	$iOccurrence++;
	return $sOutput;
}
}

if(!function_exists('showFormItemPassword')) {
function showFormItemPassword($hArgs = array()) {
	static $iOccurrence=0;
	$sOutput = getTpl('../templates/bckffc/bckffc-form-input-password.tpl');
	// die(print_r($hArgs,true));
	// use debug mode to see which data are passed to the function comment
	$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="TRACE : arguments ".print_r($hArgs,true);

	$hDefault=array();
	extract(array_merge($hDefault,$hArgs));
	// place your smart code here
	// if(empty($hFieldInfo['value'])) $hFieldInfo['value'] = genRandomPwd();
	$hFieldInfo['value'] = '';
	$sOutput = hashIntoTpl($sOutput,$hFieldInfo);
	
	// end of your smart code
	$iOccurrence++;
	return $sOutput;
}
}

?>