<?php

namespace nvRexHelper;

class Str
{
    static $cache;
    private $query;

    /**
    * get a Str object

    * @return Str
    */
    public static function Factory() : Str
    {
        if (self::$cache) return self::$cache;
        self::$cache = new self();
        return self::$cache;
    }

    /**
    * Prepare the query
    * @param string $query
    * @return void
    */
    public function Prepare(string $query) : void
    {
        $this->query = $query;
    }

    /**
    * Prepare & Execute the query
    * @param string $query
    * @param array $args
    * @return string
    */
    public function Set(string $query = "", array $args = []) : string
    {
        $this->Prepare($query);
        return $this->Execute($args);
    }

    /**
    * Execute the query
    * @param array $args
    * @return string
    */
    public function Execute(array $args = []) : string
    {
        $str = $this->query;
        foreach ($args as $key => $value)  $str = \str_replace(":$key", $value, $str);    
        return $str;
    }
}