<?php
class Market{
    #
    const HUOBI_MARKET = 'http://market.huobi.com/staticmarket/';
    #
    const OKCOIN_TICKER = 'https://www.okcoin.cn/api/ticker.do';
    const OKCOIN_DEPTH = 'https://www.okcoin.cn/api/depth.do';
    //
    const BTCC_TICKER = 'https://data.btcchina.com/data/ticker?market=';
    const BTCC_DEPTH = '';
    #
    const BTER_TICKER = 'http://data.bter.com/api/1/ticker/';
    const BTER_DEPTH = 'http://data.bter.com/api/1/depth/';
    public static function httpRequest($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }
    /**
     * 火币实时行情
     */
    public static function huobiTicker($coin='btc'){
        $data_url = self::HUOBI_MARKET."ticker_{$coin}_json.js";
        return self::httpRequest($data_url);
    }
    /**
     * 火币市场深度
     */
    public static function huobiDepth($coin='btc'){
        $data_url = self::HUOBI_MARKET."depth_{$coin}_json.js";
        return self::httpRequest($data_url);
    }
    /**
     * okcoin实时行情
     */
    public static function okcoinTicker($coin=''){
        $data_url = self::OKCOIN_TICKER;
        if(!empty($coin) && $coin == 'ltc'){
            $data_url = self::OKCOIN_TICKER."?symbol=ltc_cny";
        }
        return self::httpRequest($data_url);
    }
    /**
     * okcoin市场深度
     */
    public static function okcoinDepth($coin=''){
        $data_url = self::OKCOIN_DEPTH;
        if(!empty($coin) && $coin == 'ltc'){
            $data_url = self::OKCOIN_DEPTH."?symbol=ltc_cny";
        }
        return self::httpRequest($data_url);
    }
    /**
     * 比特币中国交易行情
     */
    public static function btccTicker($market='btccny'){
        $data_url = self::BTCC_TICKER.$market;
        return self::httpRequest($data_url);
    }
    /**
     * 比特儿交易行情
     */
    public static function bterTicker($pair){
        $data_url = self::BTER_TICKER.$pair;
        return self::httpRequest($data_url);
    }
    /**
     * 比特儿市场深度
     */
    public static function bterDepth($pair){
        $data_url = self::BTER_DEPTH.$pair;
        return self::httpRequest($data_url);
    }
}
