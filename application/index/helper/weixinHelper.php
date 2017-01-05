<?php 
namespace app\index\helper;
use \Think\Log;
use think\Db;

class weixinHelper
{
	private static $token = 'qufulin';
		
	public static function checkSignature()
	{
        $signature = input('signature');
        $timestamp = input('timestamp');
        $nonce = input('nonce');
        		
		$token = self::$token;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}

	public static function responseMsg()
	{
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        if (!empty($postStr)){
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $msgType = $postObj->MsgType;
            $time = time();
            $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
   // 'ToUserName' => 'gh_a4baaa87f038',
   // 'FromUserName' => 'oglYms4Tv4oQRfoD6sO78YZF7c04',
   // 'CreateTime' => '1483625937',
   // 'MsgType' => 'text',
   // 'Content' => '啊',
   // 'MsgId' => '6372124879361132160',
   // 
   			try{
	   			switch($postObj->MsgType){
	   				case 'text': //文本消息
	   					if(!empty( $keyword ))
	   					{
	   						self::responseTextMsg($fromUsername,$keyword);
	   					} else {
	   						$contentStr = '你是不是想说些什么';
	   					}
	   					$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
	            		echo $resultStr;
		   				break;

		   			case 'event': //事件
		   				$event = $postObj->Event;
		   				$event_key = $postObj->EventKey;
	   					self::responseEventMsg($fromUsername,$event,$event_key);
		   				break;
	   			}
   			}catch(Exception $e){echo '';}
            Log::info($postObj); //记录数据
        }else {
            echo "";
            exit;
        }
	}

	/**
	 * 回复文本信息
	 */
	public static function responseTextMsg($open_id,$keyword){

	}

	/**
	 * 回复事件
	 */
	public static function responseEventMsg($open_id,$event,$event_key){
		if($open_id && $event_key && $event){
			switch($event){
				case 'unsubscribe': //取消关注
					$db = db::name('wx_user');
					Log::info($open_id);
					$info = $db->where('open_id','=',$open_id)->select(false);
					Log::info($info);
					db('wx_user')->where('open_id','=',$open_id)->setField('status',0);
				break;

				case 'subscribe': //关注
					$db = db::name('wx_user');
					//记录数据库
					//是否存在该用户
					Log::info($open_id);
					$info = $db->where('open_id','=',$open_id)->select(false);
					Log::info($info);
					if($info)
						$db->where('open_id','=',$open_id)->setField('status',1);
					else{
						$data = [
							'open_id' => $open_id,
							'name' => '',
							'add_time' => time(),
							'status' => 1,
							'leave_time' => 0,
						];
						$db->insert($data);
					}
				break;
			}

		}else throw new Exception("无法处理");
	}


}
