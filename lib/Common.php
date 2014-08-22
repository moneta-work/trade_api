<?php
/**
 * 卖一买一数据对比,huobi,okcoin,btcc
 * sale2buy
 * huobi2okcoin, return huobi.buy,okcoin,sale
 *
 */
function priceDiff($market)
{
    $result = array('buy'=>0, 'sale'=>0);
    try{
        switch($market){
            case 'huobi2okcoin':
                $buy = Market::huobiTicker();
                $sale = Market::okcoinTicker();
                break;
            case 'huobi2btcc':
                $buy = Market::huobiTicker();
                $sale = Market::btccTicker();
                break;
            case 'okcoin2huobi':
                $buy = Market::okcoinTicker();
                $sale = Market::huobiTicker();
                break;
            case 'okcoin2btcc':
                $buy = Market::okcoinTicker();
                $sale = Market::btccTicker();
                break;
            case 'btcc2huobi':
                $buy = Market::btccTicker();
                $sale = Market::huobiTicker();
                break;
            case 'btcc2okcoin':
                $buy = Market::btccTicker();
                $sale = Market::okcoinTicker();
                break;
            //以下备用
            case 'huobi2bter':
                $buy = Market::huobiTicker('btc');
                $sale = Market::bterTicker('btc_cny');
                break;
            case 'okcoin2bter':
                $buy = Market::okcoinTicker();
                $sale = Market::bterTicker('btc_cny');
                break;
            case 'bter2huobi':
                $buy = Market::bterTicker('btc_cny');
                $sale = Market::huobiTicker('btc');
                break;
            case 'bter2okcoin':
                $buy = Market::bterTicker('btc_cny');
                $sale = Market::okcoinTicker();
                break;
        }
    } catch (Exception $e) {
        return false;
    }
    $result['buy'] = $buy['ticker']['buy'];
    $result['sale'] = $sale['ticker']['sale'];
    return $result;
}

