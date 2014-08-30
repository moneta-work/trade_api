<?php
/**
 * ok卖,火币买,即检测ok买一与火币卖一
 * 
 */
require __DIR__."/../config.php";
while(true){
    sleep(1);
    try{
        //账号余额
        $account_okcoin = Okcoin::getUserInfo();
        if($account_okcoin['free']['btc'] < $per_number){
            error_log('btc empty '.date('Y-m-d H:i:s')."\n", 3, '/tmp/ok_btc_empty.log');
            continue;
            //exit('okcoin.btc empty');
        }
        $account_huobi = Huobi::getAccountInfo();
        //for huobi bug
        if(!isset($account_huobi['available_btc_display'])){
            continue;
        }
        //获取当前价格 ok.buy huobi.sell
        $result = Common::priceDiff('okcoin2huobi');
        if($account_huobi['available_cny_display'] < ($result['sell'] * $per_number + 1)){
            error_log('cny empty '.date('Y-m-d H:i:s')."\n", 3, '/tmp/huobi_cny_empty.log');
            continue;
            //exit('huobi.cny empty');
        }
        //OK买一减火币卖一　小于额度则忽略
        if($result['buy'] - $result['sell'] < $price_diff){
            error_log('price not diff '.date('Y-m-d H:i:s')."\n", 3, '/tmp/price_not_diff.log');
            continue;
            //exit('price not diff');
        }
        //设置挂单 huobi.buy1, ok.sell1
        $buy1 = round($result['sell']*(1 + TRUST_PERCENT_BUY), 2);
        $sell1 = round($result['buy']*(1 - TRUST_PERCENT_SALE), 2);
        $price_diff_real = $sell1 - $buy1;
        //网络bug 价格可能为0
        if($buy1 < 10 || $sell1 < 10 || $price_diff_real+1 < $price_diff){
            continue;
        }
        $time = time();
        //挂单 卖优先
        $okcoin = Okcoin::trade($sell1, $per_number, 'sell');
        if($okcoin['result'] != true){
            RedisCache::instance()->hset('okcoin_sell_fail', $time, 1);
            continue;
            //exit('okcoin_sell_fail');
        }
        $huobi = Huobi::buy($buy1, $per_number);
        if($huobi['result'] != 'success'){
            error_log(print_r($huobi,true)." {$buy1} {$per_number}", 3, '/tmp/huobi_buy_fail.log');
            //卖单失败log
            RedisCache::instance()->hset('huobi_buy_fail', $time, 1);
            //continue;
            exit('huobi_buy_fail');
        }
        //成功后更新差价计数
        $price_diff_real = floor($price_diff_real);
        RedisCache::instance(7)->zIncrBy('okcoin2huobi', 1, $price_diff_real);
        //成功后记录trust_id
        //RedisCache::instance()->hset('huobi_buy', $huobi['id'], $time);
        //RedisCache::instance()->hset('okcoin_sell', $okcoin['order_id'], $time);
    } catch (Exception $e) {
        error_log('okcoin2huobi fail'.date('Y-m-d H:i:s')."\n", 3, __DIR__.'/../log/price_diff.log');
    }
}
