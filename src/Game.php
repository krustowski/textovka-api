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
	private $message = "OK";
	private $gets = array();
	private $log_file = __DIR__ . "/../log/game.log";
	private $timestamp;
	private $remote_ip;

	// user attributes
	private $nickname = "none";
	private $hp = 100;
	private $inventary = array();
	private $apikey;
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

		// blank API call
		if (!isset($gets["apikey"]) || empty($gets["apikey"]) || !isset($gets["register"]) || empty($gets["register"])) {
			$this->message = "No API key (apikey) passed. You can get one by performing a registration by appending 'register=user_name' where 'user_name' is your nickname.";
			$this->writeJSON();
		}

		// wrong API key, data file (user) does not exist
		if (!file_exists(__DIR__ . "/../data/" . $gets["apikey"] . ".json")) {
			$this->message = "Unknown API key (apikey) given.";
			$this->writeJSON();
		}
	}

    private function writeJSON() {
		// logging
		file_put_contents($this->log_file, $this->timestamp . " / " . $this->nickname . " / " . $this->action . " / " . $this->remote_ip . "\n", FILE_APPEND);

		$json = array(
			"message" 		    => $this->message,
			"api" => array(
				"name" 			    => $this->name,
				"version"		    => $this->version,
				"code" 			    => $this->code,
				"timestamp" 		=> time()	
			)
		);

		// put JSON data
		header('Content-type: text/javascript');
		echo json_encode($json, JSON_PRETTY_PRINT);
		exit();
	}
 }