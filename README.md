# Filemanager
Es el Backend del Filemanger, [guillermomartinez/filemanager-ui](https://github.com/guillermomartinez/filemanager-ui) es el Frontend.

## Requiere
- PHP >= 5.4
- Fileinfo Extension
- GD Library

## instalación
```
composer require guillermomartinez/filemanager-php:dev-master
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
http://php-filemanager.rhcloud.com/filemanager-ui/

![demo1](https://cloud.githubusercontent.com/assets/5642429/7805759/be21ea9a-033d-11e5-9914-68ccad1299c5.png)
![demo2](https://cloud.githubusercontent.com/assets/5642429/7805789/4c9441b0-033e-11e5-883e-0a3a3e3fbe50.png)
![demo3](https://cloud.githubusercontent.com/assets/5642429/7805788/4c8fdc24-033e-11e5-84ef-e3cecc5736c4.png)
