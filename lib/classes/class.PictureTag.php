<?php

namespace nvRexHelper;

class PictureTag {
    public static function generate($media) {
        $return = '<picture>';
        $srcSM = MEDIA . 'max_width_sm/' . $media;
        $srcLG = MEDIA . 'max_width_lg/' . $media;
        $src = MEDIA . $media; 
       
        $return .= '<source media="(max-width: 575px)" srcset="'.$srcSM.'">';
        $return .= '<source media="(max-width: 991px)" srcset="'.$srcLG.'">';
        $return .= '<img src="'.$src.'">';

        return $return . '</picture>';
    }
}