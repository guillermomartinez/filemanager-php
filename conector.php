<?php
include("vendor/autoload.php");
use GuillermoMartinez\Filemanager\Filemanager;

// agrega tu propio metodo de autenticacion

$extra = array(
	"source" => "filemanager/userfiles",
	"url" => "../",
	"doc_root" => "D:/wamp/www/github",
	"debug" => true,
	);
$f = new Filemanager($extra);
$f->run();
?>
