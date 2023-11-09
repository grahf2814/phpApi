<?php

namespace App\Models;

use App\Config\ResponseHttp;
use App\Config\Security;
use App\db\SQLDBConnection;
use App\DB\Sql;

class UserModel extends SQLDBConnection
{
    private string $nombre; 
    private string $dni;
    private string $correo;
    private int $rol;
    private string $password;
    private string $IDToken;



    public function __construct()
    {
        parent::__construct('','mysql');

        /*
        */
    }

    final public function getName(){ return $this->nombre;}
    final public function getDni(){ return $this->dni;}
    final public function getEmail(){ return $this->correo;}
    final public function getRol(){ return $this->rol;}
    final public function getPassword(){ return $this->password;}
    final public function getIDToken(){ return $this->IDToken;}


    final public function setName(string $name){ $this->nombre=$name;}
    final public function setDni(string $dni){ $this->dni=$dni;}
    final public function setEmail(string $email){ $this->correo=$email;}
    final public function setRol(int $rol){ $this->rol=$rol;}
    final public function setPassword(string $password){ $this->password=$password;}
    final public function setIDToken(string $IDToken){ $this->IDToken=$IDToken;}


    final public function login()
    {
        
        try
        {
            $con = $this->getConnection()->prepare("SELECT * FROM usuario where correo=:correo");
            $con->execute(
                [
                    ':correo'=>$this->getEmail()
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

    final public function getUser()
    {
        try
        {
            $con = $this->getConnection();
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

    final public function post($data)
    {
        
        
        $this->nombre=$data['name'];
        $this->dni=$data['dni'];
        $this->correo=$data['email'];
        $this->rol=$data['rol'];
        $this->password=$data['password'];
        $sql=new Sql();
                if($sql->exists("SELECT dni FROM usuario WHERE dni=:dni",":dni", self::getDni()))
        {
            return ResponseHttp::status400("El DNI ya está registrado");
        }
        else if ($sql->exists("SELECT correo FROM usuario WHERE correo=:correo",":correo", self::getEmail())) 
        {
            return ResponseHttp::status400("El Correo ya está registrado");
        }
        else
        {
            $this->setIDToken(hash('sha512',self::getDni(),self::getEmail()));
            
            try
            {
                $con = $this->getConnection();
                $query1 = "INSERT INTO usuario (nombre,dni,correo,rol,password,IDToken) VALUES ";
                $query2 = "(:nombre,:dni,:correo,:rol,:password,:IDToken)";

                $query = $con->prepare($query1.$query2);

                $query->execute(
                    [
                        ':nombre'=>$this->getName(),
                        ':dni'=>$this->getDni(),
                        ':correo'=>$this->getEmail(),
                        ':rol'=>$this->getRol(),
                        ':password'=>Security::createPassword(self::getPassword()),
                        ':IDToken'=>$this->getIDToken()
                        
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