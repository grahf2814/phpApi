<?php
namespace App\Config;

use Dotenv\DotEnv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Security
{

    private static $jwt_data;
    final public static function secretKey()
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__,2));
        $dotenv->load();
        return $_ENV['SECRET_KEY'];
    }

    final public static function createPassword(string $pw)
    {
        $pass = password_hash($pw,PASSWORD_BCRYPT);
        return $pass;
    }

    final public static function validatePassword(string $pw, string $pwh)
    {
        if(password_verify($pw,$pwh))
        {
            return true;
        }
        else
        {
            error_log("La constraseña es incorrecta");
            return false;
        }
    }

    final public static function createJWTToken(string $key, array $data)
    {
        $payload =array (
            "iat" => time(),
            "exp" => time()+ (10),
            "data"=> $data
        );

        $jwt = JWT::encode($payload,$key,'HS384');
        return $jwt;
    }

    final public static function validateJWTToken(array $token,string $key)
    {
        if(!isset($token['Authorization']))
        {
            die(json_encode(ResponseHttp::status400()));
            exit;
        }
        
        try
        {   
            $jwt = explode(" ", $token['Authorization']);
            $data = JWT::decode($jwt[1],new Key($key,'HS384'));
            self::$jwt_data = $data;
            return $data;
            exit;
        }
        catch(\Exception $e)
        {
            error_log('Token inválido o expirado');
            exit(json_encode(ResponseHttp::status401('Token Invalido o expirado')));
        }
    }

    final public static function getJWTData()
    {
        $jwt_decoded_array = json_decode(json_encode(self::$jwt_data),true);
        return $jwt_decoded_array['data'];
        exit;
    } 
}