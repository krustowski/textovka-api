<?php

/**
 * textovka v1
 * 
 * @author krustowski <textovka@n0p.cz>
 * @license MIT
 *
 * @OA\Info(
 *      title="textovka REST API", 
 *      description="PHP REST API text-based game engine",
 *      version="1.3",
 *      @OA\Contact(
 *          name="krustowski",
 *          email="textovka@n0p.cz"
 *      )
 * )
 */

namespace textovka;

defined("ROOT_DIR") || define("ROOT_DIR", __DIR__ . "/..");

// composer load
require ROOT_DIR . "/vendor/autoload.php";

new Game();
