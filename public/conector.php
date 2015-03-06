<?php
include("../vendor/autoload.php");
use GuillermoMartinez\Filemanager\Filemanager;

//$extra = array("path"=>"userfiles/demo/");
// $extra = array("debug"=>true,"separator" => "PqbFilemanager/userfiles","upload"=>array("size_max" => 2));
$extra = array("debug"=>false,"separator" => "PqbFilemanager/public/userfiles","upload"=>array("size_max" => 2));
// $extra = array("doc_root"=>"/home/demo","debug"=>true,"separator" => "/userfiles","upload"=>array("size_max" => 2));
// $extra = array();
$f = new Filemanager($extra);
$f->run();

?>