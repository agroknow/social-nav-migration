<?php

require_once("Exceptions.php");

/**
 * This file contains the classes representing the different events.
 */
/**
 * Superclass of all events containing conveniency methods.
 */
class Event {
	const TARGET = 'http://62.217.125.104:8080/socnav-0.2/';
	
	protected $data = array();
	function __construct( $resource_url, $user_id) {
		
		if ( !$this->isValid($resource_url) )
			throw new EncodingException("Invalid resource: $resource_url");

		if ( !$this->isValid($user_id) )
			throw new EncodingException("Invalid user_id: $user_id");

		$this->data['item'] = array();
		$this->data['item']['resource_uri'] = $resource_url;
		$this->data['user'] = array();
		$this->data['user']['remote_id'] = $user_id;
	}

	/**
	 * Check if the string contains only valid UTF-8 encoding.
	 * @param string $string the string to be checked for validity
	 */
	protected function isValid($string){

		if (preg_match('//u', $string) === false) {
			$error = "";

			if (preg_last_error() == PREG_NO_ERROR) { 
				$error = 'There is no error.';
			}
			else if (preg_last_error() == PREG_INTERNAL_ERROR) {
				$error = 'There is an internal error!';
			}
			else if (preg_last_error() == PREG_BACKTRACK_LIMIT_ERROR) {
				$error = 'Backtrack limit was exhausted!';
			}
			else if (preg_last_error() == PREG_RECURSION_LIMIT_ERROR) {
				$error = 'Recursion limit was exhausted!';
			}
			else if (preg_last_error() == PREG_BAD_UTF8_ERROR) {
				$error = 'Bad UTF8 error!';
			}
			else if (preg_last_error() == PREG_BAD_UTF8_ERROR) {
				$error = 'Bad UTF8 offset error!';
			}

			error_log( "Validation error:".$error );
			return false;
		}

		/* 
		 * Check that the number of ? in the text is less the 70%
		 * of the total characters.
		 */
		$char_count = strlen($string);
		$question_count = substr_count($string, '?');

		if ( $question_count/$char_count > 0.7 ) {
			error_log( "Validation error: too many ?" );
			return false;
		}

	return true;
	}

	/**
	 * Makes a POST request to $url. The request contains the $data
	 * represented as json.
	 */
	public function send($command){

		$opts = array( 'http'=>array(
    							'method'=>'POST',
								'content'=> json_encode($this->data)
					));
		$opts['http']['header'] = array('Content-Type' => 'application/json',
									     	  'Accept' => 'application/json');
		$context = stream_context_create($opts);

   		$fp = fopen(self::TARGET.$command, 'r', false, $context);
   		fpassthru($fp);
   		fclose($fp);
	}

	public function toJSON() {
		return json_encode($this->data);
	}

}
