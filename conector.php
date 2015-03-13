<?php
include("vendor/autoload.php");
use GuillermoMartinez\Filemanager\Filemanager;

$extra = array("separator" => "filemanager/userfiles"));
$f = new Filemanager($extra);
$f->run();
?>