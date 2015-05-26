<?php
include("vendor/autoload.php");
use GuillermoMartinez\Filemanager\Filemanager;

// Add your own authentication method

$extra = array(
	"source" => "github/filemanager/userfiles",
	"debug" => false,
	);
$f = new Filemanager($extra);
$f->run();
?>