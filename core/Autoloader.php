<?php
namespace Core;

/**
 * Class Autoloader untuk memuat class secara otomatis
 */
class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            // Convert namespace ke full path
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            
            // Cek file di direktori app
            $appFile = ROOT_PATH . DIRECTORY_SEPARATOR . $file;
            if (file_exists($appFile)) {
                require_once $appFile;
                return;
            }
            
            // Cek file di direktori core
            $coreFile = ROOT_PATH . DIRECTORY_SEPARATOR . $file;
            if (file_exists($coreFile)) {
                require_once $coreFile;
                return;
            }
        });
    }
}

// Register autoloader
Autoloader::register(); 