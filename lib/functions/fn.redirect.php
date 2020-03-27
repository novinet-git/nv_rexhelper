<?php 

namespace nvRexHelper;

function redirect($context, $clangId=null) {
    if($id = intval($context)) {
		$clangId = $clangId ?: \rex_clang::getCurrentId();
		$url = \rex_getUrl($id, $clangId);
	} else {
		$url = $context;
	}
	
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: $url");
	header("Connection: close");
	exit();
}