<?php 

namespace nvRexHelper;

function redirect($articleId, $clangId=null) {
    $clangId = $clangId ?: \rex_clang::getCurrentId();
    $url = \rex_getUrl($articleId, $clangId);
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $url");
    header("Connection: close");
    exit();
}