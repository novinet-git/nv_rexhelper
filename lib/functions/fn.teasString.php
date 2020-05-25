<?php

namespace nvRexHelper;

/**
 * short a string to have max $limit characters
 * 
 * @param string $string
 * @param int $limit
 * 
 * @return string
 */

function teasString ($string = "", $limit = 0) 
{

	/**
	 * @var string $result
	 */

	$result = '';

	if (!$string || !$limit) return $string;
	if (strlen($string) <= $limit) return $string;

	$result = substr($string, 0, $limit - 1);
	$result .= "...";

	return $result;
}
