<?php
include("vendor/autoload.php");
use GuillermoMartinez\Filemanager\Filemanager;

$extra = array("debug"=>false,"separator" => "public/userfiles","upload"=>array("size_max" => 10));
// $extra = array();
$f = new Filemanager($extra);
$f->run();

?>