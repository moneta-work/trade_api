<?php
//autoload
function autoload($class){
    include __DIR__."/lib/".$class.".php";
}
spl_autoload_register('autoload');
