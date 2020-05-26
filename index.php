<?php

/**
 * textovka v1
 *
 * PHP REST API text-based game engine
 */

namespace textovka;

// load all classes
foreach (glob("src/*.php") as $filename) {
    include_once $filename;
}

new Game();
