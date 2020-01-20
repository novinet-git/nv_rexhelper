<?php
namespace nvRexHelper;

class Spam {
    public static function addSpamProtection($yform) {
        $yform->setValueField('text', ['timer_validate','Spamschutz',microtime(true),'1','{"type":"hidden"}']);
        $yform->setValidateField('customfunction', ["timer_validate","nvRexHelperValidateTimer","5","Spambots haben keine Chance","0"]);
    }
}
