<?php

use App\Config\ResponseHttp;
use App\db\ConnectionDB;


$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__,2));

$dotenv->load();

$data = array(
    'serverDB'=> $_ENV['SERVER_DB'],
    'user' => $_ENV['USER'],
    'password'=> $_ENV['PASSWORD'],
    'DB'=> $_ENV['DB'],
    'IP'=> $_ENV['IP'],
    'port'=> $_ENV['PORT']
);

if(empty($data['serverDB']) || empty($data['user']) /*|| empty($data['password'])*/ || empty($data['DB']) || empty($data['IP']) || empty($data['port']))
{
    error_log('campos de DB vacios');
    die(json_encode(ResponseHttp::status500('Datos de la BD vacios')));
}
else if (strtolower($data['serverDB'])==='mysql')
{
    $host = 'mysql:host='.$data['IP'].';port='.$data['port'].';dbname='.$data['DB'];
}
else if (strtolower($data['serverDB'])==='sqlserver')
{
    $host = 'sqlsrv:server='.$data['IP'].','.$data['port'].';database='.$data['DB']; 
}
ConnectionDB::from($host,$data['user'],$data['password']);