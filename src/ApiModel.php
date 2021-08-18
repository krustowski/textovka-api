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

class ApiModel
{
    // put JSON to player - exit function
    private function writeJSON($code = 200)
    {
        // logging
        file_put_contents($this->log_file, (int) $this->timestamp . " / " . ($this->nickname ?? "none") . " / " . ($this->action ?? "none") . " / " . $this->remote_ip . "\n", FILE_APPEND);

        // player data
        $player_data = [
            "nickname" => $this->nickname,
            "hp" => $this->hp,
            "room" => $this->room,
            "inventary" => $this->inventary,
            "time_elapsed" => !is_null($this->time_ended) ? ((int) $this->time_ended - (int) $this->time_registered) : ((int) $this->timestamp - (int) $this->time_registered),
            "game_ended" => $this->game_ended,
        ];

        // room data
        $room_data = [
            "items" => $this->map["room"][$this->room]["items"] ?? null,
            "objects" => $this->map["room"][$this->room]["objects"] ?? null,
            "actions" => $this->map["room"][$this->room]["actions"] ?? null,
        ];

        // JSON output
        $json_output = [
            "api" => [
                "name" => $this->apiname,
                "version" => $this->version,
                "engine_build" => $this->engine_build ?? null,
                "exec_time_in_ms" => round((microtime(true) - $this->timestamp) * 1000, 2),
                "status_code" => $code,
                "action" => $this->action,
                "apikey" => $this->apikey,
                "timestamp" => (int) $this->timestamp,
            ],
            "player" => (!is_null($this->room) ? $player_data : []),
            "room" => (!is_null($this->room) ? $room_data : []),
            "message" => $this->message ?? $this->map["room"][$this->room]["description"]
        ];

        // put JSON data
        //http_response_code($code);
        header('Content-type: application/json');
        echo json_encode($json_output, JSON_PRETTY_PRINT);
        exit();
    }   
}