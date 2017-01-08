<?php
namespace app\index\controller;
use think\Db;
use think\db\Query;

class Index
{
    public function index()
    {
        $db = db('wx_user');
//        Query::event('before_select',function($options,$query){
//            // 事件处理
//            echo '111';
//        });
//        db('wx_user')->insert(array('open_id' => 1,'name' => 'qfl' ,'add_time' => time(),'status' => 0,'leave_time' => 0));
//        db('wx_user')->where('id',8)->update(['name' => 'qqqfl','add_time' => time()]);
//        $a = $db->where('name','like','qfl%')->where('status','=','0')->select(false);
        $a = 'qfl';
        $a = $db->where('name',$a)->select(false);
        dump($a);
    }

    public function test(){
    	// dump($_SERVER);
        $db = db::name('wx_user');
        $str = '<xml><ToUserName><![CDATA[gh_a4baaa87f038]]></ToUserName>
<FromUserName><![CDATA[oglYms4Tv4oQRfoD6sO78YZF7c04]]></FromUserName>
<CreateTime>1483860248</CreateTime>
<MsgType><![CDATA[event]]></MsgType>
<Event><![CDATA[subscribe]]></Event>
<EventKey><![CDATA[]]></EventKey>
</xml>';
        libxml_disable_entity_loader(true);
        $postObj =simplexml_load_string($str, 'SimpleXMLElement', LIBXML_NOCDATA);
        var_dump((string)$postObj->FromUserName);die;
        var_dump($postObj['FromUserName']);die;
        $info = $db->where('open_id','=',$postObj->FromUserName)->select(false);
        var_dump($info);die;
    	dump(input());die;
    }




}
