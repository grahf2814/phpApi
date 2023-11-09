<?php
namespace App\db;
use Dotenv\DotEnv;
use App\Config\ResponseHttp;
use PDO;


class SQLDBConnection
{
    private $host = '';
    private $user = '';
    private $password = '';
    private $db='';
    private $ip='';
    private $port='';
    private $dbServer='';
    
    
    public function __construct($prefix,$dbServer)
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__,2));
        $dotenv->load();
        
        $this->dbServer=$dbServer;
        $this->user=$_ENV[$prefix.'USER'];
        $this->password=$_ENV[$prefix.'PASSWORD'];
        $this->db=$_ENV[$prefix.'DB'];
        $this->ip=$_ENV[$prefix.'IP'];
        $this->port=$_ENV[$prefix.'PORT'];
        

        if(empty($this->dbServer) || empty($this->user) /*|| empty($data['password'])*/ || empty($this->db) || empty($this->ip) || empty($this->port))
        {
            error_log('campos de DB vacios');
            die(json_encode(ResponseHttp::status500('Datos de la BD vacios')));
        }
        else if (strtolower($this->dbServer)==='mysql')
        {
            $this->host = 'mysql:host='.$this->ip.';port='.$this->port.';dbname='.$this->db;
        }
        else if (strtolower($this->dbServer)==='sqlserver')
        {
            $this->host = 'sqlsrv:server='.$this->ip.','.$this->port.';database='.$this->db; 
        }
    }

    final public function getConnection()
    {
        try
        {
            $opt = [\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC];
            $dsn = new PDO($this->host,$this->user,$this->password,$opt);
            $dsn->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
            return $dsn;
        }
        catch(\PDOException $p)
        {
            error_log('Error de conexion: '. $p);
            die (json_encode(ResponseHttp::status500('')));
        }
    }
}