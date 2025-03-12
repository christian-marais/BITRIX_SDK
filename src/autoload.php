<?php

const NS2B_MODULES_PATH = __DIR__ . '/modules/';

spl_autoload_register(function ($class) {
    $prefix = 'NS2B\\SDK\\MODULES\\';
    $baseDir = NS2B_MODULES_PATH;
    
    if (strpos($class, $prefix) === 0) {
        $relativeClass = substr($class, strlen($prefix));

        // Remplace les "\" du namespace par "/" pour le chemin du fichier
        $relativePath=explode('\\', $relativeClass);
        if(count($relativePath)==4){
            $relativePath=$relativePath[0].'.'. $relativePath[1].'.'.$relativePath[2].'/'.$relativePath[3].'.php';

            $file = $baseDir . $relativePath;
            if (file_exists($file)) {
                require $file;
            }
        }
    }
});