<?php
namespace App\Config;

use Dotenv\DotEnv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Bulletproof\Image;

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
            error_log("La contraseña es incorrecta");
            return false;
        }
    }

    final public static function createJWTToken(string $key, array $data)
    {
        $payload =array (
            "iat" => time(),
            "exp" => time()+ (60*60*15),
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
    /***********Subir Imagen al servidor**************/
    final public static function uploadImage($file,$name)
    {
        $file = new Image($file);

        $file->setMime(array('png','jpg','jpeg'));//formatos admitidos
        $file->setSize(10000,500000);//Tamaño admitidos es Bytes
        $file->setDimension(200,200);//Dimensiones admitidas en Pixeles
        $file->setStorage('public/Images');//Ubicación de la carpeta

        if ($file[$name]) {
            $upload = $file->upload();            
            if ($upload) {
                $imgUrl = UrlBase::urlBase .'/public/Images/'. $upload->getName().'.'.$upload->getMime();
                $data = [
                    'path' => $imgUrl,
                    'name' => $upload->getName() .'.'. $upload->getMime()
                ];
                return $data;               
            } else {
                die(json_encode(ResponseHttp::status400($file->getError())));               
            }
        }
    }

    /***********************Subir fotos en base64***************************/
    final public static function uploadImageBase64(array $data, string $name) 
    {        
        $token = bin2hex(random_bytes(32).time()); 
        $name_img = $token . '.png';
        $route = dirname(__DIR__, 2) . "/public/Images/{$name_img}";        
    
        //Decodificamos la imagen
        $img_decoded = base64_decode(
            preg_replace('/^[^,]*,/', '', $data[$name])
        );
    
        $v = file_put_contents($route,$img_decoded);
    
        //Validamos si se subio la imagen
        if ($v) {
            return UrlBase::urlBase . "/public/Images/{$name_img}";
        } else {
            unlink($route);
            die(json_encode(ResponseHttp::status500('No se puede subir la imagen')));
        }   
        
    }
}