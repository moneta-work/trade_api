<?php
function autoload($class){
    include __DIR__."/lib/".$class.".php";
}
spl_autoload_register('autoload');
require 'config.php';
require 'PHPmailer.php';

//$huobi = Market::huobiTicker('btc');
//$okcoin = Market::okcoinTicker();
//$btcc = Market::btccTicker('btccny');
//huobi
//print_r(Huobi::getAccountInfo());
//okcoin
//print_r(Okcoin::getUserInfo());
//bter
//print_r(Bter::getFunds());
//btcc
//print_r(Btcc::getInfo());
