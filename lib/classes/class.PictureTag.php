<?php

namespace nvRexHelper;

class PictureTag 
{

    /**
     * generate a picture tag to a given media
     * 
     * @param string $media
     * @param array $attributes e.g. ["class" => "nv-image"]
     * 
     * @return string
     */

    public static function generate($media, $attributes=[]) 
    {
        $attr = "";
        foreach($attributes as $key => $value) 
        {
            $attr .= $key .'="'. $value .'" '; 
        }

        $media_object = \rex_media::get($media);

        $description = "";

        if ($media_object)
        {
            $description = $media_object->getValue("med_description");
        }
    
        $return = '<picture>';

        $srcSM = MEDIA . 'max_width_sm/' . $media;
        $srcLG = MEDIA . 'max_width_lg/' . $media;
        $src = MEDIA . $media; 
       
        $return .= '<source media="(max-width: 575px)" srcset="'.$srcSM.'">';
        $return .= '<source media="(max-width: 991px)" srcset="'.$srcLG.'">';

        if ($description)
        {
            $return .= '<img '.$attr.' src="'.$src.'" alt="' . $description . '">';
        }
        else 
        {
            $return .= '<img '.$attr.' src="'.$src.'">';
        }

      

        return $return . '</picture>';
    }

    /**
     * generate a picture tag for lazy load https://github.com/verlok/lazyload
     * 
     * @param string $media
     * @param array $attributes e.g. ["class" => "nv-image"]
     * 
     * @return string
     */

    public static function generateLazy($media, $attributes=[]) 
    {

        if (!isset($attributes["class"])) 
        {
            $attributes["class"] = "lazy";
        } 
        else 
        {
            $attributes["class"] .= " lazy";
        }

        $description = "";

        $media_object = \rex_media::get($media);

        if ($media_object)
        {
            $description = $media_object->getValue("med_description");
        }

        $attr = "";
        foreach($attributes as $key => $value) 
        {
            $attr .= $key .'="'. $value .'" '; 
        }

        $return = '<picture>';

        $srcSM = MEDIA . 'max_width_sm/' . $media;
        $srcLG = MEDIA . 'max_width_lg/' . $media;
        $src = MEDIA . $media; 
       
        $return .= '<source media="(max-width: 575px)" data-srcset="'.$srcSM.'">';
        $return .= '<source media="(max-width: 991px)" data-srcset="'.$srcLG.'">';

        if ($description)
        {
            $return .= '<img '.$attr.' data-src="'.$src.'" alt="' . $description . '">';
        }
        else 
        {
            $return .= '<img '.$attr.' data-src="'.$src.'">';
        }
        

        return $return . '</picture>';
    }

    /**
     * generate a background tag for a given media
     * 
     * @param string $media
     * @param string $selector e.g. #img-123
     * 
     * @return string
     */

    public static function generateBackgroundTag($media, $selector) 
    {
        $return = "<style scoped>";
        $return .= $selector . "{background-image: url('" . MEDIA . $media . "');}";
        $return .= "@media (max-width: 991px) {".$selector."{background-image: url('" . MEDIA . "max_width_lg/" . $media . "');}}";
        $return .= "@media (max-width: 575px) {".$selector."{background-image: url('" . MEDIA . "max_width_sm/" . $media . "');}}";
        return $return . "</style>";
    }
}