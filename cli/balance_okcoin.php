<?php
/**
 * 平衡okcoin btc
 * huobi卖,ok买
 * 
 */
require __DIR__."/../config.php";
while(true){
    sleep(1);
    try{
        $account_okcoin = Okcoin::getUserInfo();
        if(!$account_okcoin['result'] || ($account_okcoin['free']['btc']+$account_okcoin['frozen']['btc']) > BTC_OKCOIN_MIN){
            continue;
            //exit('ok.cny empty');
        }
        //获取当前价格
        $result = Common::priceDiff('huobi2okcoin');
        if(!$account_okcoin['result'] || $account_okcoin['free']['cny'] < ($per_number * $result['sell'] + 1)){
            continue;
            //exit('ok.cny empty');
        }
        //判断okcoin2huobi是否有未对冲的卖单
        if(!RedisCache::instance(7)->zCount('okcoin2huobi', 1, 10000)){
            continue;
        }
        $diffs = RedisCache::instance(7)->sort('okcoin2huobi');
        //对冲的差价
        $diff_ok = 0;
        $diff_now = floor($result['buy'] - $result['sell']);
        foreach($diffs as $v){
            if(!RedisCache::instance(7)->zScore('okcoin2huobi', $v)){
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
        $huobi = Huobi::sell($sell1, $per_number);
        if($huobi['result'] != 'success'){
            continue;
            //exit('huobi_sell_fail');
        }
        $okcoin = Okcoin::trade($buy1, $per_number, 'buy');
        if($okcoin['result'] != true){
            continue;
            //exit('okcoin_buy_fail');
        }
        //成功后更新差价计数
        RedisCache::instance(7)->zIncrBy('okcoin2huobi', -1, $diff_ok);
        //成功后记录trust_id
        //RedisCache::instance()->hset('huobi_sell', $huobi['id'], $time);
        //RedisCache::instance()->hset('okcoin_buy', $okcoin['order_id'], $time);
    } catch (Exception $e) {
        error_log('huobi2okcoin fail'.date('Y-m-d H:i:s')."\n", 3, __DIR__.'/../log/price_diff.log');
    }
}
