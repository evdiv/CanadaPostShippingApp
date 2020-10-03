<?php 

spl_autoload_register(function($className) {

	$className = substr($className, strrpos($className, "\\") + 1);
	include_once __DIR__ . "/classes/" . $className . ".class.php"; 
});