<?php 
namespace app\index\helper;
use think\Exception;
use \Think\Log;
use think\Db;

class weixinHelper
{
	private static $token = 'qufulin';
	private static $appId = 'wx910005303f8443ff';
	private static $appSecret = 'f5fe67893849b8537341920d1c895b06';
	private static $ToUserName = '';
	private static $FromUserName = '';
	public static $msgType = [
		'text' => 1,
		'image' => 2,
		'voice' => 3,
		'video' => 4,
		'shortvideo' => 5,
		'location' => 6,
		'link' => 7,
	];
	public static $subscribeInfo = "你好朋友,欢迎来到感动在路上🌹🌹🌹\n公众号正在开发中,如果有想要的功能欢迎留言[机智]\n[嘿哈]目前开放的功能:\n\n[嘿哈]天气查询\n[嘿哈]回复1上海2无锡3北京\n[嘿哈]需要其他城市请告诉我\n\n个人微信号:qufulin 乐心\n[机智]添加时请备注[机智]";

	public static $defaultResponseMsg = "现在还没法回复你哦,该公众号功能正在开发中[皱眉]\n天气查询[嘿哈]回复数字1上海2无锡3北京\n也可以留言直接提交需求,我会逐步增加,谢谢";
		
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

	public static function sendResponseMsg($msgType,$contentStr){
		$time = time();
		$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					<FuncFlag>0</FuncFlag>
					</xml>";

		$resultStr = sprintf($textTpl, self::$FromUserName, self::$ToUserName, $time, $msgType, $contentStr);
		echo $resultStr;
	}

	//解析微信内容
	public static function getWxPostData()
	{
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		file_put_contents('1.txt',$postStr);
//		$postStr = '<xml><ToUserName><![CDATA[gh_a4baaa87f038]]></ToUserName>
//<FromUserName><![CDATA[oglYms4Tv4oQRfoD6sO78YZF7c04]]></FromUserName>
//<CreateTime>1483882344</CreateTime>
//<MsgType><![CDATA[text]]></MsgType>
//<Content><![CDATA[今天的天气怎么样]]></Content>
//<MsgId>6373226139040811930</MsgId>
//</xml>';

		if (!empty($postStr)){
			libxml_disable_entity_loader(true);
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			return $postObj ? $postObj : null;
		}else {
			return null;
		}
	}

	public static function responseMsg()
	{
		$postObj = self::getWxPostData();
        if (!empty($postObj)){
            self::$FromUserName = (string)$postObj->FromUserName;
			self::$ToUserName = (string)$postObj->ToUserName;
            $msgType = (string)$postObj->MsgType;

   			try{
				if(in_array($msgType,['text','image','voice','video','shortvideo','location','link'])){
					$funcName = 'response'.ucfirst($msgType).'Msg';
					$Obj = new weixinHelper();
					if(method_exists($Obj,$funcName))
						$responseMsg = call_user_func([$Obj,$funcName],$msgType,$postObj);
					else
						$responseMsg = '我还看不懂你发的消息哦';

					//记录用户消息
					self::logUserMsg($msgType,$postObj,$responseMsg);
				}else{
					switch($msgType){
		   				case 'event': //事件
							$event = $postObj->Event;
							$event_key = $postObj->EventKey;
							$responseMsg = self::responseEventMsg($event,$event_key);
						break;
						default:
							$responseMsg = '';
							break;
	   				}
				}
				self::sendResponseMsg('text',$responseMsg);

   			}catch(Exception $e){echo '';}
            Log::info($postObj); //记录数据
        }else {
            echo "";
        }
	}

	private static function logUserMsg($msgType,$postObj,$responseMsg,$responseMsgType='text'){
		$msgTypeId = isset(self::$msgType[$msgType]) ? self::$msgType[$msgType] : 0;
		$responsemsgTypeId = isset(self::$msgType[$responseMsgType]) ? self::$msgType[$responseMsgType] : 0;

		$data = [
			'open_id' => self::$FromUserName,
			'msg_id' => (string)$postObj->MsgId,
			'request_msg' => (string)$postObj->Content,
			'request_msg_type' => $msgTypeId,
			'media_id' => (string)$postObj->MediaId,
			'thumb_media_id' => (string)$postObj->ThumbMediaId,
			'response_msg' => $responseMsg,
			'response_msg_type' => $responsemsgTypeId,
			'add_time' => time(),
		];
		//将剩下的参数插入到data字段中
		unset($postObj->ToUserName);
		unset($postObj->FromUserName);
		unset($postObj->CreateTime);
		unset($postObj->MsgType);
		unset($postObj->MediaId);
		unset($postObj->ThumbMediaId);
		unset($postObj->MsgId);
		unset($postObj->Content);
		$other_data = [];
		foreach($postObj as $k=>$v){
			$other_data[$k] = (string)$v;
		}
		$data['request_data'] = $other_data ? serialize($other_data) : '';
		$m = db::name('wx_msg');
		if(!$m->where('open_id',$data['open_id'])->where('msg_id',$data['msg_id'])->find())
            $m->insert($data);
	}

	public static function msgOperateConfig(){
		//可配置内容
		$data = [
			[
				'pub_time' => 0,
				'expire_time' => 0,
				'preg' => '/1/',
				'class' => 'app\index\helper\weatherHelper',
				'func' => 'getTodayWeather',
				'params' => ['location' => '上海'],
			],
			[
				'pub_time' => 0,
				'expire_time' => 0,
				'preg' => '/2/',
				'class' => 'app\index\helper\weatherHelper',
				'func' => 'getTodayWeather',
				'params' => ['location' => '无锡'],
			],
			[
				'pub_time' => 0,
				'expire_time' => 0,
				'preg' => '/3/',
				'class' => 'app\index\helper\weatherHelper',
				'func' => 'getTodayWeather',
				'params' => ['location' => '北京'],
			]
		];
		$time = time();
		$result = [];
		foreach($data as $v){
			if($v['pub_time'] <= $time && ($v['expire_time'] == 0 || $v['expire_time'] >= $time)){
				$result[] = $v;
			}
		}
		return $result;
	}

	/**
	 * 回复文本信息
	 */
	public static function responseTextMsg($msgType,$postObj){
		$responseText = self::$defaultResponseMsg;
		switch($postObj->MsgType){
			case 'text':
				$content = (string)$postObj->Content;
				if(empty($content))
					return $responseText;

				//处理内容
				$msgOperateConfig = self::msgOperateConfig();
				if(!empty($msgOperateConfig)){
					foreach($msgOperateConfig as $v){
						if(preg_match($v['preg'],$content,$out)){
							if(class_exists($v['class'])){
								$obj = new $v['class']();
								if(method_exists($obj,$v['func'])){
									$responseText = call_user_func([$obj,$v['func']],self::$FromUserName,$v['params']);
//									var_dump($responseText);die;
                                    break;
								}
							}
						}
					}
				}
				break;
		}
		return $responseText;
	}

	/**
	 * 回复事件
	 */
	public static function responseEventMsg($event,$event_key){
		if(self::$FromUserName && $event_key && $event){
			switch($event){
				case 'unsubscribe': //取消关注
					$db = db::name('wx_user');
					$data = [
						'status' => 0,
						'leave_time'=> time(),
					];
					$db->where('open_id',self::$FromUserName)->update($data);
				break;

				case 'subscribe': //关注
					$db = db::name('wx_user');
					//记录数据库
					//是否存在该用户
					$info = $db->where('open_id',self::$FromUserName)->select();
					if($info)
						$db->where('open_id',self::$FromUserName)->setField('status',1);
					else{
						$data = [
							'open_id' => self::$FromUserName,
							'name' => '',
							'add_time' => time(),
							'status' => 1,
							'leave_time' => 0,
						];
						$db->insert($data);
					}
					return self::$subscribeInfo;
				break;
			}
			return '';

		}else throw new Exception("无法处理");
	}

	//获取access_token
	//统一使用该接口获取,防止错乱
	public static function getAccessToken(){
		//查询是否有可使用的access_token
		$m = db::name('wx_access_token');
		$time = time();
		$info = $m->where(['add_time' => ['lt',$time],'expire_time' => ['gt',$time]])->order('id desc')->find();
		if($info)
			return $info['access_token'];
		else{
			//请求新的token
			$data = http_curl('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.self::$appId.'&secret='.self::$appSecret);
			$access_token = $data['access_token'];
			$expire_in = $data['expires_in'];
			if(empty($access_token)){
				Log::notice('微信token获取失败');
				return '';
			}
			$data = [
				'access_token' => $access_token,
				'expire_in' => $expire_in,
				'add_time' => $time,
				'expire_time' => $time + $expire_in,
			];
			$m->insert($data);
			return $access_token;
		}
	}

	/**
	 * 获取用户信息
	 * 个人公众号不能获取用户信息...该功能无法使用
	 */
	public static function getUserInfo($open_id = ''){
		$open_id = $open_id ? $open_id : self::$FromUserName;
		$access_token = self::getAccessToken();
		$data = http_curl('https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$open_id.'&lang=zh_CN');
		var_dump($data);die;

	}


}
