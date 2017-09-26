<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InfyOm\Generator\Utils\ResponseUtil;
use Response;
error_reporting(0);
/**
 * @SWG\Swagger(
 *   basePath="/api/v1",
 *   @SWG\Info(
 *     title="Laravel Generator APIs",
 *     version="1.0.0",
 *   )
 * )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends Controller
{
    public function sendResponse($result, $message)
    {
        return Response::json(ResponseUtil::makeResponse($message, $result));
    }

    public function sendError($error, $code = 404)
    {
        return Response::json(ResponseUtil::makeError($error), $code);
    }

    function https_request($url, $data = null)
    {
        //这个方法我不知道是怎么个意思  我看都是这个方法 就copy过来了
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    public static function curlGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    /**
     * @biref 发送数据post
     * @param $url 传递路径
     * @param $post_date post数据
     */
    public static function curlPost($url, $post_data){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // stop verifying certificate
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true); // enable posting
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data); // post
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // if any redirection after upload
        $data = curl_exec($curl);
        curl_close($curl);

        return $data;
    }

     public function index(){
        if($_GET['echostr']){
            $this->serve();
        }else{
            $postStr = file_get_contents("php://input");   // 获取 POST 提交的原始数据
            if (!empty($postStr)) {
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
                switch($postObj->MsgType){
                    case 'text':
                        $result = sprintf($textTpl,$postObj->FromUserName,$postObj->ToUserName,time(),'text','nihao');
                        break;
                    case "event":
                        switch($postObj->Event){
                            case 'subscribe':
                                $result = sprintf($textTpl,$postObj->FromUserName,$postObj->ToUserName,time(),'text','欢迎关注本公众号！');
                                break;
                            case 'CLICK':
                                if ($postObj->EventKey == 'userinfo'){
                                    $content = $this->getUserInfo($postObj->FromUserName);
                                    $result = sprintf($textTpl,$postObj->FromUserName,$postObj->ToUserName,time(),'text',$content);
                                    Log::info($result);
                                }
                                break;
                        }
                        break;
                }
                return $result;
            }
        }
    }

    public function sendTemplate($openid){
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this->getAccessToken();
        $post_data_arr = array(
            'touser' => "$openid",
            'template_id' => 'UbVCT6dWFuXwvzqYzUfQcnPkkmQkH6CuhjTVhqNMrho',
            'url' => 'www.baidu.com',
            'data' => array(
                'first' => array('value' => '恭喜你，获得一个红包'),
                'keynote1' => array('value' => '微信红包'),
                'keynote2' => array('value' => '0.01元'),
                'keynote3' => array('value' => date('Y-m-d')),
                'remark' => array('value' => '祝您下次更好运')
            )
        );
        $post_data = json_encode($post_data_arr);
        $result = self::curlPost($url,$post_data);
        Log::info('openid:'.$openid.';-------------result:'.$result);
        if(json_decode($result)->errcode && json_decode($result)->errcode == 0){
            return 1;
        }else{
            return 0;
        }
    }

    public function serve()
    {
        $echoStr = $_GET["echostr"];
        if ($this->checkSignature()) {
            echo $echoStr;
            //如果你不知道是否验证成功  你可以先echo echostr 然后再写一个东西
            exit;
        }
    }

    public function checkSignature()
    {
        //signature 是微信传过来的 类似于签名的东西
        $signature = $_GET["signature"];
        //微信发过来的东西
        $timestamp = $_GET["timestamp"];
        //微信传过来的值  什么用我不知道...
        $nonce     = $_GET["nonce"];
        //定义你在微信公众号开发者模式里面定义的token
        $token  = "wechat";
        //三个变量 按照字典排序 形成一个数组
        $tmpArr = array(
            $token,
            $timestamp,
            $nonce
        );
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        //哈希加密  在laravel里面是Hash::
        $tmpStr = sha1($tmpStr);
        //按照微信的套路 给你一个signature没用是不可能的 这里就用得上了
        //Log::info('tmpstr:'.$tmpStr.';signature:'.$signature);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @brief 生成相关JS相关签名
     */
    public static function make_nonceStr(){
        $codeSet = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i<16; $i++) {
            $codes[$i] = $codeSet[mt_rand(0, strlen($codeSet)-1)];
        }
        return implode($codes);
    }

    public function getAccessToken(){
        $access_token = Cache::get('access_token');
        if(!$access_token){
            $appid = 'wx4cc00ed5ecf42876';
            $appsecret = 'e920068a6fd668d17675aa527cc1804a';
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;
            $result = self::curlGet($url);
            $access_token = json_decode($result)->access_token;
            Cache::put('access_token', $access_token, 120);
        }
        return $access_token;
    }


    public function getJsapiTicket(){
        $ticket = Cache::get('ticket');
        if(!$ticket){
            $access_token = $this->getAccessToken();
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
            $result = self::curlGet($url);
            $ticket = json_decode($result)->ticket;
            Cache::put('ticket',$ticket,120);
        }
        return $ticket;
    }

    public function getWeixinParam($redirect_url){
        $jsapiticket = $this->getJsapiTicket();
        $nonceStr = self::make_nonceStr();
        $timestamp = time();
        $sign_str = 'jsapi_ticket='.$jsapiticket.'&noncestr='.$nonceStr.'&timestamp='.$timestamp.'&url='.$redirect_url;
        $signature = sha1($sign_str);
        //dd($sign_str);
        Log::info('signature:'.$signature.';noncestr:'.$nonceStr.';timestamp:'.$timestamp.';signature:'.$signature.';url:'.$redirect_url);
        $sign['timestamp'] = $timestamp;
        $sign['nonce'] = $nonceStr;
        $sign['signature'] = $signature;
        return $sign;
    }

    public function createMenu(){
        $access_token = $this->getAccessToken();
        $menu_arr = array(
            'button' => array(
                array(
                    'name' => '主菜单1',
                    'key' => 'KEY_MENU_1',
                    'sub_button' => array(
                        array(
                            'type' => 'view',
                            'name' => '搜索',
                            'url' => 'http://www.baidu.com'
                        ),
                        array(
                            "type" => "scancode_waitmsg",
                            "name" => "扫码带提示",
                            "key" => "rselfmenu_0_0",
                        )
                    )
                ),
                array(
                    'name' => '主菜单2',
                    'key' => 'KEY_MENU_2',
                    'sub_button' => array(
                        array(
                            'type' => 'view',
                            'name' => '搜索',
                            'url' => 'http://www.baidu.com'
                        ),
                        array(
                            "type" => "scancode_waitmsg",
                            "name" => "扫码带提示",
                            "key" => "rselfmenu_0_0",
                        )
                    )
                ),
                array(
                    'name' => '用户中心',
                    'key' => 'KEY_MENU_3',
                    'sub_button' => array(
                        array(
                            'type' => 'click',
                            'name' => '用户信息',
                            'key' => 'userinfo'
                        ),
                        array(
                            "type" => "scancode_waitmsg",
                            "name" => "扫码带提示",
                            "key" => "rselfmenu_0_0",
                        )
                    )
                )
            )
        );
        $menu = json_encode($menu_arr,JSON_UNESCAPED_UNICODE);
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
        $result = $this->curlPost($url,$menu);
        dd($result);
    }

    public function getUserInfo($openid){
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccessToken().'&openid='.$openid.'&lang=zh_CN';
        $result = self::curlGet($url);
        Log::info($result);
        return $result;
    }

}
