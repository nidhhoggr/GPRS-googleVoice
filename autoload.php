<?php

include('vendor/autoload.php'); 

function myload($class) 
{
    $libClass = 'libs/'.$class.'.php';
    $classClass = 'classes/'.$class.'.php';
 
    if (is_file($libClass))
    {
        require_once ($libClass);
    }
    else if(is_file($classClass))
    {
        require_once ($classClass);
    }
}

spl_autoload_register("myload");
