<?php

namespace dokuwiki\plugin\bibleverse;

use FFI\Exception;

class Utilities {
    static function to_array_field($string, $delimiter = "-", $max) {

        $start_stop = explode($delimiter, $string);
        
        $start = intval($start_stop[0]);
        $end = intval(end($start_stop));

        if($start > $end) {
            throw new Exception("Error: Start must not be bigger than end");
        }

        $array_field = [];
        for($i = $start; $i < $end + 1; $i++) {
            array_push($array_field, $i);
        }

        return $array_field;
    }

    public static function get_child_dirs($namespace) {
        global $conf;
        $data = [];
        $opts= [
            "level" => 1,
            "listdirs" => true,
            "listfiles" => true
        ];
        search($data, $conf['datadir'], 'search_universal', $opts, str_replace(":", "/", $namespace));
        
        return $data;
    }

    public static function get_child_pages($namespace) {
        global $conf;
        $data = [];
        
        search($data, $conf['datadir'], 'search_list', $opts, str_replace(":", "/", $namespace), 1);
        
        return $data;
    }

    public static function get_child_page_ids($namespace) {
        $ids = [];
        $pages = self::get_child_pages($namespace);
        foreach($pages as $item) {         
            array_push($ids, end(explode(":", $item['id'])));   
        }
        return $ids;
    }

    public static function get_child_dir_ids($namespace) {
        $ids = [];
        $pages = self::get_child_dirs($namespace);
        foreach($pages as $item) {         
            array_push($ids, end(explode(":", $item['id'])));   
        }
        return $ids;
    }

    public static function get_page_id($page) {
        return end(explode(":", $page));
    }

}