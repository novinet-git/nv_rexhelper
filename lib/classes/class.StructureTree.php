<?php

namespace nvRexHelper;

class StructureTree {

    // -- static functions ---

    /**
    ** Handling Structure Tree with cache, since recursive tree is heavily compution
    */

    public static $cache = [];

    public static function factory(int $root_id=0, $filter=null): StructureTree {
        if ($filter) return new self($root_id, [], "", $filter);
        else if (!array_key_exists("$root_id", self::$cache)) {
            self::$cache["$root_id"] = new self($root_id, [], "", $filter);
        } 
        return self::$cache["$root_id"];
    }

    // -- class memebers and constructor ---

    private $tree = [];
    private $article_id_active = 0;
    private $active_path = "";
    private $filter = null;
    private $root_id = 0;

    public function __construct(int $root_id, array $tree, string $active_path, $filter=null) {
        $this->article_id_active = \rex_article::getCurrentId();
        $this->filter = $filter;
        $this->root_id = $root_id;

        if (!$tree) {
            if($root_id == 0) {
                $this->tree["$root_id"] = $this->get_structure_tree_root();
                $this->set_active_structure($this->tree, 0 . '|' . $this->active_path);
            } else {
                if (!$root_category = \rex_category::get($root_id)) throw new \rex_exception("given id $root_id is not a category");
                $this->tree["$root_id"] = $this->get_structure_tree_rec($root_category, 0);
                $this->set_active_structure($this->tree, $this->active_path);
            }
        } else if ($tree && $active_path) {
            $this->tree = $tree;
            $this->active_path = $active_path;
        }

    }

    // -- public functions ---

    /**
    ** get the tree array
    */

    public function get_array(): array  {
        return $this->tree;
    }

    /**
    ** returns the tree as a flat array of articles 
    */

    public function get_flat_array(): array {
        $flat = [];

        foreach($this->tree as $tree) {
            foreach($tree["articles"] as $article) {
                $flat[] = $article;
            }

            foreach($tree["categories"] as $category) {
                $this->build_flat_array_rec($flat, $category);
            }
        }

        return $flat;
    }

    /**
    ** get a part of the tree, we also store that part in the chache
    */

    public function get_sub_tree(int $root_id): StructureTree {
        if ($this->filter) {
            $tree = $this->get_sub_tree_rec($this->get_array(), $root_id); 
            if (!$tree) throw new \rex_exception("couldn't find subtree, please check your code");
            return new self($root_id, $tree, $this->active_path);
        }
        else if (!\array_key_exists("$root_id", self::$cache)) {
            $tree = $this->get_sub_tree_rec($this->get_array(), $root_id); 
            if (!$tree) throw new \rex_exception("couldn't find subtree, please check your code");
            self::$cache["$root_id"] = new self($root_id, $tree, $this->active_path);
        }
        
        return self::$cache["$root_id"];
    }

    /**
    ** add a filter afterwards, returns a new instance
    */

    public function filter($filter): StructureTree {
        $tree = $this->filter_tree_rec($this->tree, $filter);
        if(!$tree) throw new \rex_exception("something went wrong filtering the tree");
        return new self($this->root_id, $tree, $this->active_path, $filter);
    }

    /**
    ** reduce the tree structure, works kinda like trim whitespaces from the 
    ** start and the end
    */

    public function reduce() {
        $tree = $this->tree;
        
        $i = 0;
        while($this->are_all_top_level_categories_empty($tree)) {
            $tree = $this->remove_top_level($tree);
        }

        $this->tree = $this->remove_empty_branches_rec($tree);
    }

    /**
    ** combines the filter and reduce methods
    */

    public function filter_and_reduce($filer): StructureTree {
        $tree = $this->filter($filer);
        $tree->reduce();
        return $tree;
    }

    /**
    ** returns the current active path
    */

    public function active_path(): string {
        return $this->active_path;
    }
 
    // --- private functions ----

    private function are_all_top_level_categories_empty(array $categories): bool {
        if(!$categories) return false;
        foreach($categories as $category) {
            if ($category["articles"]) return false;
        }
        return true;
    }

    private function remove_top_level(array $categories): array {
        $second_level_categories = [];
      
        foreach($categories as $top_level_categories) {
            foreach($top_level_categories["categories"] as $key => $cat) {
                $second_level_categories[$key] = $cat;
            }
        }
        
        return $second_level_categories;
    }

    private function remove_empty_branches_rec(array $categories): array {
        $reduced_categories = [];
       
        foreach($categories as $key => $cat) { 
            if(!$this->is_branch_empty($cat)) {
                $reduced_categories[$key] = $cat;
            }
        }

        $categories = $reduced_categories;
        $reduced_categories = [];
        foreach($categories as $key => $cat) {
            if($cat["categories"]) {
                $cat["categories"] = $this->remove_empty_branches_rec($cat["categories"]);
            }
            $reduced_categories[$key] = $cat;
        }
       
        return $reduced_categories;
    }

    private function is_branch_empty(array $category): bool {
        if ($category["articles"]) return false;
        $is_empty = true;

        foreach ($category["categories"] as $key => $cat) {
            if (!$this->is_branch_empty($cat)) $is_empty = false;
        }
      
        return $is_empty;
    }

    private function filter_tree_rec($item, $filter) {
        foreach ($item as &$category) {
            $category["articles"] = array_filter($category["articles"], $filter);
            $category["categories"] = $this->filter_tree_rec($category["categories"], $filter);
        }
        return $item;
    }

    /**
    ** running through the tree recursivly
    */

    private function get_sub_tree_rec(array $categories, int $search): array {
        if (\array_key_exists($search, $categories)) return ["$search" => $categories[$search]];
        
        $sub_tree = [];

        foreach($categories as $category) {
            if ($category["categories"]) {
                if($item = $this->get_sub_tree_rec($category["categories"], $search)) {
                    $sub_tree = $item;
                }
            }
        }

        return $sub_tree;
    }

    /**
    ** running through the tree from the root level 
    */

    private function get_structure_tree_root(): array {
        $articles = [];
        $categories = [];

        foreach(\rex_article::getRootArticles() as $article) {
            $id = $article->getId();
            $active = false;

            if ($id == $this->article_id_active) {
                $active = true;
                $this->active_path = $article->getValue("path") . "$id|";
            }

            $articles["$id"] = [
                "active" => $active,
                "status" => $article->getValue("status") ? true : false,
                "depth" => 0,
                "data" => $article,
            ];
        }

        if (\is_callable($this->filter)) $articles = \array_filter($articles, $this->filter);


        foreach(\rex_category::getRootCategories() as $item) {
            $id = $item->getId();
            $categories["$id"] = $this->get_structure_tree_rec($item, 0);
        }
     
        return [
            "active" => false,
            "depth" => 0,
            "data" => null,
            "articles" => $articles,
            "categories" => $categories,
        ];
    }

    /**
    ** running through the tree recursivly
    */

    private function get_structure_tree_rec(\rex_category $category, int $depth): array {
        $categories_depth = $depth + 1;
        $articles = [];
        $categories = [];

        foreach ($category->getArticles() as $article) {
            $id = $article->getId();
            $active = false;

            if ($id == $this->article_id_active) {
                $active = true;
                $this->active_path = $article->getValue("path") . "$id|";
            }

            $articles["$id"] = [
                "active" => $active,
                "status" => $article->getValue("status") ? true : false,
                "depth" => $depth,
                "data" => $article,
            ];
        }

        if (\is_callable($this->filter)) $articles = \array_filter($articles, $this->filter);
    

        foreach($category->getChildren() as $item) {
            $id = $item->getId();
            $categories["$id"] = $this->get_structure_tree_rec($item, $categories_depth);
        }

        return [
            "active" => false,
            "depth" => $depth,
            "data" => $category,
            "articles" => $articles,
            "categories" => $categories,
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
            $tree = &$tree["categories"];
        }
    }

    /**
    * recursevly build a flat array of the articles
    */

    private function build_flat_array_rec(array &$flat, array $category) {
        foreach($category["articles"] as $article) {
            $flat[] = $article;
        }

        foreach($category["categories"] as $child) {
            $this->build_flat_array_rec($flat, $child);
        }
    }
}