<?php
// SOME ERROR MESSAGES
define('ERR_FILE_OPEN', '<br/>Impossible d\'ouvrir le fichier : %s');
define('ERR_PERMISSION_DENIED', 'Vous n\'avez sans doute pas les droits nécessaires pour faire ça !');
define('ERR_ARTICLE_NON_VALIDE','Cet article n\'est pas valide : %s');
define('ERR_FILE_TOO_BIG','Ce fichier est trop volumineux pour être uploader !');
define('ERR_PB_TRANSFERT',"Un problème s'est produit lors du transfert du fichier vers le serveur !<br/>OPERATION ANNULÉE<br/>");
define('ERR_PARAMETER_COUNT', "Le nombre d'argument est insuffisant function : ");
define('ERR_INVALID_PARAMETER', "La valeur de l'argument est %s est incorrecte pour la fonction : %s");
define('ERR_NO_UGD', "Pas de fichier ugd pour ce fichier : %s");

// SOME CONFIGURATION VALUES
define('UGML_EXTENSION', '.ugml|.tpl');
define('MAX_FILE_SIZE', 64000); // MAXIMUM SIZE ALLOWED FOR UGML FILES
define('OPENING_HEADER_DELIMITER', "[HEADER ");
define('CLOSING_HEADER_DELIMITER', "/]");
define('HEADER_PARAM_SPLITER','|'); // CHAR USED TO SEPARATE PARAMETRE IN THE HEADER
define('TAG_SPLITER','|'); // CHAR USED TO SEPARATED DATA IN THE UGD FILE
define('FUNCTION_PARAM_SPLITER','|'); // CHAR USED TO SEPARATED ARGS SENDED TO THE EXTENSION FUNCTION

define('OPENING_EXT_STRUCTURE','[%s '); // STRUCTURE USED TO CLOSE THE DESCRIPTION OF AN EXTENSTION
define('CLOSING_EXT_STRUCTURE',"/]"); // STRUCTURE USED TO CLOSE THE DESCRIPTION OF AN EXTENSTION
define('EXT_PATTERN','< \/>');


define('OPENING_TAG_STRUCTURE',"<%s>"); // STRUCTURE USED TO DESCRIBE AN OPENNING TAG
define('CLOSING_TAG_STRUCTURE',"</%s>"); // STRUCTURE USED TO DESCRIBE AN CLOSING TAG

define('DEFVAR_STRUCTURE', "DEFVAR=(.*)=>(.*)");
define('DEFEXT_STRUCTURE', "DEFEXT=(.*)=>(.*)");
define('DEFTAG_STRUCTURE', "DEFTAG=(.*)=>(.*)");

define('CALL_VAR_STRUCTURE', "\[(.*)\]");

define('PROJECT_DIRECTORY',dirname(dirname($_SERVER['PATH_TRANSLATED'])));
define('BCKFFC_DIR','bckffc');

define('DEBUG',false);
?>