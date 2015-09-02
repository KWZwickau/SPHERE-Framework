<?php

class Singleton
{

    private static $uniqueInstance = null;

    protected function __construct()
    {
    }

    public static function getInstance()
    {

        if (self::$uniqueInstance === null) {
            self::$uniqueInstance = new Singleton;
        }

        return self::$uniqueInstance;
    }

    private final function __clone()
    {
    }
}
