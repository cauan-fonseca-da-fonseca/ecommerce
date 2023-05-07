<?php

namespace Hcode;

class Model {

    private $values = array();

    public function __call($name, $arguments){
        $method = substr($name, 0, 3);

        $fieldname = substr($name, 3, strlen($name));

        switch ($method){
            case "get":
                return (isset($this->values[$fieldname]) ? $this->values[$fieldname] : null);
            break;

            case "set":
                $this->values[$fieldname] = $arguments[0];
            break;
        }
    }

    public function setData($data = array()){
        foreach ($data as $key => $values){
            $this->{"set".$key}($values);
        }
    }

    public function getValues(){
        return $this->values;
    }

}

?>