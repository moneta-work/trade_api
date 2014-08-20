<?php
class Okcoin {
    const  PARTNER = "";
    const  SECRETKEY ="";

    protected static function okQuery($parameters, $url){
        $post_data = http_build_query($parameters, '', '&');
        $sign = md5 ($post_data.self::SECRETKEY);
        $sign = strtoupper($sign);

        $query = "partner=".self::PARTNER."&sign=".$sign."&".$post_data;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
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

    public static function trade($Price,$Amount,$Direction){
        $parameters=array(
            "amount" => $Amount,
            "partner" => self::PARTNER,
            "rate" => $Price,
            "symbol" => 'btc_cny',
            "type" => strtolower($Direction)
        );
        $url = 'https://www.okcoin.com/api/trade.do';
        $res = self::okQuery($parameters, $url);
        return $res;
    }       

    public static function cancelOrder($OrderID){
        $parameters=array("order_id"=>$OrderID,"partner"=>self::PARTNER,"symbol"=>"btc_cny");
        $url = 'https://www.okcoin.com/api/cancelorder.do';
        $res = self::okQuery($parameters, $url);
        return $res;
    }

    public static function fund(){
        $parameters = array("partner"=>self::PARTNER);
        $url = 'https://www.okcoin.com/api/userinfo.do';
        $res = self::okQuery($parameters, $url);

        if($res["result"]){
            $res = array("result"=>true, "Frozen"=>array("BTC"=>$res["info"]["funds"]["freezed"]["btc"], "CNY"=>$res["info"]["funds"]["freezed"]["cny"]),
            "Free"=>array("BTC"=>$res["info"]["funds"]["free"]["btc"], "CNY"=>$res["info"]["funds"]["free"]["cny"])); 
            return $res;
        }
        $res = array("result"=>false);
        return $res;
    } 

    public static function getOrder($orderID){
        $parameters = array("order_id"=>$orderID,"partner"=>self::PARTNER,"symbol"=>"btc_cny");
        $url = 'https://www.okcoin.com/api/getorder.do';
        $res = self::okQuery($parameters, $url);
        return $res;
    }
}
