<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WechatController extends AppBaseController
{


    private $appid = 'wx4cc00ed5ecf42876';
    private $appscrect = 'e920068a6fd668d17675aa527cc1804a';

    public function __construct(Request $request){
        $code = $request->input('code','');
        $redirect_url = 'http://' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if ($code == '') {
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->appid . '&redirect_uri=' . urlencode($redirect_url) . '&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect';
            header("Location:" . $url);
        }else{
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->appid . '&secret=' . $this->appscrect . '&code=' . $code . '&grant_type=authorization_code';
            $token = self::curlGet($url);
            $user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . json_decode($token)->access_token . '&openid=' . json_decode($token)->openid . '&lang=zh_CN';
            $user_info = self::curlGet($user_info_url);
            session(['user'=>$user_info]);
        }
    }

    public function createMenu(){
        $data = array('哈哈哈0','嘿嘿嘿','呵呵呵');
        dd(json_encode($data));
        $access_token = $this->getAccessToken();
        $menu = ' {
             "button":[
             {	
                  "type":"click",
                  "name":"主菜单1",
                  "key":"KEY_MENU_1"
              },
             {	
                  "type":"click",
                  "name":"主菜单2",
                  "key":"KEY_MENU_2"
              },
         
             {	
                  "type":"click",
                  "name":"主菜单3",
                  "key":"KEY_MENU_4"
              },
  
       ]
 }';
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
        $result = $this->curlPost($url,$menu);
        dd($result);
    }


    public function webIndex(){
        $url = 'http://' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $signArr = $this->getWeixinParam($url);
        //dd($signArr);
        return view('test_index')->with('signArr',$signArr);
    }




}
