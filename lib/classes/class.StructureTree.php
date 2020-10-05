<?php

namespace nvRexHelper;

class StructureTree {

    /**
    ** Handling Structure Tree with cache, since recursive tree is heavily compution
    */

    public static $cache = [];

    public static function factory(int $root_id): StructureTree {
        if (!array_key_exists("$root_id", self::$cache)) {
            self::$cache["$root_id"] = new self($root_id, [], "");
        } 
        return self::$cache["$root_id"];
    }

    private $tree = [];
    private $article_id_active = 0;
    private $active_path = "";

    public function __construct(int $root_id, array $tree, string $active_path) {
        $this->article_id_active = \rex_article::getCurrentId();

        if (!$tree) {
            if (!$root_category = \rex_category::get($root_id)) throw new \rex_exception("given id $root_id is not a category");
            $this->tree["$root_id"] = $this->get_structure_tree_rec($root_category, 0);
            $this->set_active_structure($this->tree, $this->active_path);
        } else if ($tree && $active_path) {
            $this->tree = $tree;
            $this->active_path = $active_path;
        }

    }

    /**
    ** get the tree array
    */

    public function get_array(): array  {
        return $this->tree;
    }

    /**
    ** get a part of the tree, we also store that part in the chache
    */

    public function get_sub_tree(int $root_id): StructureTree {
        if (!\array_key_exists("$root_id", self::$cache)) {
            $tree = $this->get_sub_tree_rec($this->get_tree(), $root_id); 
            if (!$tree) throw new \rex_exeption("couldn't find subtree, please check your code");
            self::$cache["$root_id"] = new self($root_id, $tree, $this->active_path);
        }

        return self::$cache["$root_id"];
    }

    private function get_sub_tree_rec(array $item, int $search): array {
        if (\array_key_exists($search, $item)) return ["$search" => $item[$search]];
        
        foreach($item as $child) {
            if ($child["children"]) {
                return $this->get_sub_tree_rec($child["children"], $search);
            }
        }

        return [];
    }

    /**
    ** running through the tree recursivly
    */

    private function get_structure_tree_rec(\rex_category $category, int $depth): array {
        $children_depth = $depth + 1;
        $articles = [];
        $children = [];

        foreach ($category->getArticles() as $child) {
            $id = $child->getId();
            $active = false;

            if ($id == $this->article_id_active) {
                $active = true;
                $this->active_path = $child->getValue("path") . "$id|";
            }

            $articles["$id"] = [
                "active" => $active,
                "depth" => $depth,
                "article" => $child,
            ];
        }

        foreach($category->getChildren() as $child) {
            $id = $child->getId();
            $children["$id"] = $this->get_structure_tree_rec($child, $children_depth);
        }

        return [
            "active" => false,
            "depth" => $depth,
            "category" => $category,
            "articles" => $articles,
            "children" => $children,
        ];
    }

    /**
    ** set the active path
    */

    private function set_active_structure(array &$tree, string $active_path) {
        $iterator = array_filter(explode("|", $active_path), function($item) { return $item != ""; });
      
        while($iterator) {
            $key = array_shift($iterator);     
            if (!\array_key_exists($key, $tree)) break;
            $tree = &$tree[$key];
            $tree["active"] = true; 
            $tree = &$tree["children"];
        }
    }
}