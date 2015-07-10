<?php
include("vendor/autoload.php");
use GuillermoMartinez\Filemanager\Filemanager;

// Add your own authentication method

$extra = array(
	"source" => "github/filemanager/userfiles",
	"url" => "http://localhost/",
	"debug" => false,
	);
if(isset($_POST['typeFile']) && $_POST['typeFile']=='images'){
	$extra['type_file'] = 'images';
}
$f = new Filemanager($extra);
$f->run();
?>