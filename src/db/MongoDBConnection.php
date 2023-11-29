<?php
namespace App\db;
use Dotenv\DotEnv;
use App\Config\ResponseHttp;
use MongoDB;


class MongoDBConnection
{
    private $cString ='';
    private $connection;
    private $db;
    public function __construct($prefix)
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__,2));
        $dotenv->load();
        $this->cString= $_ENV[$prefix.'_STRING'];
        $this->db= $_ENV[$prefix.'_DB'];
        try
        {
            $mclient = new MongoDB\Client(
                $this->cString,
                [],
                ['typeMap' => [
                    'root' => 'array',
                    'document' => 'array',
                    'array' => 'array',
                ]]
            );
            $this->connection= $mclient->selectDatabase($this->db);
        }
        catch(\Exception $e)
        {
            error_log('Error de conexion a MongoDB: '. $e->getMessage());
            die (json_encode(ResponseHttp::status500('')));
        }

    }
    
    final public function getConection()
    {
        return $this->connection;
    }
}