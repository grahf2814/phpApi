<?php 

namespace App\Models;

use App\Config\ResponseHttp;
use App\Config\Security;
use App\db\SQLDBConnection;
use App\DB\Sql;

class ProductModel extends SQLDBConnection
{
    private string $name;
    private string $description;
    private $stock;
    private $file;
    private string $url;
    private string $imageName;
    private string $IDtoken;
    
    public function __construct()    
    {   
        
        parent::__construct('','mysql');
        
    }
    /************************Metodos Getter**************************/
    final public function getName(){ return $this->name;}
    final public function getDescription(){ return $this->description;}
    final public function getStock(){ return $this->stock;}
    final public function getFile(){ return $this->file;}
    final public function getUrl(){ return $this->url;}
    final public function getImageName(){ return $this->imageName;}
    final public function getIDtoken(){ return $this->IDtoken;}

    /**********************************Metodos Setter***********************************/
    final public function setName(string $name) { $this->name = $name;}
    final public function setDescription(string $description) { $this->description = $description;}
    final public function setStock(string $stock) { $this->stock = $stock;}
    final public function setFile(string $file) { $this->file = $file;}
    final public function setUrl(string $url) { $this->url = $url;} 
    final public function setImageName(string $imageName) { $this->imageName = $imageName;}  
    final public function setIDtoken(string $IDtoken) { $this->IDtoken = $IDtoken;}

    final public function postSave(array $data, $file)
    {
        
        $this->name = $data['name'];
        $this->description=$data['description'];
        $this->stock = $data['stock'];
        $this->file = $file;
        $sql=new Sql();
        if($sql->exists("SELECT name FROM productos WHERE name = :name",':name',$this->getName()))
        {
            return ResponseHttp::status400('El producto ya está registrado');
        }
        else
        {
            try
            {
                $resImg = Security::uploadImage($this->getFile(),'product');
                $this->setUrl($resImg['path']);
                $this->setImageName($resImg['name']);
                $this->setIDtoken(hash('sha512',$this->getName().$this->getUrl()));

                $con = $this->getConnection();
                $query = $con->prepare('INSERT INTO productos(name,descripcion,stock,url,imageName,IDtoken) VALUES (:name,:description,:stock,:url,:imageName,:IDtoken)');
                $query->execute([
                    ':name'        => $this->getName(),
                    ':description' => $this->getDescription(),
                    ':stock'       => $this->getStock(),
                    ':url'         => $this->getUrl(),
                    ':imageName'   => $this->getImageName(),
                    ':IDtoken'     => $this->getIDtoken()
                ]);
                
                if ($query->rowCount() > 0) {
                    return ResponseHttp::status200('Producto registrado');
                } else {
                    return ResponseHttp::status500('No se puede registrar el producto');
                }        


            }
            catch(\PDOException $e)
        {
            error_log('ProductModel::postSave->'.$e);
            die(json_encode(ResponseHttp::status500('No se pudo registrar el producto')));
        }
        }
    }
}