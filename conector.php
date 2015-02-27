<?php
include("vendor/autoload.php");
use Php\Filemanager\Filemanager;

//$extra = array("path"=>"userfiles/demo/");
$extra = array("debug"=>true,"separator" => "PqbFilemanager/userfiles","upload"=>array("size_max" => 2));
// $extra = array();
$f = new Filemanager($extra);
$f->run();
// var_dump($_FILES);
// var_dump($_GET);
// var_dump($_POST);

?>