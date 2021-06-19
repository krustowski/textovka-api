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

class Map 
{
    /**
     * @OA\Property(
     *     format="string",
     *     description="map name",
     *     title="name",
     * )
     *
     * @var string
     */
    private string $name;
    
    /**
     * @OA\Property(
     *     format="string",
     *     description="map entry room",
     *     title="entry_room",
     * )
     *
     * @var string
     */
    private string $entry_room;

    /**
     * @OA\Property(
     *     format="string",
     *     description="map exit room",
     *     title="exit_room",
     * )
     *
     * @var string
     */
    private string $exit_room;
 }