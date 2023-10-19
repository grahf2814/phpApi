<?php
namespace App\Config;

class ResponseHttp
{
    public static $message = array(
        'status'=>'',
        'message'=>''
    );
    final public static function status200(string $res)
    {
        http_response_code(200);
        self::$message['status']='ok';
        self::$message['message']=$res;

        return self::$message;
    }
}