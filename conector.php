<?php
include("vendor/autoload.php");
use Php\Filemanager\Filemanager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Finder\Finder;
//$extra = array("path"=>"userfiles/demo/");
$extra = array("separator" => "PqbFilemanager/userfiles");
// $extra = array();
$f = new Filemanager($extra);
$f->run();

?>