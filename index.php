<?php
function autoload($class){
    include __DIR__."/lib/".$class.".php";
}
spl_autoload_register('autoload');

#huobi
#print_r(Huobi::getAccountInfo());

#okcoin
#print_r(Okcoin::fund());

#bter
#print_r(Bter::getFunds());

#btcc
print_r(Btcc::getInfo());
