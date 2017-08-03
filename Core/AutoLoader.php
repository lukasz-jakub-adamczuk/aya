<?php

class AutoLoader {

    private $prefix;

    private $directory;

    private $valid = false;

    public function __construct($prefix, $directory) {
        $this->prefix = (string)$prefix;
        // TODO valid dir
        $this->directory = $directory;
        $this->valid = true;
    }

    public function autoload($className) {
        // if (strpos($className, $this->prefix) !== 0) {
        //     return false;
        // }
        echo 'require('.$this->directory.$className.'.php);';

        require $this->directory.$className.'.php';
        return true;
    }

    public function register() {
        if ($this->valid) {
            spl_autoload_register([$this, 'autoload'], true, true);
            $this->valid = false;
        }
    }
}