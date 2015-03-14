<?php
include("vendor/autoload.php");
use GuillermoMartinez\Filemanager\Filemanager;

// agrega tu propio metodo de autenticacion

$extra = array("separator" => "filemanager/userfiles");
$f = new Filemanager($extra);
$f->run();
?>
