<?php
class Huobi{
    const ACCESS_KEY = '';
    const SECRET_KEY = '';
    const API_URL = 'https://api.huobi.com/api.php';

    public static function httpRequest($param, $pUrl='https://api.huobi.com/api.php'){
        $ch = curl_init();
        if($param){
            is_array($param) && $param = http_build_query($param);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
        curl_setopt($ch, CURLOPT_URL, $pUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public static function createSign($params = array()){
        $params['secret_key'] = self::SECRET_KEY;
        ksort($params);
        $preSign = http_build_query($params);
        $sign = md5($preSign);
        return strtolower($sign);
    }

    /**
     * total    总资产折合
     * net_asset    净资产折合
     * available_cny_display    可用人民币
     * available_btc_display    可用比特币
     * available_ltc_display    可用莱特币
     * frozen_cny_display   冻结人民币
     * frozen_btc_display   冻结比特币
     * frozen_ltc_display   冻结莱特币
     * loan_cny_display 借贷人民币数量
     * loan_btc_display 借贷比特币数量
     * loan_ltc_display
     */
    public static function getAccountInfo(){
        $params = array();
        $params['method'] = 'get_account_info';
        $params['access_key'] = self::ACCESS_KEY;
        $params['created'] = time();
        $params['sign'] = self::createSign($params);
        return self::httpRequest($params);
    }
}
