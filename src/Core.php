<?php

/**
 * textovka v1
 * 
 * REST API text-based game
 */

 namespace textovka;

 class Core {
     
    public static $contents = [];
    public static $mysqli = NULL;

    # MySQL params
    const DB_USER = "textovka";
    const DB_PASS = "";
    const DB_SERVER = "localhost";
    const DB_NAME = "textovka_data";

    public static function init() {
        //$mysqli = new \mysqli(self::DB_USER, self::DB_PASS, self::DB_SERVER, self::DB_NAME);
        return new Core();
    }

    public function startGame() {
        Api::init()->writeJSON();
    }
 }