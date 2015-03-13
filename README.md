# Filemanager

## Requiere
- PHP >= 5.4
- Fileinfo Extension
- GD Library

## instalación
```
composer require guillermomartinez/filemanager:dev-master
```
crea un archivo conector.php en el directorio public_html

```
<?php
include("vendor/autoload.php");
use GuillermoMartinez\Filemanager\Filemanager;

$extra = array("separator" => "filemanager/userfiles"));
$f = new Filemanager($extra);
$f->run();
?>
```
separator: Son las carpetas que separan la carpeta public_html a la carpeta userfiles

Instale https://github.com/guillermomartinez/filemanager-ui para la interfaz de usuario
