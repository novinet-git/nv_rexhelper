<?php

namespace nvRexHelper;

/**
 * helpfull function for building a string from an array
 * e.g. build type="text" class="form-control" name="zip" autocomplete="off" value="test" required
 * from 
 * $dataArray = [
 * "type" => "text",
 * "class" => "form-control",
 * "name" => $this->id,
 * "autocomplete" => "off",
 * "value" => rex_request($this->id, "string", ""),
 * "required" => ""
 * ];
 * 
 * 
 * @param array $dataArray
 * @return string
 */

function buildAttributes($dataArray=[])
{
    if(empty($dataArray)) return "";

    $arr = [];

    foreach($dataArray as $key => $value)
    {
        $arr[] = $value ? $key . '="' . $value . '"' : $key;
    }   
    
    return implode(" ", $arr);
}