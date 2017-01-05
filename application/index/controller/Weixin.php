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
}
