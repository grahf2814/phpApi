<?php

namespace App\Models;

use App\Config\ResponseHttp;
use App\Config\Security;
use App\db\ConnectionDB;
use App\DB\Sql;
class UserModel extends ConnectionDB
{
    private static string $nombre; 
    private static string $dni;
    private static string $correo;
    private static int $rol;
    private static string $password;
    private static string $IDToken;
    private static string $fecha;


    public function __construct(array $data)
    {
        self::$nombre=$data['name'];
        self::$dni=$data['dni'];
        self::$correo=$data['email'];
        self::$rol=$data['rol'];
        self::$password=$data['password'];
        
    }

    final public static function getName(){ return self::$nombre;}
    final public static function getDni(){ return self::$dni;}
    final public static function getEmail(){ return self::$correo;}
    final public static function getRol(){ return self::$rol;}
    final public static function getPassword(){ return self::$password;}
    final public static function getIDToken(){ return self::$IDToken;}
    final public static function getDate(){ return self::$fecha;}

    final public static function setName(string $name){ self::$nombre=$name;}
    final public static function setDni(string $dni){ self::$dni=$dni;}
    final public static function setEmail(string $email){ self::$correo=$email;}
    final public static function setRol(int $rol){ self::$rol=$rol;}
    final public static function setPassword(string $password){ self::$password=$password;}
    final public static function setIDToken(string $IDToken){ self::$IDToken=$IDToken;}
    final public static function setDate(string $date){ self::$fecha=$date;}

    final public static function login()
    {
        
        try
        {
            $con = self::getConnection()->prepare("SELECT * FROM usuario where correo=:correo");
            $con->execute(
                [
                    ':correo'=>self::getEmail()
                ]
                );
            if($con->rowCount()===0)
            {
                return ResponseHttp::status400('El usuario o contraseña son incorrectos');
            }
            else
            {
                foreach($con as $res)
                {
                    if(Security::validatePassword(self::getPassword(),$res['password']))
                    {
                        $payload = ['IDToken'=>$res['IDToken']];
                        $token = Security::createJWTToken(Security::secretKey(),$payload);
                        $data = 
                        [
                            'name'=>$res['nombre'],
                            'rol'=>$res['rol'],
                            'token'=>$token
                        ];
                        return ResponseHttp::status200($data);
                        exit;
                    }
                }
            }  

        }
        catch(\PDOException $e)
        {
            error_log('UserModel::Login->'.$e);
            die(json_encode(ResponseHttp::status500()));
        }
        return '';
    }

    final public static function getUser()
    {
        try
        {
            $con = self::getConnection();
            $query= $con->prepare('SELECT * FROM usuario WHERE dni=:dni');
            $query->execute([
                ':dni'=>self::getDni()
            ]);
            if($query->rowCount()==0)
            {
                return ResponseHttp::status400('El DNI ingresado no está registrado');
            }
            else
            {
                $rs['data']= $query->fetchAll(\PDO::FETCH_ASSOC);
                return $rs;
            }
            

        }
        catch(\PDOException $e)
        {
            error_log('UserModel::getUser->'.$e);
            die(json_encode(ResponseHttp::status500("No se pudieron obtener los datos")));
        }
    } 

    final public static function validateUserPassword(string $IDToken,string $oldPassword)
    {
        try
        {
            $con= self::getConnection();
            $query = $con->prepare("SELECT password FROM usuario WHERE IDToken = :IDToken");
            $query->execute([
                ':IDToken'=>$IDToken
            ]);

            if($query->rowCount()==0)
            {
                die(json_encode(ResponseHttp::status500()));
            }
            else
            {
                $res = $query->fetch(\PDO::FETCH_ASSOC);
                if(Security::validatePassword($oldPassword,$res['password']))
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }

        }
        catch(\PDOException $e)
        {
            error_log('UserModel::validateUserPassword->'.$e);
            die(json_encode(ResponseHttp::status500()));
        }
    }

    final public static function changeUserPassword()
    {
        try
        {
            $con = self::getConnection();
            $query = $con->prepare("UPDATE usuario SET password = :password WHERE IDToken = :IDToken");
            $query->execute(
            [
                ':password' => Security::createPassword(self::getPassword()),
                ':IDToken' => self::getIDToken()
            ]);
            if($query->rowCount()>0)
            {
                return ResponseHttp::status200('Contraseña actualizada exitosamente');
            }
            else
            {
                return ResponseHttp::status500('Error al Actualizar la contraseña de usuario');
            }
        }
        catch(\PDOException $e)
        {
            error_log('UserModel::changePassword->'.$e);
            die(json_encode(ResponseHttp::status500("No se pudieron obtener los datos")));
        }
    }
    
    final public static function getAll()
    {
        try
        {
            $con = self::getConnection();
            $query= $con->prepare('SELECT * FROM usuario');
            $query->execute();
            $rs['data']= $query->fetchAll(\PDO::FETCH_ASSOC);
            return $rs;

        }
        catch(\PDOException $e)
        {
            error_log('UserModel::getAll->'.$e);
            die(json_encode(ResponseHttp::status500("No se pudieron obtener los datos")));
        }
    }

    final public static function post()
    {
        if(Sql::exists("SELECT dni FROM usuario WHERE dni=:dni",":dni", self::getDni()))
        {
            return ResponseHttp::status400("El DNI ya está registrado");
        }
        else if (Sql::exists("SELECT correo FROM usuario WHERE correo=:correo",":correo", self::getEmail())) 
        {
            return ResponseHttp::status400("El Correo ya está registrado");
        }
        else
        {
            self::setIDToken(hash('sha512',self::getDni(),self::getEmail()));
            self::SetDate(date("d-m-y H:i:s"));
            try
            {
                $con = self::getConnection();
                $query1 = "INSERT INTO usuario (nombre,dni,correo,rol,password,IDToken,fecha) VALUES ";
                $query2 = "(:nombre,:dni,:correo,:rol,:password,:IDToken,:fecha)";

                $query = $con->prepare($query1.$query2);

                $query->execute(
                    [
                        ':nombre'=>self::getName(),
                        ':dni'=>self::getDni(),
                        ':correo'=>self::getEmail(),
                        ':rol'=>self::getRol(),
                        ':password'=>Security::createPassword(self::getPassword()),
                        ':IDToken'=>self::getIDToken(),
                        ':fecha'=>self::getDate()
                    ]);
                if($query->rowCount()>0)
                {
                    return ResponseHttp::status200("Usuario registrado exitosamente");
                }    
                else
                {
                    return ResponseHttp::status500("Nose pudo registrar el usuario");
                }
            }
            catch(\PDOException $e)
            {
                error_log('USerModel::post->'.$e);
                die(json_encode(ResponseHttp::status500()));
            }
        }
    }

    final public static function deleteUser()
    {
        try
        {
            $con = self::getConnection();
            $query = $con->prepare("DELETE FROM usuario WHERE IDToken = :IDToken");
            $query->execute
            (
                [
                    ':IDToken'=> self::getIDToken()
                ]
            );
            if($query->rowCount()>0)
            {
                return ResponseHttp::status200("Usuario eliminado exitosamente");
            }
            else
            {
                return ResponseHttp::status200("No se pudo eliminar al usuario");
            }
        }
        catch(\PDOException $e)
            {
                error_log('USerModel::deleteUser->'.$e);
                die(json_encode(ResponseHttp::status500()));
            }
    }

}