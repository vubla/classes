<?php
//exec('pwd',$bug); var_dump($bug);

function checkConfig(){};
require_once '../../../autoload.php'; 

define('CLASS_FOLDER','../../../');
$path = '../../../../api/search/index.php';
if(sizeof($argv) > 1){
  $_GET['q'] = $argv[1]; 
}
if(sizeof($argv) > 2){
  $_GET['host'] =$argv[2]; 
}

include ($path);