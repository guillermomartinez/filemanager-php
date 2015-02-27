<?php
include("vendor/autoload.php");
use Php\Filemanager\Filemanager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Finder\Finder;
$extra = array("doc_root"=> "/home/demo", "separator" => "userfiles");
$extra = array("separator" => "PqbFilemanager/userfiles");
$f = new Filemanager($extra);
// $f->mode ='getFolder';
$r = $f->run();

// $r2 = new JsonResponse(array());
// // $r2->setData(array());
// $r2->sendContent();
// var_dump($r2);
// $finder = new Finder();
// var_dump($finder->files()->in(__DIR__));

// foreach ($finder as $file) {
//     // Print the absolute path
//     print $file->getRealpath()."\n";

//     // Print the relative path to the file, omitting the filename
//     print $file->getRelativePath()."\n";

//     // Print the relative path to the file
//     print $file->getRelativePathname()."\n";
// }
// $stream = file_get_contents("scripts/filemanager.config.js");
// $data = json_decode($stream, true);
// var_dump($data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
	<form action="index.php?mode=getfolder&path=/" method="post" enctype="multipart/form-data" >
		<input type="hidden" name="accion" value="uploadfile">
		<input type="file" name="archivo[]" multiple>
		<input type="submit" value="Enviar">
	</form>
</body>
</html>