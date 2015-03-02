<?php
include("vendor/autoload.php");
use Php\Filemanager\Filemanager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Intervention\Image\ImageManager;

$manager = new ImageManager(array('driver' => 'gd'));
$path = '/var/www/html/PqbFilemanager/userfiles/6_1-226-137-274x150.jpg';
$image = $manager->make($path)->fit(100,100,function ($constraint) {$constraint->upsize();});
$image->save('test.jpg');
// $finder = new Finder();
// $files = $finder->files()->notName('_thumbs')->depth('0')->sortByType()->in('userfiles/1620');
// foreach ($files as $key => $file) {
// 	var_dump($file);
// 	break;
	
// }
?>