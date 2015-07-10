# Filemanager
Es el conector del Filemanger, [guillermomartinez/filemanager-ui](https://github.com/guillermomartinez/filemanager-ui) es la interfaz gráfica.

## Requiere
- PHP >= 5.4
- Fileinfo Extension
- GD Library

## instalación
```
composer require guillermomartinez/filemanager-php:0.1.*
```
crea un archivo conector.php en el directorio public_html

```
<?php
include("vendor/autoload.php");
use GuillermoMartinez\Filemanager\Filemanager;

$extra = array("source" => "filemanager/userfiles");
$f = new Filemanager($extra);
$f->run();
?>
```
separator: Son las carpetas que separan la carpeta public_html a la carpeta userfiles

Instale https://github.com/guillermomartinez/filemanager-ui para la interfaz de usuario

## Demo
http://php-filemanager.rhcloud.com/

![demo2](https://cloud.githubusercontent.com/assets/5642429/8630887/aec46114-2731-11e5-9a7b-907127d77891.jpg)
![demo1](https://cloud.githubusercontent.com/assets/5642429/8630885/ae7e7122-2731-11e5-88bb-b8fd2f5ae9a5.jpg)
![demo3](https://cloud.githubusercontent.com/assets/5642429/8630886/aeaa1b7e-2731-11e5-9097-cafeefba1aea.jpg)
