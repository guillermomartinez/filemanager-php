<?php
$dir = 'userfiles/';
$filename = 'koala.jpg';
$name = 'koala';
for ($i=2; $i < 101 ; $i++) { 
	copy($dir.$filename,$dir.$name.'-'.$i.'.jpg');
}
?>