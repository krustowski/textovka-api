<?php

/**
 * textovka v1
 * 
 * REST API text-based game
 */

namespace textovka;

// load all classes
foreach (glob("src/*.php") as $filename) {
    include_once $filename;
}

new Game();