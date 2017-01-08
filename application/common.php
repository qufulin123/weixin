<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

function http_curl($url,$param,$method='get',$is_json_decode=true){
   $httpInfo = array();
   if(is_array($param)){
      $param = http_build_query($param);
   }
   $ch = curl_init();

   curl_setopt($ch, CURLOPT_HEADER, 0);
   curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 30 );
   curl_setopt( $ch, CURLOPT_TIMEOUT , 30);
   curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
   if( $method == 'post')
   {
      curl_setopt( $ch , CURLOPT_POST , true );
      curl_setopt( $ch , CURLOPT_POSTFIELDS , $param );
      curl_setopt( $ch , CURLOPT_URL , $url );
   }
   else
   {
      if($param){
         curl_setopt( $ch , CURLOPT_URL , $url.'?'.$param );
      }else{
         curl_setopt( $ch , CURLOPT_URL , $url);
      }
   }
   $response = curl_exec( $ch );
   if ($response === FALSE) {
      return false;
   }
   $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
   $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
   curl_close( $ch );
   if($is_json_decode)
      $response = json_decode($response,true);
   return $response ? $response : false;
}