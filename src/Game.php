<?php

/**
 * textovka v1
 *
 * PHP REST API text-based game engine
 */

namespace textovka;

class Game
{
    // API attributes
    private $apiname = "textovka REST API";
    private $version = "v1";
    private $engine_build;
    private $message;
    private $log_file = __DIR__ . "/../game.log";
    private $timestamp;
    private $remote_ip;
    private $apikey = null;

    // user attributes
    private $nickname = null;
    private $hp = 100;
    private $room = null;
    private $exit_room = null;
    private $inventary = [];
    private $map = null;
    private $map_name = null;
    private $time_registered;
    private $time_ended = null;
    private $action;
    private $game_ended = false; // won game
    private $game_over; // TODO: implement this

    // hashing
    const PEPPER = "e7748b05b6d25adfd32e6b9ba81c44cf2012981278aea32cc22a5ae0ee8e2008";

    // init function
    public function __construct()
    {
        // init
        $this->timestamp = microtime(true);
        $this->remote_ip = $_SERVER["REMOTE_ADDR"] ?? null;
        $this->engine_build = md5_file(__FILE__) ?? null;

        // process URI queries
        $this->processGets();
    }

    // process URI queries (GET)
    private function processGets()
    {
        // XSS
        $gets = array_map("htmlspecialchars", $_GET);

        // register API call
        if (isset($gets["register"]) && !empty($gets["register"])) {
            $this->nickname = $gets["register"];
            $this->generateKey();
        }

        // blank API call
        if (!isset($gets["apikey"])) {
            $this->message = "No API key (apikey) passed. You can get one by performing a registration by appending 'register=user_name' where 'user_name' is your nickname.";
            $this->writeJSON(401);
        }

        // try that API key then
        $this->apikey = $gets["apikey"];

        // wrong API key, data file (user) does not exist
        if (!file_exists(__DIR__ . "/../data/" . $this->apikey . ".json")) {
            $this->apikey = null;
            $this->message = "Unknown API key (apikey) given.";
            $this->writeJSON(403);
        }

        // load player data
        $this->getUserData();

        // try given action
        if (isset($gets["action"]) && !empty($gets["action"])) {
            $this->action = $gets["action"];
            $this->processAction();
        }

        // print room message (description)
        $this->message = $this->map["room"][$this->room]["description"];

        // end of processing -> end of algorithm
        $this->writeJSON();
    }

    // generate new API key - new register
    private function generateKey()
    {
        $new_hash = null;

        // generate new hash until a new one can be used
        while (true) {
            $new_hash = hash("sha256", self::PEPPER . $this->nickname . $this->engine_build);

            if (!file_exists(__DIR__ . "/../data/" . $new_hash . ".json")) {
                break;
            } else {
                $json_data = json_decode(file_get_contents(__DIR__ . "/../data/" . $new_hash . ".json"), true);

                // user is already registered (has their file with such nickname)
                if ($json_data["nickname"] == $this->nickname) {
                    $this->message = "User '" . $this->nickname . "' already exists.";
                    $this->writeJSON(403);
                }

                break;
            }
        }

        // maps
        $maps = scandir(__DIR__ . "/../maps/");
        $maps_count = count($maps);

        // try to load the world map
        $rand_map_num = ($maps_count > 2) ? rand(2, --$maps_count) : null;
        $init_map = ($rand_map_num && $maps[$rand_map_num]) ? json_decode(file_get_contents(__DIR__ . "/../maps/" . $maps[$rand_map_num]), true) : json_decode(file_get_contents(__DIR__ . "/../maps/demo.json"), true);

        $this->map_name = ($rand_map_num && $maps[$rand_map_num]) ? $maps[$rand_map_num] : "demo.json";

        // invalid map file
        if (is_null($init_map)) {
            $this->message = "Internal game error: invalid map (invalid JSON file)";
            $this->writeJSON(500);
        }

        // player data to be written
        $json_data = [
            "nickname" => $this->nickname,
            "hp" => 100,
            "inventary" => [],
            "room" => $init_map["start_room"],
            "exit_room" => $init_map["exit_room"],
            "game_ended" => false,
            "time_registered" => (int) $this->timestamp,
            "time_ended" => null,
            "map_name" => $this->map_name,
            "map" => $init_map,
        ];

        // write data to a new file
        file_put_contents(__DIR__ . "/../data/" . $new_hash . ".json", json_encode($json_data));

        // JSON output
        $this->apikey = $new_hash;
        $this->action = "register";
        $this->message = "New API key (apikey) for '" . $this->nickname . "' generated.";
        $this->writeJSON();
    }

    // load player data
    private function getUserData()
    {
        $data = json_decode(file_get_contents(__DIR__ . "/../data/" . $this->apikey . ".json"), true);

        // invalid player file
        if (is_null($data)) {
            $this->message = "Internal game error: invalid player data";
            $this->writeJSON(500);
        }

        // player data for Game object and update
        $this->nickname = $data["nickname"];
        $this->hp = $data["hp"];
        $this->room = $data["room"];
        $this->exit_room = $data["map"]["exit_room"];
        $this->inventary = $data["inventary"];
        $this->time_registered = $data["time_registered"];
        $this->time_ended = $data["time_ended"];
        $this->map = $data["map"];
        $this->game_ended = $data["game_ended"];

        if ($this->game_ended) {
            $this->message = "Game ended, there is nothing else to do!";
            $this->writeJSON();
        }

        if ($this->hp <= 0) {
            $this->message = "You are dead.";
            $this->writeJSON();
        }
    }

    // process given action
    private function processAction()
    {
        $room_actions = $this->map["room"][$this->room]["actions"] ?? null;
        $basic_actions = [
            "go-north", "go-south", "go-east", "go-west",
        ];

        // unknown/forbidden action
        if (!in_array($this->action, $basic_actions) && !in_array($this->action, $room_actions)) {
            $this->message = "I do not recognize such action or it is not allowed in this room.";
            $this->writeJSON(404);
        }

        // basic actions
        if (in_array($this->action, $basic_actions)) {
            // which direction (trim "go-" off)
            $direction = substr($this->action, 3);

            // path undefined
            if (!isset($this->map["room"][$this->room][$direction]) || empty($this->map["room"][$this->room][$direction])) {
                $this->message = "You cannot go that way in this room!";
                $this->writeJSON(404);
            }

            // change the room according to that direction
            $this->room = $this->map["room"][$this->room][$direction];
            $this->message = $this->map["room"][$this->room]["description"];
        }

        // room-specified actions
        if (!is_null($room_actions) && in_array($this->action, $room_actions)) {
            $effects = $this->map["room"][$this->room]["effects"][$this->action] ?? null;

            // world map error: no effects for action(s)
            if (is_null($effects)) {
                $this->message = "Internal game error: invalid map (no effects for actions)";
                $this->writeJSON(500);
            }

            // type of action must be defined
            if (!isset($effects["type"])) {
                $this->message = "Internal game error: invalid map (effect type not specified)";
                $this->writeJSON(500);
            }

            // TODO: prepare switch for room action types
            switch ($effects["type"]) {
                case "pick":
                    // defined: item
                    if (isset($effects["item"])) {
                        // put item to inventory and delete it from player's map
                        $inventary = $this->inventary;

                        array_push($inventary, $effects["item"]);
                        $item_key = array_search($effects["item"], $this->map["room"][$this->room]["items"]);
                        unset($this->map["room"][$this->room]["items"][$item_key]);
                        $item_key = array_search($this->action, $this->map["room"][$this->room]["actions"]);
                        unset($this->map["room"][$this->room]["actions"][$item_key]);

                        $this->inventary = $inventary;
                        $this->message = $effects["item"] . " picked.";
                    } else {
                        $this->message = "Internal game error: invalid map (no item in effects)";
                        $this->writeJSON();
                    }
                    break;

                case "dismiss":
                    // defined: object
                    if (isset($effects["object"]) && isset($effects["required-item"])) {
                        $inventary = $this->inventary;

                        if (!in_array($effects["required-item"], $inventary)) {
                            $this->message = "You do not have a required item (" . $effects["required-item"] . ")!";
                            $this->writeJSON();
                        }

                        // dissmis the item too
                        $object_key = array_search($effects["required-item"], $inventary);
                        $inventary[$object_key] = null;

                        $object_key = array_search($effects["object"], $this->map["room"][$this->room]["objects"]);
                        unset($this->map["room"][$this->room]["objects"][$object_key]);
                        $object_key = array_search($this->action, $this->map["room"][$this->room]["actions"]);
                        unset($this->map["room"][$this->room]["actions"][$object_key]);

                        $this->inventary = $inventary;
                        $this->message = "Object " . $effects["object"] . " dismissed.";
                    } else {
                        $this->message = "Internal game error: invalid map (no object in effects)";
                    }
                    break;

                case "fill":
                    // defined: required-item, object
                    if (isset($effects["required-item"])) {
                        $inventary = $this->inventary;

                        if (!in_array($effects["required-item"], $inventary)) {
                            $this->message = "You do not have a required item (" . $effects["required-item"] . ")!";
                            $this->writeJSON();
                        }

                        $object_key = array_search($effects["required-item"], $inventary);
                        $inventary[$object_key] = $effects["required-item"] . "-" . $effects["object"];
                        $object_key = array_search($effects["object"], $this->map["room"][$this->room]["objects"]);
                        unset($this->map["room"][$this->room]["objects"][$object_key]);
                        $object_key = array_search($this->action, $this->map["room"][$this->room]["actions"]);
                        unset($this->map["room"][$this->room]["actions"][$object_key]);

                        $this->inventary = $inventary;
                        $this->message = "Filled the " . $effects["required-item"] . " with " . $effects["object"];
                    } else {
                        $this->message = "Internal game error: invalid map (no required-item in effects)";
                    }
                    break;

                case "fight":
                    // defined: object
                    // defined: item ?? none => very low damage
                    break;

                default:
                    if (isset($effects["required-item"])) {
                        $inventary = $this->inventary;

                        if (!in_array($effects["required-item"], $inventary)) {
                            $this->message = "You do not have a required item (" . $effects["required-item"] . ")!";
                            $this->writeJSON();
                        }
                    }
            }

            // if the message was not set, try loading it up
            if (empty($this->message) && isset($effects["message"])) {
                $this->message = $effects["message"];
            }

            // clean the inventary
            $this->inventary = array_filter($this->inventary);

            // do the damage to player if defined (lower and upper hp levels)
            if (isset($effects["damage-hp"]) && count($effects["damage-hp"]) == 2) {
                $damage = rand($effects["damage-hp"][0], $effects["damage-hp"][1]);
                $this->hp -= $damage;
                $this->message .= "\nhp lowered by " . $damage . ".";

                # hp overflow fix
                if ($this->hp > 100) {
                    $this->hp = 100;
                }
            }

            // show hidden room parts
            $hidden = $this->map["room"][$this->room]["hidden"] ?? null;

            if (isset($effects["show-hidden"]) && $effects["show-hidden"] && !is_null($hidden)) {
                // rewrite hidden array keys
                foreach (array_keys($hidden) as $hidden_key) {
                    $this->map["room"][$this->room][$hidden_key] = $hidden[$hidden_key];
                }
            }
        }

        // game ended!
        if ($this->room == $this->exit_room) {
            $this->time_ended = time();
            $time_elapsed = (int) $this->time_ended - (int) $this->time_registered;
            $this->message = "Congratz! You won the game in " . $time_elapsed . " secs!";
            $this->game_ended = true;
        }

        // update player data and exit
        $this->updateUserData();
        $this->writeJSON();
    }

    // update player data after an action was processed
    private function updateUserData()
    {
        $json_data = [
            "nickname" => $this->nickname,
            "hp" => $this->hp,
            "inventary" => $this->inventary,
            "room" => $this->room,
            "time_registered" => $this->time_registered,
            "time_ended" => $this->time_ended,
            "game_ended" => $this->game_ended,
            "map" => $this->map,
        ];

        file_put_contents(__DIR__ . "/../data/" . $this->apikey . ".json", json_encode($json_data));
    }

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
        header('Content-type: text/javascript');
        echo json_encode($json_output, JSON_PRETTY_PRINT);
        exit();
    }
}
