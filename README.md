# Filemanager for PHP
It is the connector to the File Manager, [guillermomartinez/filemanager-ui](https://github.com/guillermomartinez/filemanager-ui) It is the GUI.

## Required
- PHP >= 5.4

## Wiki
[Wiki](https://github.com/guillermomartinez/filemanager-ui/wiki)

## Installation
```
composer require guillermomartinez/filemanager-php:0.1.*
```
Create file conector.php in folder public_html

```
<?php
include("vendor/autoload.php");
use GuillermoMartinez\Filemanager\Filemanager;

// Add your own authentication method
//if(!isset($_SESSION['username']) || $_SESSION['username']!="")
//  exit();
$extra = array(
	// path after of root folder
	// if /var/www/public_html is your document root web server
	// then source= usefiles o filemanager/usefiles
	"source" => "userfiles",
	// url domain
	// so that the files and show well http://php-filemanager.rhcloud.com/userfiles/imagen.jpg
	// o http://php-filemanager.rhcloud.com/filemanager/userfiles/imagen.jpg
	"url" => "http://php-filemanager.rhcloud.com/",
	"debug" => false,
	"images" => [
        	'resize'=>[
			// width,height,IF TRUE crop in width ELSEIF NULL is auto,IF TRUE crop in height ELSEIF NULL is auto
            		'medium' => array(340,180,true,true),
			'large' => array(1024,768,true,true),
        	]
    	],
	);
if(isset($_POST['typeFile']) && $_POST['typeFile']=='images'){
    $extra['type_file'] = 'images';
}
$f = new Filemanager($extra);
$f->run();
?>
```

Install https://github.com/guillermomartinez/filemanager-ui for user interface.

## Demo
http://php-filemanager.rhcloud.com/

![demo2](https://cloud.githubusercontent.com/assets/5642429/8630887/aec46114-2731-11e5-9a7b-907127d77891.jpg)
![demo1](https://cloud.githubusercontent.com/assets/5642429/8630885/ae7e7122-2731-11e5-88bb-b8fd2f5ae9a5.jpg)
![demo3](https://cloud.githubusercontent.com/assets/5642429/8630886/aeaa1b7e-2731-11e5-9097-cafeefba1aea.jpg)
