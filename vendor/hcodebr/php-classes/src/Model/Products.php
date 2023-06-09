<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;

class Products extends Model{

   

    public static function listAll(){
        $sql = new Sql();

        return $sql->select("SELECT * FROM TB_PRODUCTS ORDER BY desproduct");
    }

    public static function checkList($list)
    {
        foreach($list as &$row) {
            $p = new Products();
            $p->setData($row);
            $row = $p->getValues();
        }

        return $list;
    }

    public function save(){
        $sql = new Sql();

        $array = [
            ":idproduct" => $this->getidproduct(),
            ":desproduct" => $this->getdesproduct(),
            ":vlprice" => $this->getvlprice(),
            ":vlwidth" => $this->getvlwidth(),
            ":vlheight" => $this->getvlheight(),
            ":vllenght" => $this->getvllength(),
            ":vlweight" => $this->getvlweight(),
            ":desurl" => $this->getdesurl()
        ];

        $result = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllenght, :vlweight, :desurl)", array(
            ":idproduct" => $this->getidproduct(),
            ":desproduct" => $this->getdesproduct(),
            ":vlprice" => $this->getvlprice(),
            ":vlwidth" => $this->getvlwidth(),
            ":vlheight" => $this->getvlheight(),
            ":vllenght" => $this->getvllength(),
            ":vlweight" => $this->getvlweight(),
            ":desurl" => $this->getdesurl()
        ));

        $this->setData($result[0]);
    }

    public function get($idproduct)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM TB_PRODUCTS WHERE idproduct = :idproduct", array(
            ":idproduct" => $idproduct
        ));
        $this->setData($results[0]);
    }

    public function delete()
    {
        $sql = new Sql();
        $sql->query("DELETE FROM TB_PRODUCTS WHERE idproduct = :idproduct", array(
            ":idproduct" => $this->getidproduct()
        ));

    }

    public function checkPhoto() 
    {
        if(file_exists(
            $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . 
            "res" . DIRECTORY_SEPARATOR . 
            "site" . DIRECTORY_SEPARATOR . 
            "img" . DIRECTORY_SEPARATOR . 
            "products" . DIRECTORY_SEPARATOR .
            $this->getidproduct() . ".jpg"
            )) {
            $url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";
        } else {
            $url ="/res/site/img/products/product.jpg";
        }  

        return $this->setdesphoto($url);
    }

    public function getValues()
    {
        $this->checkPhoto();
        $values = parent::getValues();
        return $values;
    }

    public function setPhoto($file)
    {
    
        $extension = explode('.',$file['name']);
        $extension = end($extension);
        switch ($extension) {
            case "jpg":
            case "jpeg":
                $image = imagecreatefromjpeg($file["tmp_name"]);
                break;

            case "gif":
                $image = imagecreatefromgif($file["tmp_name"]);
                break;

            case "png":
                $image = imagecreatefrompng($file["tmp_name"]);
                break;
        }

        $destino = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . 
        "res" . DIRECTORY_SEPARATOR . 
        "site" . DIRECTORY_SEPARATOR . 
        "img" . DIRECTORY_SEPARATOR . 
        "products" . DIRECTORY_SEPARATOR .
        $this->getidproduct() . ".jpg";

        imagejpeg($image, $destino);
        imagedestroy($image);
        $this->checkPhoto();
    }

}

?>