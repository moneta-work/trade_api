<?php
class Common{
    /**
     * 买一卖一数据对比,huobi,okcoin,btcc
     * buy2sell
     * huobi2okcoin, return huobi.buy,okcoin,sell
     *
     */
    public static function priceDiff($market)
    {
        $result = array('buy'=>0, 'sell'=>0);
        try{
            switch($market){
                case 'huobi2okcoin':
                    $buy = Market::huobiTicker();
                    $sell = Market::okcoinTicker();
                    break;
                case 'huobi2btcc':
                    $buy = Market::huobiTicker();
                    $sell = Market::btccTicker();
                    break;
                case 'okcoin2huobi':
                    $buy = Market::okcoinTicker();
                    $sell = Market::huobiTicker();
                    break;
                case 'okcoin2btcc':
                    $buy = Market::okcoinTicker();
                    $sell = Market::btccTicker();
                    break;
                case 'btcc2huobi':
                    $buy = Market::btccTicker();
                    $sell = Market::huobiTicker();
                    break;
                case 'btcc2okcoin':
                    $buy = Market::btccTicker();
                    $sell = Market::okcoinTicker();
                    break;
                //以下备用
                case 'huobi2bter':
                    $buy = Market::huobiTicker('btc');
                    $sell = Market::bterTicker('btc_cny');
                    break;
                case 'okcoin2bter':
                    $buy = Market::okcoinTicker();
                    $sell = Market::bterTicker('btc_cny');
                    break;
                case 'bter2huobi':
                    $buy = Market::bterTicker('btc_cny');
                    $sell = Market::huobiTicker('btc');
                    break;
                case 'bter2okcoin':
                    $buy = Market::bterTicker('btc_cny');
                    $sell = Market::okcoinTicker();
                    break;
            }
        } catch (Exception $e) {
            return false;
        }
        $result['buy'] = $buy['ticker']['buy'];
        $result['sell'] = $sell['ticker']['sell'];
        return $result;
    }

    /**
     * 发送邮件
     * @param $pAddress 地址 array or string
     * @param $pSubject 标题
     * @param $pBody 内容
     */
    public static function send($pAddress, $pSubject, $pBody, $pCcAddress = NULL){
        static $mail;
        if(!$mail){
            require preg_replace( '/Tool/' ,'' , dirname(__FILE__)) . 'Source/PHPMailer/PHPmailer.php';
            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->CharSet = 'utf-8';
            $mail->SMTPAuth = true;
            $mail->Port = 25;
            $mail->Host = "smtp.exmail.qq.com";
            $mail->From = "account@yuanbaohui.com";
            $mail->Username = "account@yuanbaohui.com";
            $mail->Password = "ybh2013hui!1";
            $mail->FromName = "tradeapi";
            $mail->IsHTML(true);
        }
        $mail->ClearAddresses();
        $mail->ClearCCs();
        $mail->ClearBCCs();
        if(is_array($pAddress)){
            foreach($pAddress as $v){
                $mail->AddAddress($v);
            }
            unset($v);
        } else {
            $mail->AddAddress($pAddress);
        }
        $pCcAddress && $mail->AddBCC($pCcAddress);
        $mail->Subject = $pSubject;
        $mail->MsgHTML(preg_replace('/\\\\/', '', $pBody));
        if($mail->Send()){
            return true;
        }
        return false;
    }
}
