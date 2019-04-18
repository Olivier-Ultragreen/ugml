<?php
/* this function will resize the image regardless to the original's image ratio */
function stretchAndSave($hArgs) {
	static $iOccurrence=0;
	
	$hDefault=array(
		'file'=>'../media/_images/calage.gif', 
		'width'=>20,
		'height'=>20,
		'acceptedTypes'=>array('.gif','.png','.jpg','.jpe','.jpeg'),
		'jpegTypes'=>array('.jpg','.jpe','.jpeg'),
		'folder'=>$GLOBALS['UGML']['CONF']['UPLOAD_PATH']);
	extract(array_merge($hDefault,$hArgs));
	
	$fileType=strtolower(strrchr($file,'.'));
	
	if(!in_array($fileType,$acceptedTypes)) {
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : non supported file type ".print_r($hArgs,true);
		return false;
	}
	
	list($iSrcWidth, $iSrcHeight) = getimagesize($file);
	
	$fileNew	= realpath($folder).'/'.str_replace($fileType,'',basename($file))."_{$width}x{$height}{$fileType}";
	$imgDest 	= imagecreatetruecolor($width, $height);
	
	switch($fileType) {
		case '.gif': {
			$imgSrc 	= imagecreatefromgif(realpath($file));
			imagecopyresampled($imgDest, $imgSrc, 0, 0, 0, 0, $width, $height, $iSrcWidth, $iSrcHeight);
			imagegif($imgDest,$fileNew,100);
			break;
		}
		case '.png': {
			$imgSrc 	= imagecreatefrompng(realpath($file));
			imagecopyresampled($imgDest, $imgSrc, 0, 0, 0, 0, $width, $height, $iSrcWidth, $iSrcHeight);
			imagepng($imgDest,$fileNew,100);
			break;
		}
		default: {
			$imgSrc 	= imagecreatefromjpeg(realpath($file));
			imagecopyresampled($imgDest, $imgSrc, 0, 0, 0, 0, $width, $height, $iSrcWidth, $iSrcHeight);
			imagejpeg($imgDest,$fileNew,100);
			break;
		}
	}
	$iOccurrence++;
}
/* this function will resize the image regardless to the original's image ratio */
function stretchAndOutput($hArgs) {
	static $iOccurrence=0;
	
	$hDefault=array(
		'file'=>'../media/_images/blank.png', 
		'width'=>20,
		'height'=>20,
		'acceptedTypes'=>array('.gif','.png','.jpg','.jpe','.jpeg'),
		'jpegTypes'=>array('.jpg','.jpe','.jpeg')
		);
	extract(array_merge($hDefault,$hArgs));
	
	$fileType=strtolower(strrchr($file,'.'));
	
	if(!in_array($fileType,$acceptedTypes)) {
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : non supported file type ".print_r($hArgs,true);
		return false;
	}
	
	list($iSrcWidth, $iSrcHeight) = getimagesize($file);
	
	$fileNew	= NULL;
	$imgDest 	= imagecreatetruecolor($width, $height);
	
	switch($fileType) {
		case '.gif': {
			header('Content-type: image/gif');
			$imgSrc 	= imagecreatefromgif(realpath($file));
			imagecopyresampled($imgDest, $imgSrc, 0, 0, 0, 0, $width, $height, $iSrcWidth, $iSrcHeight);
			imagegif($imgDest,$fileNew,100);
			break;
		}
		case '.png': {
			header('Content-type: image/png');
			$imgSrc 	= imagecreatefrompng(realpath($file));
			imagecopyresampled($imgDest, $imgSrc, 0, 0, 0, 0, $width, $height, $iSrcWidth, $iSrcHeight);
			imagepng($imgDest,$fileNew,100);
			break;
		}
		default: {
			header('Content-type: image/jpeg');
			$imgSrc 	= imagecreatefromjpeg(realpath($file));
			imagecopyresampled($imgDest, $imgSrc, 0, 0, 0, 0, $width, $height, $iSrcWidth, $iSrcHeight);
			imagejpeg($imgDest,$fileNew,100);
			break;
		}
	}
	$iOccurrence++;
}

/* this function will only keep the central part of the image, hence part of the image will be lost */
function cropStrechAndSave($hArgs) {
	static $iOccurrence=0;
	
	$hDefault=array(
		'file'=>'../media/_images/calage.gif', 
		'width'=>20,
		'height'=>20,
		'acceptedTypes'=>array('.gif','.png','.jpg','.jpe','.jpeg'),
		'jpegTypes'=>array('.jpg','.jpe','.jpeg'),
		'folder'=>$GLOBALS['UGML']['CONF']['UPLOAD_PATH']);
	extract(array_merge($hDefault,$hArgs));
	
	$fileType=strtolower(strrchr($file,'.'));
	
	if(!in_array($fileType,$acceptedTypes)) {
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : non supported file type ".print_r($hArgs,true);
		return false;
	}
	
	list($iSrcWidth, $iSrcHeight) = getimagesize($file);
	
	$fSrcRatio 	= $iSrcWidth/$iSrcHeight;
	$fDestRatio	= $width/$height;
	
	if($fSrcRatio>$fDestRatio) {
		// keep the source image's height and calculate new width according to destination image's ratio
		$iWidthToCropTo = floor($iSrcHeight * $fDestRatio);
		$iHeightToCropTo = $iSrcHeight;
		// select the center part of the image to crop
		$iSrcX = floor(($iSrcWidth-$iWidthToCropTo)/2);
		$iSrcY = 0;
	}
	elseif($fSrcRatio<$fDestRatio) {
		// keep the source image's with and calculate new height according to destination image's ratio
		$iWidthToCropTo = $iSrcWidth;
		$iHeightToCropTo = floor($iSrcWidth / $fDestRatio);
		// select the center part of the image to crop
		$iSrcX = 0;
		$iSrcY = floor(($iSrcHeight-$iHeightToCropTo)/2);
	}
	else {
		$iWidthToCropTo = $iSrcWidth;
		$iHeightToCropTo = $iSrcHeight;
		// select the center part of the image to crop
		$iSrcX = 0;
		$iSrcY = 0;
	}

	$fileNew	= realpath($folder).'/'.str_replace($fileType,'',basename($file))."_{$width}x{$height}{$fileType}";
	$imgDest 	= imagecreatetruecolor($width, $height);
	
	switch($fileType) {
		case '.gif': {
			$imgSrc 	= imagecreatefromgif(realpath($file));
			imagecopyresampled($imgDest, $imgSrc, 0, 0, $iSrcX, $iSrcY, $width, $height, $iWidthToCropTo, $iHeightToCropTo);
			imagegif($imgDest,$fileNew,100);
			break;
		}
		case '.png': {
			$imgSrc 	= imagecreatefrompng(realpath($file));
			imagecopyresampled($imgDest, $imgSrc, 0, 0, $iSrcX, $iSrcY, $width, $height, $iWidthToCropTo, $iHeightToCropTo);
			imagepng($imgDest,$fileNew,100);
			break;
		}
		default: {
			$imgSrc 	= imagecreatefromjpeg(realpath($file));
			imagecopyresampled($imgDest, $imgSrc, 0, 0, $iSrcX, $iSrcY, $width, $height, $iWidthToCropTo, $iHeightToCropTo);
			imagejpeg($imgDest,$fileNew,100);
			// die("imagecopyresampled($imgDest, $imgSrc, 0, 0, $iSrcX, $iSrcY, $width, $height, $iWidthToCropTo, $iHeightToCropTo);<br/>");
			break;
		}
	}
	$iOccurrence++;
}
/* this function will keep the original ratio and the original image in its entirety the potential remaining space will be filled with $background color */
function fitInAndSave($hArgs) {
	static $iOccurrence=0;
	
	$hDefault=array(
		'file'=>'../media/_images/calage.gif', 
		'width'=>20,
		'height'=>20,
		'background'=> '255,255,255',
		'acceptedTypes'=>array('.gif','.png','.jpg','.jpe','.jpeg'),
		'jpegTypes'=>array('.jpg','.jpe','.jpeg'),
		'folder'=>$GLOBALS['UGML']['CONF']['UPLOAD_PATH']);
	extract(array_merge($hDefault,$hArgs));
	
	$fileType=strtolower(strrchr($file,'.'));
	
	if(!in_array($fileType,$acceptedTypes)) {
		$GLOBALS['UGML']['DEBUG'][__FUNCTION__][$iOccurrence]="ERROR : non supported file type ".print_r($hArgs,true);
		return false;
	}
	
	list($iSrcWidth, $iSrcHeight) = getimagesize($file);
	
	$fSrcRatio 	= $iSrcWidth/$iSrcHeight;
	$fDestRatio	= $width/$height;
	
	if($fSrcRatio>$fDestRatio) {
		$iWidthToFitIn = $width;
		$iHeightToFitIn = $iWidthToFitIn / $fSrcRatio;
		$iDestX = 0;
		$iDestY = floor(($height-$iHeightToFitIn)/2);
	}
	elseif($fSrcRatio<$fDestRatio) {
		$iHeightToFitIn = $height;
		$iWidthToFitIn = $iHeightToFitIn * $fSrcRatio;
		$iDestX = floor(($width-$iWidthToFitIn)/2);
		$iDestY = 0;
	}
	else {
		$iWidthToFitIn = $width;
		$iHeightToFitIn = $height;
		// select the center part of the image to crop
		$iDestX = 0;
		$iDestY = 0;
	}

	$fileNew	= realpath($folder).'/'.str_replace($fileType,'',basename($file))."_{$width}x{$height}{$fileType}";
	$imgDest 	= imagecreatetruecolor($width, $height);
	
	// fill in the image with colored background
	list($r,$v,$b) = explode(',',$background);
	$bgColor	= imagecolorallocate($imgDest,$r,$v,$b);
	imagefill($imgDest,0,0,$bgColor);
	
	switch($fileType) {
		case '.gif': {
			$imgSrc 	= imagecreatefromgif(realpath($file));
			imagecopyresampled($imgDest, $imgSrc, $iDestX, $iDestY, 0, 0, $iWidthToFitIn, $iHeightToFitIn, $iSrcWidth, $iSrcHeight);
			imagegif($imgDest,$fileNew,100);
			break;
		}
		case '.png': {
			$imgSrc 	= imagecreatefrompng(realpath($file));
			imagecopyresampled($imgDest, $imgSrc, $iDestX, $iDestY, 0, 0, $iWidthToFitIn, $iHeightToFitIn, $iSrcWidth, $iSrcHeight);
			imagepng($imgDest,$fileNew,100);
			break;
		}
		default: {
			$imgSrc 	= imagecreatefromjpeg(realpath($file));
			imagecopyresampled($imgDest, $imgSrc, $iDestX, $iDestY, 0, 0, $iWidthToFitIn, $iHeightToFitIn, $iSrcWidth, $iSrcHeight);
			imagejpeg($imgDest,$fileNew,100);
			break;
		}
	}
	$iOccurrence++;
}
?>