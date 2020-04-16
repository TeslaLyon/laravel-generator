<?php

use \Illuminate\Support\Facades\Redis;

if (!function_exists('BeSign')) {
    /**
     * 调用短信平台的加密方法
     *
     * @param $data
     *
     * @return bool
     * @author : liangshuo
     * @Date : 2019-11-28
     * @Time : 17:44
     */
    function BeSign($data)
    {
        if (empty($data)) {
            return false;
        }
        //替换为自己的验签密钥
        $secretkey = config('sms.SMS_SIGN_SECRET_KEY');
        if (empty($secretkey)) {
            return false;
        }
        unset($data['token']);
        $data['token'] = createToken();
        ksort($data);
        $string = implode('', $data); //把所有的值级成字符串
        $string = urlencode($string);
        $_sign = md5(md5($string).$secretkey);
        $data['sign'] = strtoupper($_sign);//签名转为大写字符串
        return $data;
    }
}


if (!function_exists('createToken')) {
    function createToken()
    {
        $app_key = config('sms.SMS_APP_KEY'); //sms分配的app_key
        $secretkey = config('sms.SMS_SIGN_SECRET_KEY'); //sms分配的secret_key
        $time = time(); //服务器当前时间戳
        $session = $time.$app_key.$secretkey;
        $sessionID = substr(md5($session), 0, 16);
        $tokenStr = $app_key.".".$sessionID.".".$time;
        return $tokenStr;
    }
}

if (!function_exists('sendMessage')) {
    /**
     * 发送短信
     *
     * @param  string  $mobiles
     * @param  string  $code
     *
     * @return bool
     * @author : liangshuo
     * @Date : 2019-11-28
     * @Time : 17:44
     */
    function sendMessage($mobiles = '', $code = '')
    {
        $url = config('sms.SMS_API_URL');
        $data = [];
        $data['mobile'] = $mobiles;

        //固定模板发送
        $data['sms_type'] = '1';
        $data['tag'] = config('sms.SMS_TAG');
        $data['keywords'] = '{"mobile_code":"'.$code.'"}';

        $smsdata = BeSign($data);
        $res = requestCurl($url, $smsdata);
        $res_arr = json_decode($res, true);

        //若因为本平台自身原因发送失败 则启用备用平台发送

        if ($res_arr['code'] !== 10000) {
            Log::error(
                'common.sendMessage() 发送验证码 error, 手机号：'.$mobiles.'; 返回值: '.$res
            );

            return false;
        }

        return true;
    }
}

if (!function_exists('getSignature')) {
    function getSignature($params)
    {
        $secret = config('app.api_sign_key');

        unset($params['sign']);

        $str = '';  //待签名字符串
        //先将参数以其参数名的字典序升序进行排序
        ksort($params);

        //遍历排序后的参数数组中的每一个key/value对

        foreach ($params as $k => $v) {
            //为key/value对生成一个key=value格式的字符串，并拼接到待签名字符串后面
            $str .= $k.':'.$v.',';
        }
        //将签名密钥拼接到签名字符串最后面
        $str .= 'sign:'.$secret;
        //通过md5算法为签名字符串生成一个md5签名，该签名就是我们要追加的sign参数值
        return md5($str);
    }
}
