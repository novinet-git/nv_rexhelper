<?php

namespace nvRexHelper;

/** 
 * build an array with the length of $colsAmount,
 * the $items should be distributed 
 * to all containers in an equal amount
 * 
 * eg. buildItemCols( ["banane", "apfel", "birne", "pizza", "toast"], 2 );  => [["banane", "apfel", "birne"], ["pizza", "toast"]] 
 * 
 * @param array $items , the length should be greater than 1
 * @param int $colsAmount , should be greater than 1
 * 
 * @return array
 */
function BuildItemCols($items, $colsAmount) {

    $result = [];
    $colsAmount = intval($colsAmount);
    $length = count($items);
    $oneCol = [$items];

    // check if parameters are valid
    if (empty($items) || $length <= 1) return $oneCol;
    if (!$colsAmount || $colsAmount <= 1) return $oneCol;
    if ($length < $colsAmount) return $oneCol;

    // calc the equal amount of the items for $colsAmount
    $div = $length / $colsAmount;
    $leftOver = $length % $colsAmount;
    $itemsAmount = floor($div);

    // build
    for ($i = 0; $i < $colsAmount; $i++):
        
        $innerResult = [];

        for ($j = $itemsAmount; $j >= 1; $j--) {

            $innerResult[] = array_shift($items);

        }

        if ($leftOver > 0) {
            $leftOver--;
            $innerResult[] = array_shift($items);
        }

        $result[] = $innerResult;

    endfor;

    return $result;
 
}