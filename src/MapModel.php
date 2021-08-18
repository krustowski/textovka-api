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

namespace Textovka;

class MapModel 
{
    public function __construct(
        /**
         * @OA\Property(
         *     format="string",
         *     description="map name",
         *     title="name",
         * )
         *
         * @var string
         */
        // x("DemoMap.json") => "Demo"
        private string $name = "",

        /**
         * @OA\Property(
         *     format="array",
         *     description="map room list",
         *     title="room_list",
         * )
         *
         * @var array
         */
        private array $room_list = [],

        /**
         * @OA\Property(
         *     format="array",
         *     description="map rooms",
         *     title="rooms",
         * )
         *
         * @var array|object
         */
        private array|RoomModel $rooms = [],
        
        /**
         * @OA\Property(
         *     format="string",
         *     description="map entry room",
         *     title="entry_room_name",
         * )
         *
         * @var string
         */
        private string $entry_room_name = "",

        /**
         * @OA\Property(
         *     format="string",
         *     description="map exit room",
         *     title="exit_room_name",
         * )
         *
         * @var string
         */
        private string $exit_room_name = "",
    )
    {
        return $this;
    }

 }