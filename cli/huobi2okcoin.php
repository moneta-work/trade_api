<?php
/**
 * 火币卖,OK买,即检测火币买一与OK卖一
 * price_diff=1, 检测火币 币多钱少，ok 钱多币少
 */
require __DIR__."/../config.php";
while(true){
    sleep(1);
    try{
        //是否大于挂一笔单的btc
        $account_huobi = Huobi::getAccountInfo();
        if(!isset($account_huobi['available_btc_display']) || $account_huobi['available_btc_display'] < $per_number){
            error_log('btc empty '.date('Y-m-d H:i')."\n", 3, '/tmp/huobi_btc_empty.log');
            continue;
            //exit('huobi.btc empty');
        }
        $account_okcoin = Okcoin::getUserInfo();
        //获取当前价格
        $result = Common::priceDiff('huobi2okcoin');
        if(!$account_okcoin['result'] || $account_okcoin['free']['cny'] < ($per_number * $result['sell'] + 1)){
            error_log('cny empty '.date('Y-m-d H:i')."\n", 3, '/tmp/okcoin_cny_empty.log');
            continue;
            //exit('ok.cny empty');
        }
        //火币买一减OK卖一　小于额度则忽略
        if($result['buy'] - $result['sell'] < $price_diff){
            error_log('price not diff '.date('Y-m-d H:i')."\n", 3, '/tmp/price_not_diff.log');
            continue;
            //exit('price not diff');
        }
        //设置挂单 ok.buy1, huobi.sell1
        $buy1 = round($result['sell']*(1 + TRUST_PERCENT_BUY), 2);
        $sell1 = round($result['buy']*(1 - TRUST_PERCENT_SALE), 2);
        $price_diff_real = $sell1 - $buy1;
        //网络bug 价格可能为0
        if($buy1 < 10 || $sell1 < 10 || $price_diff_real+1 < $price_diff){
            continue;
        }
        $time = time();
        //挂单 卖优先
        $huobi = Huobi::sell($sell1, $per_number);
        if($huobi['result'] != 'success'){
            //卖单失败log
            RedisCache::instance()->hset('huobi_sell_fail', $time, 1);
            continue;
            //exit('huobi_sell_fail');
        }
        $okcoin = Okcoin::trade($buy1, $per_number, 'buy');
        if($okcoin['result'] != true){
            error_log(print_r($okcoin,true)." {$buy1} {$per_number}", 3, '/tmp/okcoin_buy_fail.log');
            RedisCache::instance()->hset('okcoin_buy_fail', $time, 1);
            //continue;
            exit('okcoin_buy_fail');
        }
        //成功后更新差价计数
        $price_diff_real = floor($price_diff_real);
        RedisCache::instance(7)->zIncrBy('huobi2okcoin', 1, $price_diff_real);
        //成功后记录trust_id
        //RedisCache::instance()->hset('huobi_sell', $huobi['id'], $time);
        //RedisCache::instance()->hset('okcoin_buy', $okcoin['order_id'], $time);
    } catch (Exception $e) {
        error_log('huobi2okcoin fail'.date('Y-m-d H:i:s')."\n", 3, __DIR__.'/../log/price_diff.log');
    }
}
