<?php
require_once('../lib/nusoap.php');

/**
 * This class is a Soap client which connect to the old socnav and retrieves
 * ratings, reviews and tags. This class internally uses the nusoap library.
 */
class Harvester {
	
	const TARGET = 'http://e-knownet.grnet.gr/cfmodule/server.php'; 
	private $client;
	
	function __construct() {
		$this->client = new nusoap_client(self::TARGET);
		$err = $this->client->getError();
		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
			exit();
		}
	}

	function getRatings ($user) {
		$parameter=array('user'=>$user);
		return $this->client->call('Functionclass1.userRatings', $parameter);
	}

	function getReviews ($user) {
		$parameter=array('user'=>$user);
		return $this->client->call('Functionclass1.userReviewings', $parameter);
	}

	function getTaggings ($user) {
		$parameter=array('user'=>$user);
		return $this->client->call('Functionclass1.userTaggings', $parameter);
	}
}
