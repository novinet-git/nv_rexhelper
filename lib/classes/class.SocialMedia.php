<?php

namespace nvRexHelper;

class SocialMedia 
{
    /**
     * get the meta tags for social media, e.g. the meta image, title for facebook and twitter
     * 
     * @param UrlSeo $urlSeo 
     * @param string $image optional, please pass the full url
     * 
     * @return string
     */
    public static function getMetaTags ($urlSeo, $image=null) 
    {
        $content = '';

        if ($title = $urlSeo->getTitle()) 
        {
            $content .= '<meta property="og:title" content="' . $title . '">' . PHP_EOL;
            $content .= '<meta property="og:site_name" content="' . $title . '">' . PHP_EOL;
        }

        if ($description = $urlSeo->getDescription()) 
        {
            $content .= '<meta property="og:description" content="' . $description . '">' . PHP_EOL;
        }

        if ($image) 
        {
            $content .= '<meta property="og:image" content="' . $image . '">' . PHP_EOL;
        }
        
        $content .= '<meta property="og:url" content="'. FE . \rex_getUrl(\rex_article::getCurrentId()) . '">' . PHP_EOL;

        return $content;
       
    }
}