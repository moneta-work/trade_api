<?php
class Okcoin {

    protected static function okQuery($params, $url){
        $post_data = http_build_query($params, '', '&');
        $sign = md5 ($post_data.KEY_OKCOIN);
        $sign = strtoupper($sign);

        $query = "partner=".KEY_OKCOIN."&sign=".$sign."&".$post_data;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        $res = json_decode ($res,true);
        return $res;
    }

    public static function marketDepth($N=5){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.okcoin.com/api/depth.do");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $res = curl_exec($ch);
        $res = json_decode ($res,true);
        $res_ask = array_reverse(array_slice($res["asks"] , -$N, $N));
        $res_bid = array_slice($res["bids"] , 0, $N) ;
        $ans = array("asks"=>$res_ask,"bids"=>$res_bid);

        return $ans;
    }

    /**
     * 下单
     * @param $price 买入/或卖出价格
     * @param $amount 数量
     * @parma $type 市价单/限价单
     *
     */
    public static function trade($price,$amount,$type){
        $params=array(
            "amount" => $amount,
            "partner" => KEY_OKCOIN,
            "rate" => $price,
            "symbol" => 'btc_cny',
            "type" => strtolower($type)
        );
        $url = 'https://www.okcoin.com/api/trade.do';
        $res = self::okQuery($params, $url);
        return $res;
    }       

    public static function cancelOrder($id){
        $params = array("order_id"=>$id, "partner"=>KEY_OKCOIN, "symbol"=>"btc_cny");
        $url = 'https://www.okcoin.com/api/cancelorder.do';
        $res = self::okQuery($params, $url);
        return $res;
    }

    public static function getUserInfo(){
        $params = array("partner"=>KEY_OKCOIN);
        $url = 'https://www.okcoin.com/api/userinfo.do';
        $res = self::okQuery($params, $url);

        if($res["result"]){
            $res = array(
                "result"=>true, 
                "frozen"=>array("btc"=>$res["info"]["funds"]["freezed"]["btc"], "cny"=>$res["info"]["funds"]["freezed"]["cny"]),
                "free"=>array("btc"=>$res["info"]["funds"]["free"]["btc"], "cny"=>$res["info"]["funds"]["free"]["cny"])
            ); 
        } else {
            $res = array("result"=>false);
        }
        return $res;
    } 

    public static function getOrder($id){
        $params = array("order_id"=>$id,"partner"=>KEY_OKCOIN,"symbol"=>"btc_cny");
        $url = 'https://www.okcoin.com/api/getorder.do';
        $res = self::okQuery($params, $url);
        return $res;
    }
}
