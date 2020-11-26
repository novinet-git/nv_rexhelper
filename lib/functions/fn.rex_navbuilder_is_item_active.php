<?php
namespace nvRexHelper;

/**
  * run through a rex_navbuilder structure recursivly
  * returns true if one child/ or child of 
  * child hast active = true
*/
function rex_navbuilder_is_item_active(array $item): bool {
    if(isset($item["active"]) && $item["active"]) return true;
    
    if(isset($item["children"]) && is_array($item["children"])) {
        foreach($item["children"] as $child) {
            if(rex_navbuilder_is_item_active($child)) return true;
        }
    }

    return false;
}