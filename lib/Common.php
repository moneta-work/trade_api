<?php
require __DIR__.'/Market.php';
/**
 * 卖一买一数据对比,huobi,okcoin,btcc
 * sell2buy
 * huobi2okcoin, return huobi.buy,okcoin,sell
 *
 */
function priceDiff($market)
{
    $result = array('buy'=>0, 'sell'=>0);
    try{
        switch($market){
            case 'huobi2okcoin':
                $buy = Market::huobiTicker();
                $sell = Market::okcoinTicker();
                break;
            case 'huobi2btcc':
                $buy = Market::huobiTicker();
                $sell = Market::btccTicker();
                break;
            case 'okcoin2huobi':
                $buy = Market::okcoinTicker();
                $sell = Market::huobiTicker();
                break;
            case 'okcoin2btcc':
                $buy = Market::okcoinTicker();
                $sell = Market::btccTicker();
                break;
            case 'btcc2huobi':
                $buy = Market::btccTicker();
                $sell = Market::huobiTicker();
                break;
            case 'btcc2okcoin':
                $buy = Market::btccTicker();
                $sell = Market::okcoinTicker();
                break;
            //以下备用
            case 'huobi2bter':
                $buy = Market::huobiTicker('btc');
                $sell = Market::bterTicker('btc_cny');
                break;
            case 'okcoin2bter':
                $buy = Market::okcoinTicker();
                $sell = Market::bterTicker('btc_cny');
                break;
            case 'bter2huobi':
                $buy = Market::bterTicker('btc_cny');
                $sell = Market::huobiTicker('btc');
                break;
            case 'bter2okcoin':
                $buy = Market::bterTicker('btc_cny');
                $sell = Market::okcoinTicker();
                break;
        }
    } catch (Exception $e) {
        return false;
    }
    $result['buy'] = $buy['ticker']['buy'];
    $result['sell'] = $sell['ticker']['sell'];
    return $result;
}

