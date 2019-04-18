<?php
function ls($path, $ext=''){
	// GETS THE LIST OF FILE FROM $path DIRECTORY WHICH EXTENSIONS ARE $ext
	// BASIC BUT USEFULL
	// NB USE ONLY IF ORDER FONCTION DOESN'T FIT
	$handle=opendir($path);
	while ($file = readdir($handle)){
		if(!empty($ext) && strrchr($file, '.') == ".$ext"){
			$list_file[]=$path.$file;
		}
		else {
			$list_file[]=$path.$file;
		}
	}
	closedir($handle);
	return $list_file;
}
function excerpt($string, $length) {
	$sOutput='';
	// removes potentially hazardous html tags from the string
	$sOutput=strip_tags($string);
	
	if(strlen($sOutput)>$length) {
		$sOutput=substr($sOutput,0,$length);
		
		// gets the position of the last ponctuation mark
		$iComaPosition=strrpos($sOutput, ', ');
		$iDotPosition=strrpos($sOutput, '.');
		$iSpacePosition=strrpos($sOutput, ' ');
		$iSemicolonPosition=strrpos($sOutput, '; ');
		
		$iLastWordEndPosition=max($iComaPosition, $iDotPosition, $iSpacePosition, $iSemicolonPosition);
		
		// cuts the string further in attempt to keep a meaningfull string ...
		$sOutput=substr($sOutput, 0, $iLastWordEndPosition).' [...]';
	}
	return $sOutput;

} 
function sendmail($hArgs = array()) {
	global $DB, $hTranslation;
	$sOutput = '';
	$hDefault=array(
		'from'		 => '',
		'from_name'	 => '',
		'to'		 => '',
		'cc'		 => '',
		'bcc'		 => '',
		'subject'	 => '',
		'message'	 => '',
		'attachment' => array(),
		'embededimages'=> array(),
		'debug'		 => SENDMAIL_DEBUG
	);
	extract(array_merge($hDefault,$hArgs));
	
	
	$oMail= new PHPMailer\PHPMailer\PHPMailer();
	$oMail->CharSet = 'UTF-8';
	// Commenter les lignes ci-dessous pour un envoi via la fonction mail() de PHP et en ignorant la configuration SMTP
	$oMail->IsSMTP();
	$oMail->SMTPSecure	 = 'tls';
	$oMail->SMTPAuth	 = true;
	if(SENDMAIL_DEBUG) $oMail->SMTPDebug	 = 2;
	$oMail->Host		 = SMTP_HOST;
	$oMail->Username	 = SMTP_USER;
	$oMail->Password	 = SMTP_PWD;
	// 
	
	$oMail->From		 = $from;
	$oMail->FromName	 = $from_name;

	$oMail->AddAddress(trim($to));
	if(!empty($cc) && trim($cc)!='') $oMail->AddAddress(trim($cc));
	if(!empty($bcc) && trim($bcc)!='') $oMail->AddBCC(trim($bcc));
	$oMail->Subject = $subject;
	
	$oMail->isHTML(true);
	$oMail->Body = $message;
	// Décommenter la ligne ci-dessous pour envoyer une version non HTML en sus de la version HTML du mail
	// $oMail->AltBody = strip_tags(stristr($message,'<body'));

	if(!empty($attachment)) {
		foreach($attachment as $file) {
			if(!is_array($file)) {
				$oMail->AddAttachment($file);
			}
			else {
				$oMail->AddAttachment($file['path'],$file['name']);
			}
		}
	}
	if(!empty($embededimages)) {
		foreach($embededimages as $cid => $file) {
			$oMail->AddEmbeddedImage($file, $cid);
		}
	}
	try {
		return $oMail->Send();
	} catch (Exception $e) {
		die($e->errorMessage());
	} catch (\Exception $e) {
		die($e->getMessage());
	}
}
?>