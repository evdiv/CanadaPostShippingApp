<?php 

//Autoload Classes
spl_autoload_register(function($className) {
	
	$className = explode('\\', $className);
	$filePath = './classes/' . end($className) . '.class.php';

    if (file_exists($filePath)) {
        require_once $filePath;
    } 
});