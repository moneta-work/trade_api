<?php
/**
 * 火币卖,OK买,即检测火币买一与OK卖一
 */
require __DIR__."/../config.php";
require __DIR__."/../lib/Common.php";
require __DIR__."/../lib/Redis.php";
require __DIR__."/../lib/Huobi.php";
require __DIR__."/../lib/Okcoin.php";
//while(true){
 //   sleep(1);
    //获取当前价格
    try{
        $result = priceDiff('huobi2okcoin');
        //火币买一减OK卖一　小于额度则忽略
        if($result['buy'] - $result['sell'] < PRICE_DIFF){
            //continue;
            exit('price not diff');
        }
        //设置挂单 ok.buy1, huobi.sell1
        $buy1 = $result['sell']*(1 + TRUST_PERCENT_BUY);
        $sell1 = $result['buy']*(1 - TRUST_PERCENT_SALE);
        //挂单 卖优先
        $huobi = Huobi::sell($sell1, PER_NUMBER);
        if($huobi['result'] != 'success'){
            //卖单失败log
            RedisCache::instance()->hset('huobi_sell_fail', time(), 1);
            //continue;
            exit('huobi_sell_fail');
        }
        $okcoin = Okcoin::trade($buy1, PER_NUMBER, 'buy');
        if($okcoin['result'] != true){
            RedisCache::instance()->hset('okcoin_buy_fail', time(), 1);
            //continue;
            exit('okcoin_buy_fail');
        }
        //成功后记录trust_id
        RedisCache::instance()->hset('huobi_sell', $huobi['id'], 0);
        RedisCache::instance()->hset('okcoin_buy', $okcoin['order_id'], 0);
    } catch (Exception $e) {
        error_log('huobi2okcoin fail'.date('Y-m-d H:i:s')."\n", 3, __DIR__.'/../log/price_diff.log');
    }
//}
