<?php

namespace nvRexHelper;

class StructureTree {

    /**
    ** Handling Structure Tree with cache, since recursive tree is heavily compution
    */

    public static $cache = [];

    public static function factory(int $root_id): StructureTree {
        if (!array_key_exists("$root_id", self::$cache)) {
            self::$cache["$root_id"] = new self($root_id);
        } 
        return self::$cache["$root_id"];
    }

    private $tree = [];
    private $root_category = null;
    private $root_id = null;
    private $article_id_active = 0;
    private $active_path = "";

    public function __construct(int $root_id) {
        $this->root_id = $root_id;
        $this->article_id_active = \rex_article::getCurrentId();
        if (!$this->root_category = \rex_category::get($root_id)) throw new \rex_exception("given id $root_id is not a category");
        $this->tree["$root_id"] = $this->get_structure_tree_rec($this->root_category, 0);
        $this->set_active_structure($this->tree, $this->active_path);
    }

    public function get_tree(): array {
        return $this->tree;
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
            "articles" => $articles,
            "children" => $children,
        ];
    }

    /**
    ** set the active path
    */

    private function set_active_structure(array &$tree, string $active_path) {
        dump($active_path);

        $iterator = explode("|", $active_path);
        $iterator = array_filter($iterator, function($item) {
            return $item != "";
        });
      
        while($iterator) {
            $key = array_shift($iterator);     
            if (!\array_key_exists($key, $tree)) break;
            $tree = &$tree[$key];
            $tree["active"] = true; 
            $tree = &$tree["children"];
        }
    }
}