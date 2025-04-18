<?php

const NS2B_MODULES_PATH = __DIR__ . '/modules/';

spl_autoload_register(function ($class) {
    $prefix = 'NS2B\\SDK\\MODULES\\';
    $baseDir = NS2B_MODULES_PATH;
    
    if (strpos($class, $prefix) === 0) {
        $relativeClass = substr($class, strlen($prefix));

        // Remplace les "\" du namespace par "/" pour le chemin du fichier
        $relativePath=explode('\\', $relativeClass);
        switch(true){
            case count($relativePath)==4:
                $path=strtolower($relativePath[0].'.'. $relativePath[1].'.'.$relativePath[2]).'/'.$relativePath[3].'.php';
                break;
            case count($relativePath)>4:
                $path=strtolower($relativePath[0].'.'. $relativePath[1].'.'.$relativePath[2]).'/'.implode('/', array_slice($relativePath, 3, count($relativePath))).'.php';
                break;
            default:
                $file= array_pop($relativePath);
                $path=strtolower(implode('/', $relativePath)).'/'.$file.'.php';
                break;
        }
        
        $file = $baseDir . $path;
        
        if (file_exists($file)) {
            require $file;
        }
    }
});