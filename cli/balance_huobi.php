<?php
/**
 * 平衡火币 btc
 * ok卖,huobi买
 * 
 */
require __DIR__."/../config.php";
while(true){
    sleep(1);
    try{
        $account_huobi = Huobi::getAccountInfo();
        if(!isset($account_huobi['available_btc_display']) || ($account_huobi['available_btc_display'] + $account_huobi['frozen_btc_display']) > BTC_HUOBI_MIN){
            continue;
            //exit('huobi.btc empty');
        }
        //获取当前价格
        $result = Common::priceDiff('okcoin2huobi');
        if($account_huobi['available_cny_display'] < ($result['sell'] * $per_number + 1)){
            continue;
            //exit('huobi.cny empty');
        }
        //判断huobi2okcoin是否有未对冲的卖单
        if(!RedisCache::instance(7)->zCount('huobi2okcoin', 1, 10000)){
            continue;
        }
        $diffs = RedisCache::instance(7)->sort('huobi2okcoin');
        //对冲的差价
        $diff_ok = 0;
        $diff_now = floor($result['buy'] - $result['sell']);
        foreach($diffs as $v){
            if(!RedisCache::instance(7)->zScore('huobi2okcoin', $v)){
                continue;
            }
            if($v + $diff_now >= $price_diff){
                $diff_ok = $v;
                break;
            }
        }
        if(!$diff_ok){
            continue;
        }
        //设置挂单 huobi.buy1, ok.sell1
        $buy1 = round($result['sell']*(1 + TRUST_PERCENT_BUY), 2);
        $sell1 = round($result['buy']*(1 - TRUST_PERCENT_SALE), 2);
        //网络bug 价格可能为0
        if($buy1 < 10 || $sell1 < 10){
            continue;
        }
        $time = time();
        //挂单 卖优先
        $okcoin = Okcoin::trade($sell1, $per_number, 'sell');
        if($okcoin['result'] != true){
            continue;
            //exit('okcoin_sell_fail');
        }
        $huobi = Huobi::buy($buy1, $per_number);
        if($huobi['result'] != 'success'){
            continue;
            //exit('huobi_buy_fail');
        }
        //成功后更新差价计数
        RedisCache::instance(7)->zIncrBy('huobi2okcoin', -1, $diff_ok);
        //成功后记录trust_id
        //RedisCache::instance()->hset('huobi_sell', $huobi['id'], $time);
        //RedisCache::instance()->hset('okcoin_buy', $okcoin['order_id'], $time);
    } catch (Exception $e) {
        error_log('huobi2okcoin fail'.date('Y-m-d H:i:s')."\n", 3, __DIR__.'/../log/price_diff.log');
    }
}
