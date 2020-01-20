<?php
# Redaxo needs this function to be global <-
function nvRexHelperValidateTimer($label, $microtime, $seconds) {
    if (($microtime + $seconds) > microtime(true)) {
        return true;
    } else {
        return false;
    }
}
# ->