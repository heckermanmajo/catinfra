<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();

    ob_start();

    spl_autoload_register(function ($class_name)
    {
        $class_name = str_replace('\\', '/', $class_name);
        $file_path = __DIR__ . "/{$class_name}.php";
        if (file_exists($file_path))
        {
            require_once $file_path;
        }
    });