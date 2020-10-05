<?php

namespace nvRexHelper;

class StructureTree {

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

    public function __construct(int $root_id) {
        $this->root_id = $root_id;
        if (!$this->root_category = \rex_category::get($root_id)) throw new \rex_exception("given id $root_id is not a category");
        $this->tree = $this->get_structure_tree_rec($this->root_category, 0);
    }

    public function get_tree(): array {
        return $this->tree;
    }

    private function get_structure_tree_rec(\rex_category $category, int $depth): array {
        $children_depth = $depth + 1;

        return [
            "depth" => $depth,
            "articles" => $category->getArticles(),
            "children" => array_map(function($child) use ($children_depth) {
                return $this->get_structure_tree_rec($child, $children_depth);
            }, $category->getChildren()),
        ];
    }

}