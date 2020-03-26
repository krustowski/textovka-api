<?php

/**
 * textovka v1
 * 
 * REST API text-based game
 */

 namespace textovka;

 class Api {

    private $name = "textovka API";
	private $version = "v1";
	private $code = 200;
	private $message = "OK";
	private $api_quota = 100;
	private $api_usage = 0;

	private $apikey;
	private $api_ips = [];
	private $identity = "NULL";
	private $acl = "NULL";
	private $data_array = array();
    private $query = "NULL";
    
    public static function init() {
        return new Api();
    }

    public static function writeJSON() {
		// logging
		Core::$mysqli->query("INSERT INTO `api_calls` (`id`, `ip`, `identity`, `query`, `code`, `timestamp`) VALUES (NULL, '" . Core::$remote_IP  . "', '" . $this->identity . "', '" . $this->query . "', '" . $this->code . "', '" . date("Y-m-d H:i:s") . "')");

		$json = array(
			"timestamp" 		=> time(),
			"name" 			    => $this->name,
			"version"		    => $this->version,
			"code" 			    => $this->code,
			"message" 		    => $this->message,
			"api_quota_hourly"	=> $this->api_quota,
			"api_usage"		    => $this->api_usage,
			"system_load" 		=> sys_getloadavg(),
			"identity" 		    => $this->identity,
			"acl" 		    	=> $this->acl,
			"query"		    	=> $this->query,
			"data" 			    => $this->data_array
		);

		header('Content-type: text/javascript');
		echo json_encode($json, JSON_PRETTY_PRINT);
		exit();
    }
 }