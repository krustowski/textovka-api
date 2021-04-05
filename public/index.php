<?php

/**
 * textovka v1
 *
 * PHP REST API text-based game engine
 */

namespace textovka;

defined("ROOT_DIR") || define("ROOT_DIR", __DIR__ . "/..");

// composer load
require ROOT_DIR . "/vendor/autoload.php";

new Game();
