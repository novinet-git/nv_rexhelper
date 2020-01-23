<?php

namespace nvRexHelper;

class SrcSet {
    public static $LOOKUP = [
        ["Smartphone", 576], 
        ["Tablet", 991],
        ["Desktop", 0]
    ];
    

    public static function getInput($mform, $ids, $label) {
        $mform->addFieldset($label);
        
        for($i = 0; $i < count($ids); $i++) {
            $lookup = self::$LOOKUP[$i];
            $label = $lookup[0];
            $label .= $lookup[1] == 0 ? ' (maximale Breite)' : ' (bis ' . $lookup[1] .'px)';
            $mform->addMediaField($ids[$i], [$label]);

        }
    }

    public static function getOutput($data, $ids) {
        $return = '<picture>';
        for($i = 0; $i < count($ids); $i++) {
            $lookup = self::$LOOKUP[$i];
            $src = MEDIA . $data['REX_MEDIA_'.$ids[$i]];

            if($lookup[1] == 0) {
                $return .= '<img src="' . $src . '">';
            }  else {
                $width = $lookup[1];
                $return .= '<source media="(max-width: '.$width.'px)" srcset="'.$src.'">';
            }
           

            
            
        }
       
           
        return $return . '</picture>';
    }
}