<?php

namespace Hcode\Model;

use Exception;
use Hcode\Model;
use Hcode\DB\Sql;
use Hcode\Mailer;

class User extends Model{

    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret";
    const IV = "HcodePhp7_Secret";

    public static function login($login, $password){
        $sql = new Sql();

        $result = $sql->select("SELECT * FROM TB_USERS 
                                    WHERE deslogin = :deslogin", array(
            ":deslogin" =>  $login));

        if (count($result) === 0){
            throw new \Exception("Usuário inexiste ou senha inválida!");
        }else{
            $data = $result[0];
            if (password_verify($password, $data["despassword"])){
                $user = new User();

                $user->setData($data);

                $_SESSION[User::SESSION] = $user->getValues();

                return $user;
            }else{
                throw new \Exception("Usuário inexiste ou senha inválida!");
            }
        }
    }

    public static function verifyLogin($inadmin = true){
        if (!User::checkLogin($inadmin)){
            if($inadmin){
                header("Location: /admin/login");
            }else{
                header("Location: /login");
            }
            exit;
        }
    }

    public static function logout(){
        $_SESSION[User::SESSION] = NULL;
    }

    public static function listAll(){
        $sql = new Sql();

        return $sql->select("SELECT * FROM TB_USERS AS USER
                                INNER JOIN TB_PERSONS AS PERS USING(idperson)
                            ORDER BY PERS.idperson ASC");
    }

    public static function hashPassword($password){
        return password_hash($password, PASSWORD_DEFAULT, array(
            "cost"  => 12
        ));
    }

    public function save(){
        $sql = new Sql();

        $result = $sql->select("CALL SP_USERS_SAVE (
            :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
                ":desperson"    =>  $this   ->  getdesperson(),
                ":deslogin"     =>  $this   ->  getdeslogin(),
                ":despassword"  =>  User::hashPassword($this   ->  getdespassword()),
                ":desemail"     =>  $this   ->  getdesemail(),
                ":nrphone"      =>  $this   ->  getnrphone(),
                ":inadmin"      =>  $this   ->  getinadmin()));

        $this->setData($result[0]);
    }

    public function get($iduser)
    {
        $sql = new sql();

        $results = $sql->select("SELECT * FROM TB_USERS a INNER JOIN TB_PERSONS b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser" => $iduser
        ));

        $this->setData($results[0]);
    }

    public function update() 
    {
        $sql = new Sql();

        $result = $sql->select("CALL SP_USERSUPDATE_SAVE (:iduser,:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
                ":iduser" => $this->getiduser(),
                ":desperson" => $this -> getdesperson(),
                ":deslogin" => $this -> getdeslogin(),
                ":despassword" => User::hashPassword($this ->getdespassword()),
                ":desemail" => $this -> getdesemail(),
                ":nrphone" => $this -> getnrphone(),
                ":inadmin" => $this -> getinadmin()));

        $this->setData($result[0]);
    }
    
    public function delete() 
    {
        $sql = new Sql();
        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser" => $this->getiduser()
        ));
    }

    public static function getForgot($email) 
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM TB_PERSONS a INNER JOIN TB_USERS b USING(idperson)
        WHERE a.desemail = :email", array(
            ":email" => $email
        ));

        if(count($results) === 0)
        {
            throw new Exception("Não foi possivel recuperar a senha");
        } else {
            $data = $results[0];
            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser" => $data["iduser"],
                ":desip" => $_SERVER["REMOTE_ADDR"]
            ));

            if(count($results2) === 0) {
                throw new Exception("Não foi possivel recuperar a senha");
            } else {
                $dataRecovery = $results2[0];
                var_dump($dataRecovery);
                $code = base64_encode(openssl_encrypt($dataRecovery["idrecovery"], "aes-256-cbc", User::SECRET, OPENSSL_RAW_DATA, User::IV));
                $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefini Senha da Hcode Store", "forgot", array(
                    "name"=>$data["desperson"],
                    "link" => $link
                ));

                $mailer->send();

                return $data;

            }

        }
    }

    public static function validForgotDecrypt($code)
    {

        $idrecovery = openssl_decrypt(base64_decode($code), "aes-256-cbc", User::SECRET, OPENSSL_RAW_DATA, User::IV);
        $sql = new Sql();
        $results = $sql->select("
            SELECT *
            FROM TB_USERSPASSWORDSRECOVERIRS a
            INNER JOIN TB_USERS b USING(iduser)
            INNER JOIN TB_PERSONS c USING(idperson)
            WHERE a.idrecovery = :idrecovery
            AND
            a.dtrecovery IS NULL
            AND
            DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= now()", array(
                ":idrecovery" => $idrecovery
            ));

        if (count($results) === 0) {

            throw new Exception(("Não foi possivel recuperar a senha"));

        } else {
            return $results[0];
        }

    }

    public static function setForgotUsed($idrecovery)
    {
        $sql = new Sql();
        $sql->query("UPDATE TB_USERSPASSWORDSRECOVERIRS set dtrecovery = NOW() WHERE idrecovery = :idrecovery",array(
            ":idrecovery" => $idrecovery
        ));
    }

    public function setPassword($password)
    {

        $sql = new Sql();

        $sql->query("UPDATE TB_USERS set despassword = :password where iduser = :iduser", array(
            ":password" => $password,
            ":iduser" => $this->getiduser()
        ));

    }

    public static function checkLogin($inadmin = true){
        if(!isset($_SESSION[User::SESSION]) || !$_SESSION[User::SESSION] || !(int)$_SESSION[User::SESSION]["iduser"] > 0){
            return false;
        }else{
            if($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true){
                return true;
            }else if($inadmin === false){
                return true;
            }else{
                return false;
            }
        }
        die;
    }

    public static function checkLoginExist($login){
        $sql = new Sql();

        $result = $sql->select("SELECT * FROM TB_USERS WHERE deslogin = :deslogin", array(
            ":deslogin" =>  $login));

        return (count($result) > 0);
    }

}

?>