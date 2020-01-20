<?php

namespace nvRexHelper;

class Spam {
    public static function validateTimer($label, $microtime, $seconds) {
        if (($microtime + $seconds) > microtime(true)) {
            return true;
        } else {
            return false;
        }
    }

    public static function addSpamProtection($yform) {
        $yform->setValueField('text', array('timer_validate','Spamschutz',microtime(true),'1','{"type":"hidden"}'));
        $yform->setValidateField('customfunction', array("timer_validate","yform_validate_timer","5","Spambots haben keine Chance","0"));
    }
}

