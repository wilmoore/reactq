<?php

$files[] = realpath(__DIR__.'/autoload.php');
$files[] = realpath(__DIR__.'/autoload.php.dist');

foreach ($files as $file) {
    is_readable($file) && !defined('AUTOLOADER_PATH') && define('AUTOLOADER_PATH', $file);
}
unset($files, $file);

if (!defined('AUTOLOADER_PATH')) {
    throw new Exception('Unable to locate an autoloader');
}

require_once AUTOLOADER_PATH;
return AUTOLOADER_PATH;
