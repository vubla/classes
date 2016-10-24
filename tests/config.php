<?php 

define ('HOST', 'localhost');
define ('DB_USER', 'phpunit');
define ('DB_PASS', 'Trekant01');

if(!defined('CLASS_FOLDER')) define('CLASS_FOLDER', '../..');
define('UNITTEST_MODE', true);
define ('API_URL', 'http://api.vubla.com');
define ('VUBLA_CACHE', 'phpunit_cache');

if(!function_exists('checkConfig')) {
    function checkConfig(){};

    require_once '../../autoload.php'; 
}
AutoLoad::init();

define ('DB_PREFIX', 'phpunit_alex');
define ('DB_METADATA', 'phpunit_alexmetadata');
