<?php

//*********************************************
// If the configuration doesn't exist check the system and run installer.

if(!file_exists(__DIR__ .'/config.php')){

    $setupFile = 'setup-config.php';

    if(!file_exists(__DIR__ . '/' . $setupFile)){
        $die = "<p>There doesn't seem to be a " . $setupFile . " file. 
        It is needed before the installation can continue.</p>";   
        
        die($die);
    }

    require_once __DIR__ . '/' . $setupFile;

} else {

    //************************
    // Get configuration

    require __DIR__.'/config.php';

    //**************************
    // Register The Auto Loader

    require __DIR__.'/autoloader.php';

    //***********************
    // API Routes

    require __DIR__.'/api.php';

}

