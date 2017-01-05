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
        $a = $db->where('name','like','qfl%')->where('status','=','0')->select(false);
        dump($a);
    }

    public function test(){
    	// dump($_SERVER);
    	dump(input());die;
    }




}
