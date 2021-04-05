<?php

/**
 * textovka v1
 *
 * PHP REST API text-based game engine
 */

namespace textovka;

defined("ROOT_DIR") || define("ROOT_DIR", __DIR__ . "/..");

// load all classes
foreach (glob(ROOT_DIR . "/src/*.php") as $filename) {
    include_once $filename;
}

new Game();
