<?php 
namespace app\index\helper;
use think\Exception;
use \Think\Log;
use think\Db;

class weatherHelper
{
    private $juhe_appkey = '69aaecbabaab4cefe6cc8cfd9b250e8c';
    public function getTodayWeatherData($open_id='',$location='上海'){
        //************1.根据城市查询天气************
        $url = "http://op.juhe.cn/onebox/weather/query";
        $params = array(
            "cityname" => $location,//要查询的城市，如：温州、上海、北京
            "key" => $this->juhe_appkey,//应用APPKEY(应用详细页查询)
            "dtype" => "json",//返回数据的格式,xml或json，默认json
        );
        $data = http_curl($url,$params);
        if(empty($data) || $data['error_code'] != 0){
            Log::notice('天气请求错误,错误代码:'.$data['error_code'].',错误原因:'.$data['reason']);
            return false;
        }else{
            return $data['result']['data'];
        }
    }

    public function getTodayWeather($open_id='',$data = []){
        $location = isset($data['location']) ? $data['location'] : '';
        $data = $this->getTodayWeatherData($open_id,$location);
        if(empty($data))
            return '';

        //拼接文字
        $msg = [];
        //城市
        $msg[] = $data['realtime']['city_name'].' '.$data['pubdate'];
        //实时
        $msg[] = '实时:当前'.$data['realtime']['weather']['temperature'].'° '.$data['realtime']['weather']['info'].' '.$data['realtime']['wind']['direct'].' '.$data['realtime']['wind']['power'];

        //未来几天
        $msg[] = '未来几天预报:';
        foreach($data['weather'] as $v){
            $date = date('n月j日',strtotime($v['date']));
            $msg[] = $date.' 周'.$v['week'].' 白天:'.$v['info']['day'][1].' 最高温度'.$v['info']['day'][2].'°  夜间:'.$v['info']['night'][1].' 最高温度'.$v['info']['night'][2].'° ';
        }
        return join(PHP_EOL,$msg);
    }
}
