<?php
namespace app\index\controller;
use app\index\helper\weixinHelper;
use \Think\Log;

class Weixin
{
    public function valid()
    {
        $echoStr = input('echostr');
        //处理消息
        weixinHelper::responseMsg();

        if(weixinHelper::checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    public function Weather()
    {
        $postObj = self::getWxPostData();
        var_dump($postObj);die;
    }

    //妈蛋 也没有权限 连个菜单都搞不了...
    public function menuCreate(){
        $menu = [
            'button' => [
                [
                    'type' => 'click',
                    'name' => '今日天气',
                    'key' => 'weather',
                ],
            ]
        ];
        $access_token = weixinHelper::getAccessToken();
        $data = curl('https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token,$menu,'post');
        var_dump($data);


    }
}
