<?php
require 'config.php';

//$huobi = Huobi::buy(3011, 0.02);
$huobi = Huobi::sell(3311, 0.02);
var_dump($huobi);
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
