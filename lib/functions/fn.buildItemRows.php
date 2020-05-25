<?php

namespace nvRexHelper;

/**
 * build an array of arrays,
 * off of an array,
 * with a certain amount of maximum items
 * 
 * e.g. An Array with 16 items and maxItems 5
 * will be 3 arrays of 5 items and 1 array of 1 item
 * 
 * @param array $items
 * @param int $maxItems
 * 
 * @return array
 */

function BuildItemRows ($items, $maxItems=0) 
{
    $result = [];
    $currentRow = [];
    $currentItemCount = 0;
    $maxItems = intval($maxItems);

    if (empty($items) || $maxItems <= 0) return $items;

    foreach ($items as $key => $value)
    {
        $currentRow[$key] = $value;
        $currentItemCount++;

        if ($currentItemCount >= $maxItems) 
        {
            $result[] = $currentRow;
            $currentRow = [];
            $currentItemCount = 0;
        }

    }

    if (!empty($currentRow)) 
    {
        $result[] = $currentRow;
    }

    return $result;

}