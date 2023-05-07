<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;

class Categories extends Model{

   

    public static function listAll(){
        $sql = new Sql();

        return $sql->select("SELECT * FROM TB_CATEGORIES ORDER BY descategory");
    }

    public function save(){
        $sql = new Sql();

        $result = $sql->select("CALL sp_categories_save (:idcategory, :descategory)", array(
            ":idcategory"=>$this->getidcategory(),
            ":descategory"=> $this->getdescategory()
        ));

        $this->setData($result[0]);

        Categories::updateFile();
    }

    public function get($idcategory)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM TB_CATEGORIES WHERE idcategory = :idcategory", array(
            ":idcategory" => $idcategory
        ));
        $this->setData($results[0]);
    }

    public function delete()
    {
        $sql = new Sql();
        $sql->query("DELETE FROM TB_CATEGORIES WHERE idcategory = :idcategory", array(
            ":idcategory" => $this->getidcategory()
        ));

        Categories::updateFile();
    }


    public static function updateFile()
    {
        $categories = Categories::listAll();

        $html = [];

        foreach($categories as $row) {
            array_push($html, '<li> <a href="/categories/' . $row['idcategory'].'">' . $row['descategory']. '</a></li>');
        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));
    }

}

?>