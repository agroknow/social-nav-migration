<?php

require_once("Event.php");
require_once("Exceptions.php");

/**
 * This class represents the ratings generated by a user for a specific item.
 * The user could have generate multiple ratings for a item e.g multiple 
 * ratings, multidimensional ratings, etc.
 *
 * @param string  $resource_url
 * @param integer $user_id 
 * @param array   $preferences an associative array where the key is the
 *							   dimension and the value the rating.
 * @param integer $rated_on    unix timestamp
 * @param string  $domain      the url of the domain where the rating occured
 * $param string  $ip_address  the ip address of the host who performed the rating.
 */
class Rating extends Event {

	const COMMAND = 'ratings';
	function __construct( $resource_url, $user_id, $preferences, $rated_on=0, $domain = null, $ip_address = null ) {
		parent::__construct($resource_url, $user_id);
	
		$this->data["preferences"] = $preferences;
		if($domain) $this->data["domain"] = $domain;
		if($ip_address) $this->data["ip_address"] = $ip_address;
	}
	
	function send(){
		parent::send(self::COMMAND);
	}

	public static function byUserId($id) {
		$url = "http://62.217.125.104:8080/socnav-0.2/ratings?user_remote_id=$id";	
		$response = file_get_contents($url);
		echo "$response";
	}
}
