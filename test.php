<?php
include("vendor/autoload.php");
use Php\Filemanager\Filemanager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

$finder = new Finder();
$files = $finder->files()->notName('_thumbs')->depth('0')->sortByType()->in('userfiles/1620');
foreach ($files as $key => $file) {
	var_dump($file);
	break;
	
}
?>