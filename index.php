<?php

/**
 * textovka v1
 * 
 * REST API text-based game
 */

namespace textovka;

foreach (glob("src/*.php") as $filename) {
    include_once $filename;
}

Core::init()->startGame();