<?php

namespace DocSysTools;

class DocSysTools
{
    // @TODO: move/implement CLI calls to binaries in this class...(?)

    public static function init()
    {
        require dirname(__DIR__) . '/bootstrap.php';
    }

    function __construct()
    {
        self::init();
    }
}