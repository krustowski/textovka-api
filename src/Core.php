<?php

/**
 * textovka v1
 * 
 * REST API text-based game
 */

 namespace textovka;

 class Core {
     
    public static $contents = [];

    public static function init() {
        return new Core();
    }

    public function startGame() {
        Api::init()->writeJSON();
    }
 }