<?php
namespace api\common\lib;


trait TraitInstance{
    private static $instance = null;
    static function getInstance($config=null)
    {
        if(!self::$instance){
            self::$instance = new static($config);
        }
        return self::$instance;
    }
}