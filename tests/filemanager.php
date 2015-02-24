<?php
include("../vendor/autoload.php");
use Php\Filemanager\Filemanager;

// $extra = array("doc_root"=> "/home/demo", "separator" => "userfiles");
$extra = array("separator" => "PqbFilemanager/tests/userfiles");
$f = new Filemanager($extra);
$r = $f->run();
?>