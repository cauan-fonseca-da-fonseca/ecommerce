<?php

namespace Hcode;

use Rain\Tpl;
use Hcode\Model\User;

class Page {

    /* 
        __construct faz a criação do header
        setTpl faz a criação do body
        __destruct faz a criação do footer
    */

    private $tpl;
    private $options = array();
    private $defaults = array(
        "header"    => true,
        "footer"    => true,
        "data"      =>  array()
    );

    public function __construct($opts = array(), $tpl_dir = "/views/"){
        $this->options = array_merge($this->defaults, $opts);

        // Configuração do Rain-Tpl
        $config = array(
            "tpl_dir"   =>  $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
            "cache_dir" =>  $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
            "debug"     => false
        );

        Tpl::configure($config);

        $this->tpl = new Tpl;

        $this->setData($this->options["data"]);

        if ($this->options["header"]) $this->tpl->draw("header");
    }

    private function setData($data = array()){
        foreach ($data as $key => $value){
            $this->tpl->assign($key, $value);
        }
    }

    public function setTpl($name, $data = array(), $returnHTML = false){
        $this->setData($data);

        return $this->tpl->draw($name, $returnHTML);
    }

    public function __destruct(){
        if ($this->options["footer"]) $this->tpl->draw("footer");
    }
}

?>