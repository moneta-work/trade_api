<?php
/**
 * ok卖,火币买,即检测ok买一与火币卖一
 * 
 */
require __DIR__."/../config.php";
while(true){
    sleep(1);
    try{
        //判断账号余额
        $account_okcoin = Okcoin::getUserInfo();
        //是否满足最低btc额度
        if($price_diff == 0 && $account_okcoin['free']['btc'] > OKCOIN_BTC){
            continue;
        }
        if($account_okcoin['free']['btc'] < $per_number){
            error_log('btc empty '.date('Y-m-d H:i:s')."\n", 3, '/tmp/ok_btc_empty.log');
            continue;
            //exit('okcoin.btc empty');
        }
        $account_huobi = Huobi::getAccountInfo();
        //是否满足最低btc额度
        if($price_diff == 0 && $account_huobi['available_btc_display'] < HUOBI_BTC){
            continue;
        }
        //获取当前价格
        $result = Common::priceDiff('okcoin2huobi');
        if($account_huobi['available_cny_display'] <= ($result['sell'] * $per_number + 1)){
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
        //设置挂单 ok.buy1, huobi.sell1
        $buy1 = round($result['sell']*(1 + TRUST_PERCENT_BUY), 2);
        $sell1 = round($result['buy']*(1 - TRUST_PERCENT_SALE), 2);
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
            //卖单失败log
            RedisCache::instance()->hset('huobi_buy_fail', $time, 1);
            continue;
            //exit('huobi_buy_fail');
        }
        //成功后记录trust_id
        RedisCache::instance()->hset('huobi_buy', $huobi['id'], $time);
        RedisCache::instance()->hset('okcoin_sell', $okcoin['order_id'], $time);
    } catch (Exception $e) {
        error_log('okcoin2huobi fail'.date('Y-m-d H:i:s')."\n", 3, __DIR__.'/../log/price_diff.log');
    }
}
