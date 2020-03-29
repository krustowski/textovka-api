<?php

/**
 * textovka v1
 * 
 * REST API text-based game
 */

 namespace textovka;

 class Game {
	// API attributes
    private $apiname = "textovka API";
	private $version = "v1";
	private $message;
	private $log_file = __DIR__ . "/../game.log";
	private $timestamp;
	private $remote_ip;
	private $apikey = "none";

	// user attributes
	private $nickname = null;
	private $hp = 100;
	private $room = null;
	private $inventary = array();
	private $map = null;
	private $time_registred;
	private $action;
	
	// hashing
	const PEPPER = "7e0952136aba42e91fcf7967f490da3d6d4cb175a5916f8e451f924ea77caeb5";
	
	// init function
    public function __construct() {
		// init
		$this->timestamp = microtime(true);
		$this->remote_ip = $_SERVER["REMOTE_ADDR"] ?? "none";

		// process URI queries
		$this->processGets();
	}

	// process URI queries (GET)
	private function processGets() {
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
	private function generateKey() {
		$new_hash = null;

		// generate new hash until a new one can be used
		while (true) {
			$new_hash = hash("sha256", self::PEPPER . $this->nickname);

			if (!file_exists(__DIR__ . "/../data/" . $new_hash . ".json")) { 
				break; 
			} else {
				$json_data = json_decode(file_get_contents(__DIR__ . "/../data/" . $new_hash . ".json"), true);

				// user is already registred (has their file with such nickname)
				if ($json_data["nickname"] == $this->nickname) {
					$this->message = "User '" . $this->nickname . "' already exists.";
					$this->writeJSON(403);
				}

				break;
			}
		}

		// load world map
		$init_map = json_decode(file_get_contents(__DIR__ . "/../map.json"), true);

		// invalid map file
		if (is_null($init_map)) {
			$this->message = "Internal game error: invalid map";
			$this->writeJSON(500);
		}

		// player data to be written
		$json_data = [
			"nickname" 			=> $this->nickname,
			"hp" 				=> 100,
			"inventary" 		=> [],
			"room"				=> $init_map["default_room"],
			"time_registred"	=> (int)$this->timestamp, 
			"map" 				=> $init_map
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
	private function getUserData() {
		$data = json_decode(file_get_contents(__DIR__ . "/../data/" . $this->apikey . ".json"), true);

		// invalid player file
		if (is_null($data)) {
			$this->message = "Internal game error: invalid player data";
			$this->writeJSON(500);
		}

		// player data for Game object and update
		$this->nickname			= $data["nickname"];
		$this->hp 				= $data["hp"];
		$this->room				= $data["room"];
		$this->inventary		= $data["inventary"];
		$this->time_registred 	= $data["time_registred"];
		$this->map				= $data["map"];
	}

	// process given action
	private function processAction() {
		$room_actions = $this->map["room"][$this->room]["actions"] ?? null;
		$basic_actions = [
			"go-north", "go-south", "go-east", "go-west"
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
			if (!isset($this->map["room"][$this->room][$direction])) {
				$this->message = "There is no such way in this room.";
				$this->writeJSON(404);
			}

			// change the room according to that direction
			$this->room = $this->map["room"][$this->room][$direction];
			$this->message = $this->map["room"][$this->room]["description"];
		}

		// update player data and exit
		$this->updateUserData();
		$this->writeJSON();
	}

	// update player data after an action was processed
	private function updateUserData() {
		$json_data = [
			"nickname" 			=> $this->nickname,
			"hp" 				=> $this->hp,
			"inventary" 		=> $this->inventary,
			"room"				=> $this->room,
			"time_registred"	=> $this->time_registred, 
			"map" 				=> $this->map
		];

		file_put_contents(__DIR__ . "/../data/" . $this->apikey . ".json", json_encode($json_data));
	}

	// put JSON to player - exit function
    private function writeJSON($code = 200) {
		// logging
		file_put_contents($this->log_file, (int)$this->timestamp . " / " . ($this->nickname ?? "none") . " / " . ($this->action ?? "none") . " / " . $this->remote_ip . "\n", FILE_APPEND);

		// player data
		$player_data = [
			"nickname" 		=> $this->nickname,
			"hp" 			=> $this->hp,
			"room"			=> $this->room,
			"inventary" 	=> $this->inventary,
			"time_elapsed"	=> (int)$this->timestamp - (int)$this->time_registred
		];

		// room data
		$room_data = [
			"items"			=> $this->map["room"][$this->room]["items"] ?? null,
			"objects" 		=> $this->map["room"][$this->room]["objects"] ?? null,
			"actions"		=> $this->map["room"][$this->room]["actions"] ?? null
		];

		// JSON output
		$json_output = [
			"api" => [
				"name"				=> $this->apiname,
				"version"			=> $this->version,
				"engine_build"		=> md5_file(__FILE__) ?? null,
				"exec_time_in_ms"	=> round((microtime(true) - $this->timestamp) * 1000, 2),
				"status_code"		=> $code,
				"action"			=> $this->action,
				"apikey"			=> $this->apikey,				
				"timestamp"			=> (int)$this->timestamp	
			],
			"player" 	=> (!is_null($this->room) ? $player_data : []),
			"room"		=> (!is_null($this->room) ? $room_data : []),
			"message" 	=> $this->message
		];

		// put JSON data
		header('Content-type: text/javascript');
		echo json_encode($json_output, JSON_PRETTY_PRINT);
		exit();
	}
 }