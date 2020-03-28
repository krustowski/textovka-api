<?php

/**
 * textovka v1
 * 
 * REST API text-based game
 */

 namespace textovka;

 class Game {
	// api attributes
    private $name = "textovka API";
	private $version = "v1";
	private $code = 200;
	private $message = "API OK";
	private $log_file = __DIR__ . "/../log/game.log";
	private $timestamp;
	private $remote_ip;
	private $is_new_user = false;

	// user attributes
	private $nickname = "none";
	private $hp = 100;
	private $inventary = array();
	private $apikey = null; 
	private $graph;
	private $action = "none";
    
    public function __construct() {
		// init
		$this->timestamp = time() ?? null;
		$this->remote_ip = $_SERVER["REMOTE_ADDR"] ?? "none";

		$this->processGets();
	}

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
			$this->writeJSON();
		}

		// wrong API key, data file (user) does not exist
		if (!file_exists(__DIR__ . "/../data/" . $gets["apikey"] . ".json")) {
			$this->message = "Unknown API key (apikey) given.";
			$this->writeJSON();
		}

		$this->apikey = $gets["apikey"];
		$this->loadData();

		// end of processing -> end of algorithm
		$this->writeJSON();
	}

	private function generateKey() {
		$new_hash = null;

		while (true) {
			$new_hash = hash("sha256", $this->timestamp . $this->nickname);

			if (!file_exists(__DIR__ . "/../data/" . $new_hash . ".json")) 
				break;
		}

		// TODO: create new file in data/
		$json_data = [
			"graph" => $this->graph,

		];

		file_put_contents(__DIR__ . "/../data/" . $new_hash . ".json", $json_data);

		$this->apikey = $new_hash;
		$this->is_new_user = true;
		$this->message = "New API key (apikey) for '" . $this->nickname . "' generated.";
		$this->writeJSON();
	}

	private function getData() {
		// TODO: load existing JSON
		$data = json_decode(__DIR__ . "/../data/" . $this->apikey . ".json");
	}

    private function writeJSON() {
		// logging
		file_put_contents($this->log_file, $this->timestamp . " / " . $this->nickname . " / " . $this->action . " / " . $this->remote_ip . "\n", FILE_APPEND);

		$json_output = array(
			"api" => array(
				"name"				=> $this->name,
				"version"			=> $this->version,
				"apikey"			=> $new_key,				
				"timestamp"			=> time()	
			),
			"message"		=> $this->message
		);

		// put JSON data
		header('Content-type: text/javascript');
		echo json_encode($json_output, JSON_PRETTY_PRINT);
		exit();
	}
 }