<?php

class String {

    // public function getName($name, $case = 'caps') {
    //     // $var = '_s'.ucfirst($name).'Name';
    //     $var = '_'.$name.'Name';
    //     $val = $this->$var;
    //     if ($case == 'caps') {
    //         $var = '_'.$name.'Name';
    //         $val = $this->$var;
    //         return str_replace(' ', '', ucwords(str_replace('-', ' ', $val)));
    //     } elseif ($case == 'lower') {
    //         return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $this->$var));
    //     } else {
    //         return $this->$val;
    //     }
    // }
    public static function toCamelCase($text) {
        // return $text;
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $text)));
    }
}