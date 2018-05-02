<?php
function listeRubriques($hArgs) {

	// recuperation et verification des parametres d'entree
	$hDefault=array(
		'tpl'=>'../templates/_fileRubriqueLi.tpl',
		'requestedType'=>$GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['type'],
		'path'=>'./',
		'on'=>'selected',
		'off'=>'',
		'postTreatmentFunction'=>''
		);
	extract(array_merge($hDefault,$hArgs));
	
	// initialisation de varaibles utiles
	$sOutput='';
	$liste=array();

	// initialisation des valeurs par défaut
	if(empty($requestedType)) $requestedType=$GLOBALS['UGML']['REQUESTED_UGML_FILE']['HEADER']['type'];
	if(empty($path)) $path=$GLOBALS['UGML_CONF_VAR']['WEB_ROOT'];
	if(empty($postTreatmentFunction)) $postTreatmentFunction='';

	$tpl=parseUgml(array('file'=>$tpl));

	// récupératoin de la liste des rubriques
	$liste=selectUgmlFiles('nav'.FUNCTION_PARAM_SPLITER.'title'.FUNCTION_PARAM_SPLITER.'group'.FUNCTION_PARAM_SPLITER.'on'.FUNCTION_PARAM_SPLITER.'off', $path, 'position', 'ASC', 'type='.$requestedType);
	
	// si aucun fichier n'a ete trouve sortie express
	if(!isset($liste['sOrderBy']) or !is_array($liste['sOrderBy'])) return '';

	// construction des liens du menu
	foreach($liste['sOrderBy'] as $k=>$v) {
		$hData=array();
		if($liste['nav'][$k]=='ok' and (empty($liste['group'][$k]) or (!empty($_SESSION['bckffc-group_id']) and $liste['group'][$k]==$_SESSION['bckffc-group_id']))) {
			$hData['link']=basename($liste['filename'][$k]);
			$hData['title']=$liste['title'][$k];
			$hData['classCss']='';
			if(basename($GLOBALS['UGML']['REQUESTED_UGML_FILE']['FILENAME'])==basename($liste['filename'][$k])) {
				if(!empty($on)) $hData['classCss']=' class="'.$on.'"';
			}
			else {
				if(!empty($off)) $hData['classCss']=' class="'.$off.'"';
			}
			$sOutput.=hashIntoTpl($tpl,$hData);
		}
	}
	// gestion de la double extension ugml ou php
	$ext=strrchr($_SERVER['PATH_TRANSLATED'],'.');
	$exts=explode('|',UGML_EXTENSION);
	if(!in_array($ext,$exts)) $sOutput=str_replace($exts, array($ext,$ext), $sOutput);

	// retraitement du flux par la fonction $postTreatmentFunction
	if(!empty($postTreatmentFunction)) $sOutput=call_user_func($postTreatmentFunction, $sOutput);
	return $sOutput;
}
?>