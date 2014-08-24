<?php
/**
 * ok卖,火币买,即检测ok买一与火币卖一
 */
require __DIR__."/../config.php";
require __DIR__."/../lib/Common.php";
require __DIR__."/../lib/Redis.php";
require __DIR__."/../lib/Huobi.php";
require __DIR__."/../lib/Okcoin.php";
//while(true){
 //   sleep(1);
    try{
        //判断账号余额
        $account_okcoin = Okcoin::getUserInfo();
        if($account_okcoin['free']['btc'] < PER_NUMBER){
            //continue;
            exit('okcoin.btc empty');
        }
        //获取当前价格
        $result = priceDiff('okcoin2huobi');
        $account_huobi = Huobi::getAccountInfo();
        if($account_huobi['available_cny_display'] <= ($result['sell'] * PER_NUMBER + 1)){
            //continue;
            exit('huobi.cny empty');
        }
        //OK买一减火币卖一　小于额度则忽略
        if($result['buy'] - $result['sell'] < PRICE_DIFF){
            //continue;
            exit('price not diff');
        }
        //设置挂单 ok.buy1, huobi.sell1
        $buy1 = round($result['sell']*(1 + TRUST_PERCENT_BUY), 2);
        $sell1 = round($result['buy']*(1 - TRUST_PERCENT_SALE), 2);
        $time = time();
        //挂单 卖优先
        $okcoin = Okcoin::trade($sell1, PER_NUMBER, 'sell');
        if($okcoin['result'] != true){
            RedisCache::instance()->hset('okcoin_sell_fail', $time, 1);
            //continue;
            exit('okcoin_sell_fail');
        }
        $huobi = Huobi::buy($buy1, PER_NUMBER);
        if($huobi['result'] != 'success'){
            //卖单失败log
            RedisCache::instance()->hset('huobi_buy_fail', $time, 1);
            //continue;
            exit('huobi_buy_fail');
        }
        //成功后记录trust_id
        RedisCache::instance()->hset('huobi_buy', $huobi['id'], $time);
        RedisCache::instance()->hset('okcoin_sell', $okcoin['order_id'], $time);
    } catch (Exception $e) {
        error_log('okcoin2huobi fail'.date('Y-m-d H:i:s')."\n", 3, __DIR__.'/../log/price_diff.log');
    }
//}
