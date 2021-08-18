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

class PlayerModel
{

    public function __construct(
        /**
         * @OA\Property(
         *     format="string",
         *     description="player's name",
         *     title="name",
         * )
         *
         * @var string
         */
        private string $token,

        /**
         * @OA\Property(
         *     format="string",
         *     description="player's name",
         *     title="name",
         * )
         *
         * @var string
         */
        private string $name = "",

        /**
         * @OA\Property(
         *     format="int|float",
         *     description="player's hit points (HP) -- health level",
         *     title="hp",
         * )
         *
         * @var int|float
         */
        private int|float $hp = 100,

        /**
         * @OA\Property(
         *     format="string,
         *     description="player's position -- room label",
         *     title="room_name",
         * )
         *
         * @var string
         */
        private string $room_name = "",

        /**
         * @OA\Property(
         *     format="string|object",
         *     description="player's map or its name/label",
         *     title="map",
         * )
         *
         * @var string|object MapModel
         */
        private string|MapModel $map = "",

        /**
         * @OA\Property(
         *     format="array|object",
         *     description="player's inventary",
         *     title="inventary",
         * )
         *
         * @var array|object ItemModel
         */
        private array|ItemModel $inventory = [],

        /**
         * @OA\Property(
         *     format="int|float",
         *     description="player's registration timestamp",
         *     title="time_registered",
         * )
         *
         * @var int|float
         */
        private int|float $time_registered = 0,

        /**
         * @OA\Property(
         *     format="int|float",
         *     description="player's last activity timestamp",
         *     title="time_last_activity",
         * )
         *
         * @var int|float
         */
        private int|float $time_last_activity = 0,

        /**
         * @OA\Property(
         *     format="int|float",
         *     description="player's game-over timestamp",
         *     title="time_finished",
         * )
         *
         * @var int|float
         */
        private int|float $time_finished = 0,

        /**
         * @OA\Property(
         *     format="boolean",
         *     description="player's registration timestamp",
         *     title="time_registered",
         * )
         *
         * @var boolean
         */
        private bool $game_over = false,
    )
    {
        $this->load(token: $token);
    }

    private function load(string $token) 
    {
        // load player data
        $data = json_decode(
            json: file_get_contents(filename: PLAYER_DATA_DIR . "/" . $token . ".json"), 
            associative: true
        );

        // fill Player class properties
        foreach($data as $property => $value) {
            $this->set(
                property: $property, 
                value: match ($property) {
                    // mini hack for object properties
                    "map" => new MapModel(name: $value),
                    "room" => new RoomModel(name: $value),
                    "inventory" => new InventoryModel(importedItems: $value),
                    default => $value
                }
            );
        }

        return $this;
    }

    public function get(string $property) 
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function set(string $property, mixed $value) 
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }

        return $this;
    }

    public function __destruct()
    {        
        foreach(get_object_vars($this) as $property => $value) {
            // get objects property ID if value is object 
            // MapModel (name), RoomModel (name/label refs to MapModel rooms), InventoryModel (list of items)
            $value = $value?->name ?? $value?->exportItems() ?? $value;
            $data[$property] = $value;
        }

        // save player data
        file_put_contents(filename: PLAYER_DATA_DIR . "/" . $this->token . ".json", data: json_encode($data));
    }

}