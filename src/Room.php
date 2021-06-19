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

class Room 
{
    private array $neighborhood = [
        "north" => null,
        "south" => null,
        "easy" => null,
        "west" => null
    ];
}