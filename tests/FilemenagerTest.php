<?php
use Php\Filemanager\Filemanager;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FilemenagerTest extends PHPUnit_Framework_TestCase
{
	private $urlbase = 'http://192.168.33.117';

	public function testValidPath(){
		$f = new Filemanager;
		
		$this->assertTrue($f->validPath('/'));
		$this->assertTrue($f->validPath('/demo/'));
		$this->assertTrue($f->validPath('/demo/otro/'));
		$this->assertTrue($f->validPath('/test.txt'));
		$this->assertTrue($f->validPath('/demo/file.doc'));
		$this->assertEquals($f->validPath(''),false);		
		$this->assertEquals($f->validPath("\\"),false);
		$this->assertEquals($f->validPath("..\\..\\"),false);
		$this->assertEquals($f->validPath("../"),false);
		$this->assertEquals($f->validPath("../../"),false);
		$this->assertEquals($f->validPath("./"),false);
		$this->assertEquals($f->validPath("/demo/otro"),false);

	}
	public function testFileInfo(){
		$filesystem = new Filesystem();
		$filesystem->remove('tests/userfiles');
		$filesystem->mkdir("tests/userfiles");
		
		// Sin archivos
		$extra = array("separator" => "tests/userfiles");
		$f = new Filemanager($extra);
		$path = '/demo/';
		$fullpath = $f->getFullPath().$path;
		$file = new \SplFileInfo($fullpath);
		$r = $f->fileInfo($file,$path);
		$this->assertEquals($r,NULL);

		// Cuando es un directorio
		$filesystem->mkdir("tests/userfiles/demo");
		$extra = array("separator" => "tests/userfiles");
		$f = new Filemanager($extra);
		$path = '/demo/';
		$fullpath = $f->getFullPath().$path;
		$file = new \SplFileInfo($fullpath);
		$r = $f->fileInfo($file,$path);
		$this->assertEquals($r['path'],'/tests/userfiles/demo/');
		$this->assertEquals($r['filename'],'demo');
		$this->assertEquals($r['filetype'],'');

		// // Cuando es un archivo
		// $filesystem->dumpFile("tests/userfiles/test.txt",'Hola Mundo');
		// $extra = array("separator" => "tests/userfiles");
		// $f = new Filemanager($extra);
		// $path = '/test.txt';
		// $fullpath = $f->getFullPath().$path;
		// $file = new \SplFileInfo($fullpath);
		// $r = $f->fileInfo($file,$path);
		// $this->assertEquals($r['path'],'/tests/userfiles/');
		// $this->assertEquals($r['filename'],'test.txt');
		// $this->assertEquals($r['filetype'],'txt');

		// // Cuando es un archivo no leible
		// $filesystem->dumpFile("tests/userfiles/file.txt",'Hola Mundo');
		// $filesystem->chown("tests/userfiles/file.txt",'test');
		// $filesystem->chgrp("tests/userfiles/file.txt",'test');
		// $extra = array("separator" => "tests/userfiles");
		// $f = new Filemanager($extra);
		// $path = '/file.txt';
		// $fullpath = $f->getFullPath().$path;
		// $file = new \SplFileInfo($fullpath);
		// $r = $f->fileInfo($file,$path);
		// $this->assertEquals($r,null);
		
	}
	public function testGetAllFiles(){
		$filesystem = new Filesystem();
		$filesystem->remove('tests/userfiles');

		// // Carpeta vacia
		// $filesystem->mkdir("tests/userfiles");
		// $extra = array("separator" => "tests/userfiles");
		// $f = new Filemanager($extra);
		// $path = '/';
		// $r = $f->getAllFiles($path);
		// $this->assertEquals($r,array());

		// // Un archivo
		// $filesystem->dumpFile("tests/userfiles/test.txt",'Hola Mundo');
		// $extra = array("separator" => "tests/userfiles");
		// $f = new Filemanager($extra);
		// $path = '/';
		// $r = $f->getAllFiles($path);
		// $this->assertEquals($r[0]['path'],'/tests/userfiles/');
		// $this->assertEquals($r[0]['filename'],'test.txt');
		// $this->assertEquals($r[0]['filetype'],'txt');

		// // Una carpeta y un archivo archivo
		// $filesystem->mkdir("tests/userfiles/demo");		
		// $extra = array("separator" => "tests/userfiles");
		// $f = new Filemanager($extra);
		// $path = '/';
		// $r = $f->getAllFiles($path);
		// $this->assertEquals($r[0]['path'],'/tests/userfiles/');
		// $this->assertEquals($r[0]['filename'],'demo');
		// $this->assertEquals($r[0]['filetype'],'');
		// $this->assertEquals($r[1]['path'],'/tests/userfiles/');
		// $this->assertEquals($r[1]['filename'],'test.txt');
		// $this->assertEquals($r[1]['filetype'],'txt');

		// // No existe carpeta		
		// $extra = array("separator" => "tests/userfiles");
		// $f = new Filemanager($extra);
		// $path = '/test';
		// $r = $f->getAllFiles($path);
		// $this->assertEquals($r,NULL);

		// // Path un archivo					
		// $extra = array("separator" => "tests/userfiles");
		// $f = new Filemanager($extra);
		// $path = '/test.txt';
		// $r = $f->getAllFiles($path);
		// $this->assertEquals($r['path'],'/tests/userfiles/');
		// $this->assertEquals($r['filename'],'test.txt');
		// $this->assertEquals($r['filetype'],'txt');

		// // Todos
		// $extra = array("separator" => "tests/userfiles");
		// $f = new Filemanager($extra);
		// $path = '/';
		// $r = $f->getAllFiles($path);
		// $this->assertTrue(count($r)>0);

	}
	public function testUploadAll(){
		// $extra = array("separator" => "tests/userfiles");
		// $f = new Filemanager($extra);
		// $path = '/';
		// $files = array();
		// $p = __DIR__ .'/_data/Koala.jpg';
		// $files[] = new UploadedFile($p,'Koala.jpg','image/jpg',filesize($p));
		// $files[] = new UploadedFile($p,'Koala.jpg','image/jpg',filesize($p));
		// $r = $f->uploadAll($files,$path);
		// var_dump($r);
		// var_dump($files[0]);		

	}
	// public function testUpload(){
	// 	$extra = array("separator" => "tests/userfiles");
	// 	$f = new Filemanager($extra);
	// 	$path = '/';
	// 	$p = __DIR__ .'/_data/Koala.jpg';
	// 	$file = new UploadedFile($p,'Koala.jpg','image/jpg',filesize($p));
	// 	$r = $f->upload($file,$path);
	// 	var_dump($r);
	// }
	public function testRun(){
		// $filesystem = new Filesystem();
		// $filesystem->remove('tests/userfiles');

		// // Mostrar cuando esta vacio
		// $filesystem->mkdir("tests/userfiles");
		// $_POST['mode']='getfolder';
		// $_POST['path']='/';
		// $extra = array("separator" => "tests/userfiles");
		// $f = new Filemanager($extra);
		// $f->run();
		// $response = new JsonResponse(array("data"=>array(),"msg"=>""));
  //       $this->assertEquals($f->run(), $response->sendContent());

	}
	public function testClearNameFile(){
		$extra = array("separator" => "tests/userfiles");
		$f = new Filemanager($extra);
		$filename = 'Ñoño NúMEro';
		$r = $f->clearNameFile($filename);
		$this->assertEquals($r,"nono-numero");
		$filename = 'Ñoño  NúMEro';
		$r = $f->clearNameFile($filename);
		$this->assertEquals($r,"nono-numero");
	}
}
?>