<?php

namespace nvRexHelper;

class PictureTag {
    public static function generate($media, $attributes=[]) {
        $attr = "";
        foreach($attributes as $key => $value) {
            $attr .= $key .'="'. $value .'" '; 
        }

        $return = '<picture>';

        $srcSM = MEDIA . 'max_width_sm/' . $media;
        $srcLG = MEDIA . 'max_width_lg/' . $media;
        $src = MEDIA . $media; 
       
        $return .= '<source media="(max-width: 575px)" srcset="'.$srcSM.'">';
        $return .= '<source media="(max-width: 991px)" srcset="'.$srcLG.'">';
        $return .= '<img '.$attr.' src="'.$src.'">';

        return $return . '</picture>';
    }

    public static function generateBackgroundTag($media, $class) {
        $return = "<style scoped>";
        $return .= "." . $class . "{background-image: url('" . MEDIA . $media . "');}";
        $return .= "@media (max-width: 991px) {.".$class."{background-image: url('" . MEDIA . "max_width_lg/" . $media . "');}}";
        $return .= "@media (max-width: 575px) {.".$class."{background-image: url('" . MEDIA . "max_width_sm/" . $media . "');}}";
        return $return . "</style>";
    } 
}