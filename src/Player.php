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
 *      version="1.1",
 *      @OA\Contact(
 *          name="krustowski",
 *          email="textovka@n0p.cz"
 *      )
 * )
 */

namespace textovka;

class Player 
{

    /**
     * @OA\Property(
     *     format="string",
     *     description="player's name",
     *     title="nickname",
     * )
     *
     * @var string
     */
    private string $nickname;

    /**
     * @OA\Property(
     *     format="int64",
     *     description="player's hit points (HP) -- health level",
     *     title="hp",
     * )
     *
     * @var integer
     */
    private int $hp = 100;

    /**
     * @OA\Property(
     *     format="string",
     *     description="player's position -- room ID",
     *     title="room_id",
     * )
     *
     * @var string
     */
    private string $room_id;

    /**
     * @OA\Property(
     *     format="object",
     *     description="player's map",
     *     title="map",
     * )
     *
     * @var object
     */
    private Map $map;

    /**
     * @OA\Property(
     *     format="object",
     *     description="player's inventary",
     *     title="inventary",
     * )
     *
     * @var object
     */
    private Inventary $inventary;

    /**
     * @OA\Property(
     *     format="int64",
     *     description="player's registration timestamp",
     *     title="time_registered",
     * )
     *
     * @var string
     */
    private int $time_registered = 0;

    /**
     * @OA\Property(
     *     format="int64",
     *     description="player's game-over timestamp",
     *     title="time_ended",
     * )
     *
     * @var string
     */
    private int $time_ended = 0;

    public function __construct(string $apikey)
    {
        
    }

}