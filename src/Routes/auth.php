<?php
use App\Config\Security;
use App\db\connectionDB;

echo json_encode(Security::createJWTToken(Security::secretKey(),['hola']));

ConnectionDB::getConnection();
/* $pass = Security::createPassword('prueba');

if(Security::validatePassword('prueba',$pass))
{
    echo json_encode("contraseña correcta");
}
else
{
    echo json_encode("algo salió mal");
}
 */

