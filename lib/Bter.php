<?php
class Bter{
	public static function query($path, array $req = array()) {
		// API settings, add your Key and Secret at here
		$key = '';
		$secret = '';
	 
		// generate a nonce to avoid problems with 32bits systems
		$mt = explode(' ', microtime());
		$req['nonce'] = $mt[1].substr($mt[0], 2, 6);
	 
		// generate the POST data string
		$post_data = http_build_query($req, '', '&');
		$sign = hash_hmac('sha512', $post_data, $secret);
	 
		// generate the extra headers
		$headers = array(
			'KEY: '.$key,
			'SIGN: '.$sign,
		);

		// curl handle (initialize if required)
		static $ch = null;
		if (is_null($ch)) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Bter PHP bot; '.php_uname('a').'; PHP/'.phpversion().')');
		}
		curl_setopt($ch, CURLOPT_URL, 'https://bter.com/api/'.$path);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		// run the query
		$res = curl_exec($ch);

		if ($res === false) throw new Exception('Curl error: '.curl_error($ch));
		//echo $res;
		$dec = json_decode($res, true);
		if (!$dec) throw new Exception('Invalid data: '.$res);
		return $dec;
	}

    /**
     * 获取账号余额
     *
     * @return array 
        "result":"true",
        "available_funds":{
            "CNY":"1122.16",
            "BTC":"0.83337671",
            "LTC":"94.364",
            "YAC":"0.07161",
            "WDC":"82.35029899"
        },
        "locked_funds":{
            "BTC":"0.0002",
            "LTC":"0.0002",
        }
     */
    public static function getFunds(){
        return self::query('1/private/getfunds');
    }

    /**
     * 取消订单
     *
     * @return array('result'=>true, 'msg'=>'Success');
     */
    public static function orderCancel($id){
        $data = array(
            'order_id' => $id
        );
        return self::query('1/private/cancelorder', $data);
    }

    /**
     * 订单消息
     *
     * @return array('result', 'msg', array(
     *                                  'id',
     *                                  'status',
     *                                  'pair',
     *                                  'type',
     *                                  'rate',
     *                                  'amount',
     *                                  'initial_rate',
     *                                  'initial_amount'
     *                                  ));
     */
    public static function orderInfo($id){
        $data = array(
            'order_id' => $id
        );
        return self::query('1/private/getorder', $data);
    }

    public static function orderList(){
        return self::query('1/private/orderlist');
    }

    /**
     * 创建交易
     * @param price 价格
     * @param amount 数量
     * @param pair 买卖币种
     * @param type buy或sell
     *
     * @return array("result":"true","order_id":"12","msg":"Success)
     */
    public static function orderCreate($price, $amount, $pair='btc_cny', $type='buy'){
        $data = array(
            'pair' => $pair,
            'type' => $type,
            'rate' => $price,
            'amount' => $amount
        );
        return self::query('1/private/placeorder', $data);
    }
}
?> 
