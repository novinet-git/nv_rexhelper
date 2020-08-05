<?php

/**
 * get the module id and a module hast to be in the stacktrace to work
 * 
 * @return int
 * 
 */

function traceModuleId()
{
    $id = 0;
    $trace = (new Exception())->getTrace();

    while(!$id && $item = array_shift($trace))
    {
        $file = explode("/", $item["file"]);
        $length = count($file);

        if ($length == 0) continue;

        for ($i = 0; $i < $length; $i++)
        {
            $part = $file[$i];
            if ($part == "module")
            {
                $next = $file[$i + 1];

                if($next && $val = intval($next))
                {
                    $id = $val;
                    break;
                }
            }
        }
    }

    return $id;
}