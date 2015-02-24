<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Initial');
$I->cleanDir('tests/userfiles');

$I->sendAjaxPostRequest('/PqbFilemanager/tests/filemanager.php', array('mode' => "getfolder","path"=>"/")); 
$I->see('{"data":[],"msg":""}');

copy("tests/_data/Koala.jpg","tests/userfiles/Koala.jpg");
$I->sendAjaxPostRequest('/PqbFilemanager/tests/filemanager.php', array('mode' => "getfolder","path"=>"/")); 
$I->see('Koala.jpg');



