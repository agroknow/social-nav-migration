<?php

require_once("Rating.php");
require_once("Tagging.php");
require_once("Reviewing.php");
require_once("Exceptions.php");

/*
 * This file harvests social data from the old socnav transforms thems
 * and sends them to the new socnav
 */


if ($argv[1] =="--help" or $argv[1] == "-h") {
?>

  This is the migration service for the social navigation module.
  The service reads the user ids from the stdin

  Usage:
  <?php echo $argv[0];?> [-h] [-d] -n number_of_users 

  -d 
      dry run. Fetch the result from the old socnav, make the necessary,
      but don't send the results to the new socnav.

  -n
      The number of user to fetch results for. The service will fetch ratings,
      reviews and tags for the first n users.
  
  -h, --help
      Print this message.

  Example
  php migration_service.php -n100 < userss.csv

  Read the ratings, taggings and reviewing for the first 100 users from userss.csv
  and store them in the new socnav.


  php migration_service.php -n100 -d < userss.csv

  Read the ratings, taggings and reviewing for the first 100 users from userss.csv
  and do NOT store them in the new socnav.



<?php
exit(0);
}

$shortopts = "dhn:";
$longopts  = array("help");
$options = getopt($shortopts, $longopts);

$dry_run = false;
if( array_key_exists("d", $options) )
	$dry_run = true;

if( array_key_exists("n", $options) )
	$users = intval($options["n"]);
else {
	echo "Use must specify the number of users, type -h for help.\n";
	exit(-1);
}

require_once('Event.php');
require_once('Harvester.php');

$total_ratings = 0;
$total_taggings = 0;
$total_reviewings = 0;

$error_ratings = 0;
$error_taggings = 0;
$error_reviewings = 0;
$error_encoding = 0;

$no_rate = 0;
$no_tag = 0;
$no_review = 0;

function migrateRatingsForUser($i) {
	echo "* migrateRatingsForUser($i)\n";
	global $dry_run, $client;
	
	global $total_ratings, $no_rate;

	$ratings =  $client->getRatings($i); 
	
	// No ratings from this user
	if ( array_key_exists("norate", $ratings[0]) ) {
		$no_rate++;
		return;
	}

	foreach( $ratings as $rating ) {

		$total_ratings++;

		$r = new Rating( $rating["resourceUrl"], $i, $rating["dimrate"], $rating["rated_on"] );
		print( $r->toJSON()."<br>" );
		if (!$dry_run)
			@$r->send();
	}

}

function migrateTaggingsForUser($i) {
	echo "* migrateTagginsForUser($i)\n";
	global $dry_run, $client;

	global $total_taggings, $no_tag, $error_taggings, $error_encoding;

	$taggings =  $client->getTaggings($i); 

	if( $taggings[0] == null ) return;
	
	
	// No tags from this user
	if ( array_key_exists("notag", $taggings[0]) ) {
		$no_tag++;
		return;
	}

	try {
		foreach( $taggings as $tagging ) {
			
			$total_taggings++;

			$t = new Tagging( $tagging["resourceUrl"], $i, $tagging["result"], $tagging["tagged_on"] );
//			print( $t->toJSON()."<br>" );
			if (!$dry_run)
				@$t->send();
		}
	} catch (TaggingsException $tex) {
		$error_taggings++;
		error_log($tex);
	} catch (EncodingException $eex) {
		$error_encoding++;
		error_log($eex);	
	}

}

function migrateReviewingsForUser($i) {
	echo "* migrateReviewingsForUser($i)\n";
	global $dry_run, $client;

	global $total_reviewings, $no_review, $error_encoding;

	$reviewings =  $client->getReviews($i); 

	// Nothing about this user
	if($reviewings[0] == null) return;

	// No reviews from this user
	if ( array_key_exists("noreview", $reviewings[0]) ) {
		$no_review++;
		return;
	}

	foreach( $reviewings as $reviewing ) {
		try {
			foreach( $reviewing["dimreview"] as $review) {	

				$total_reviewings++;

				$r = new Reviewing( $reviewing["resourceUrl"], $i, $review["value"], $reviewing["reviewed_on"] );
//				print( $r->toJSON()."<br>" );
				if (!$dry_run)
					@$r->send();
			}
		} catch (EncodingException $eex) {
			$error_encoding++;
			error_log($eex);
		}
	}
}

$client = new Harvester();

//getRatingsForUser(0);
//getTaggingsForUser(55);
//getReviewingsForUser(67);


$f = fopen( 'php://stdin', 'r' );

$i=0;
while( $line = fgets( $f ) ) {

	$id = intval(trim($line));

    try {	
        migrateRatingsForUser($id);
	    migrateTaggingsForUser($id);
	    migrateReviewingsForUser($id);
    } catch (Exception $ex) {
        continue;
    }

	$i++;
	if ($i == $users)
		break;
}

fclose( $f );

echo "Total Ratings....$total_ratings\n";
echo "No Ratings.......$no_rate\n";
echo "Total Taggings...$total_taggings\n";
echo "No Tags..........$no_tag\n";
echo "Error Taggings...$error_taggings\n";
echo "Total Reviewings.$total_reviewings\n";
echo "No reviewings....$no_review\n";
echo "Error Encoding...$error_encoding\n";

exit(0);
