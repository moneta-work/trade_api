<?php
function autoload($class){
    include __DIR__."/lib/".$class.".php";
}
spl_autoload_register('autoload');
require 'config.php';

$huobi = Market::huobiTicker('btc');
//$okcoin = Market::okcoinTicker();
$btcc = Market::btccTicker('btccny');
print_r($huobi);
//print_r($okcoin);
print_r($btcc);
//huobi
//print_r(Huobi::getAccountInfo());
//okcoin
//print_r(Okcoin::fund());
//bter
//print_r(Bter::getFunds());
//btcc
//print_r(Btcc::getInfo());
