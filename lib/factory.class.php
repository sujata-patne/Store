<?php
namespace Store\Factory;


class Factory {
    public static function build($class,$param = '') {
        if (class_exists($class)) {
            return new $class($param);
        }
        else {
            throw new Exception("Invalid class name given.");
        }
    }
}