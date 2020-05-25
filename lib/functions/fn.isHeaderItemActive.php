<?php

namespace nvRexHelper;


/**
 * check if a header category/article ($id) is active
 * these items should be in the root directory
 * 
 * @param number $id
 * 
 * @return boolean
 */
function isHeaderItemActive($id)
{
    if (!$id || !intval($id)) return false;

    $currentId = \rex_article::getCurrentId();

    // check single articles
    if ($currentId == $id) return true;

    // check deeper articles
    $root = \rex_category::get(\nvDomainSettings::getValue("id"));
    $rootId = $root->getValue("id");
    $rootCategories = $root->getChildren();

    // find out which root category is the context
    $categoryId = 0;
    foreach ($rootCategories as $category)
    {
        if ($category->getValue("id") == $id) $categoryId = $id;
    }

    if (!$categoryId) return false;
    

    // loop from the current article to the top
    // and check the ids for equality
    if (!$article = \rex_article::get($currentId)) return false;
    
    while(true)
    {
        if (!$parent = $article->getParent()) return false;
        if ($parent->getValue("id") == $rootId) return false;
        if ($parent->getValue("id") == $categoryId) return true;

        $article = $parent;
    }

    return false;
}