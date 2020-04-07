<?php

namespace nvRexHelper;

/**
 * get a pre tag with information for debuggin purposes
 * 
 * @param any $value
 * @param bool $exit (default is false) -> true to exit after pre
 */

function pre($value, $exit = false){

	$aCaller = debug_backtrace (DEBUG_BACKTRACE_IGNORE_ARGS, 2 );

	echo '<pre style="position:relative; z-index:2000000; background:#fff">';

	if($aCaller && $aCaller[0]){
		if(stristr($aCaller[0]['file'], 'fn.old.functions.php')){
			echo $aCaller[1]['file'] . ' => ';
		} else {
			echo $aCaller[0]['file'] . ' => ';
		}
	}
	print_r($value);
	echo '</pre>';

	if($exit){
		exit();
	}
	
}