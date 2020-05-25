<?php

namespace nvRexHelper;

class Layer 
{

    /**
     * the select options
     * [class => label]
     * 
     * @var array OPTIONS
     */

    const OPTIONS = [
        "nv-layer-none" => "Kein Layer",
        "nv-layer-primary" => "Primär Layer",
        "nv-layer-secondary" => "Sekundär Layer"
    ];

    /**
     * the default option
     * 
     * @var string DEFAULT
     */

    const DEFAULT = "nv-layer-none";

    /**
     * the key for the select field
     * 
     * @var string KEY
     */

    const KEY = "nvRexHelperSelectLayer";

    /**
     * get the select field for the module input
     * 
     * @param MForm $mform
     * @param int $id
     * 
     * @return bool
     */

    public static function AddSelect ($mform, $id=null) 
    {
        if (!$id) return false;

        $options = self::OPTIONS;

        $mform->addSelectField("$id.0." . self::KEY, $options, ["Layer"]);

        return true;
    }

    /**
     * get the layer for a certain value
     * 
     * @param string $value
     * 
     * @return string
     */

    public static function GetLayer ($item) 
    {
        $result = "";

        $value = $item[self::KEY] ?: self::DEFAULT;

        $result .= '<div class="nv-rexhelper-select-layer ' . $value . '"></div>';

        return $result;
    }

}