<?php

require_once("Rating.php");
require_once("Tagging.php");
require_once("Reviewing.php");

function getRatingsForUser($id) {
	
	$url = "http://62.217.125.104:8080/socnav-0.2/ratings?user_remote_id=$id";	
	$response = file_get_contents($url);
	
	$events = json_decode($response);


	echo "<table border=\"1\">";
	echo "<tr><td>Resource Uri</td><td>Dimension</td><td>Value</td><td>Updated at</td></tr>";
	echo "<h3>Ratings</h3>";
	foreach ($events as $event) {
		foreach ($event->preferences as $preference) {
			echo "<tr>";
			echo "<td>".$event->item->resource_uri."</td>";
			echo "<td>$preference->dimension</td>";
			echo "<td>$preference->value</td>";
			echo "<td>$event->updated_at</td>";	
			echo "</tr>";
		}
	}
	echo "</table>";
}


function getTaggingsForUser($id) {
	
	$url = "http://62.217.125.104:8080/socnav-0.2/taggings?user_remote_id=$id";	
	$response = file_get_contents($url);
	
	$events = json_decode($response);

	echo "<table border=\"1\">";
	echo "<tr><td>Resource Uri</td><td>Language</td><td>Tag</td><td>Updated at</td></tr>";
	echo "<h3>Tagging</h3>";
	foreach ($events as $event) {
		foreach ($event->tags as $tag) {
			echo "<tr>";
			echo "<td>".$event->item->resource_uri."</td>";
			echo "<td>$tag->lang</td>";
			echo "<td>$tag->value</td>";
			echo "<td>$event->updated_at</td>";	
			echo "</tr>";
		}
	}
	echo "</table>";

}

function getReviewingsForUser($id) {
	
	$url = "http://62.217.125.104:8080/socnav-0.2/reviewings?user_remote_id=$id";	
	$response = file_get_contents($url);
	
	$events = json_decode($response);

	echo "<table border=\"1\">";
	echo "<tr><td>Resource Uri</td><td>Review</td><td>Updated at</td></tr>";
	echo "<h3>Tagging</h3>";
	foreach ($events as $event) {
		echo "<tr>";
		echo "<td>".$event->item->resource_uri."</td>";
		echo "<td>$event->review</td>";
		echo "<td>$event->updated_at</td>";	
		echo "</tr>";
	}	
	echo "</table>";

}

/*
 * A thin wrapper around the Rating class.
 *
 * @param $user the remote user id.
 * @resource the resource uri. 
 * @param array   $preferences an associative array where the key is the
 *							   dimension and the value the rating.
 */
function postRating($user, $resource, $preferences) {
	$ratings = new Rating($resource, $user, $preferences);
	@$ratings->send();
}


/*
 * A thin wrapper around Tagging class
 *
 * @param $user the remote user id.
 * @resource the resource uri. 
 * @param array   $tags         an associative array where the key is the
 *							    dimension and the value the tag.
 */
function postTagging($user, $resource, $tags) {
	$tagging = new Tagging($resource, $user, $tags);
	$tagging->send();

}

/*
 * A thin wrapper around Reviewing class
 *
 * @param $user the remote user id.
 * @resource the resource uri. 
 * @param string  review        the text of the review
 */
function postReviewing($user, $resource, $review) {
	$reviewing = new Reviewing($resource, $user, $review);
	@$reviewing->send();
}

$action = $_GET["action"];
session_start();
/* If the user is not logged in*/
if ( !isset ($_SESSION['user']) ) {
	header( 'Location: login.php' ) ;
	exit;	
}

if ( isset($_REQUEST["submitted"]) ) {
	
	if ( !isset($_REQUEST["userid"]) || !isset($_REQUEST["resource"])) {
		echo "No user or resource required";
		exit;
	}

	$user = $_REQUEST["userid"];
	$resource = $_REQUEST["resource"];
	
	$dimensions = array("pref1", "pref2", "pref3");
	
	switch ($_REQUEST["submitted"]) {
		
		case 1:	
			$preferences = array();
			foreach ($dimensions as $dim) {
				if (    isset($_REQUEST[$dim."dim"]) && isset($_REQUEST[$dim."val"])
					&& !empty($_REQUEST[$dim."dim"]) && !empty($_REQUEST[$dim."val"]) ) {
					$d = $_REQUEST[$dim."dim"];
					$v = $_REQUEST[$dim."val"];					
					
					$preference = array( "dimension" => $d, "value" => $v );
					$preferences[] = $preference;
				}
			}
			
			postRating($user, $resource, $preferences);
			break;
		case 2:
			echo "Inside case 2";
			$tags = array();
			foreach ($dimensions as $dim) {
				if (    isset($_REQUEST[$dim."lan"]) && isset($_REQUEST[$dim."val"])
					&& !empty($_REQUEST[$dim."lan"]) && !empty($_REQUEST[$dim."val"]) ) {
					$l = $_REQUEST[$dim."lan"];
					$v = $_REQUEST[$dim."val"];					
					
					$tag = array( "language" => $l, "value" => $v );
					$tags[] = $tag;
				}
			}
			postTagging($user, $resource, $tags);
			break;
		case 3:	
			if ( isset($_REQUEST["review"]) && isset($_REQUEST["review"] )) {
				$r = $_REQUEST["review"];	
				postReviewing($user, $resource, $r);
			}
			break;
	}
}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Social Navigation Tool</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="style.css" rel="stylesheet" type="text/css" media="screen" />

</head>
<body>
<div id="header-wrapper">
	<div id="header"> 
		<div id="menu">
			<ul>
				<li> <a href="login.php" >Login</a></li>
				<li> <a href="client.php?action=read" >Read Data</a></li>
				<li> <a href="client.php?action=write" >Write Data</a></li>
			</ul>
		</div> <!-- end #menu -->
	</div><!-- end #header -->
</div><!-- end #header-wrapper -->

<?php
	if (isset($_REQUEST['userid'])){
		$userId = $_REQUEST['userid'];
	
		echo "<h2>User: ".$userId."</h2>";
		if (isset($_REQUEST['ratings'])){
			getRatingsForUser($userId);
		}

		if (isset($_REQUEST['taggings'])){
			getTaggingsForUser($userId);
		}

		if (isset($_REQUEST['reviewings'])){
			getReviewingsForUser($userId);
		}
	}
?>
<div id="page">
	
	<div id="content">  
	<?php 
	switch($action) {
		case "read":	
			echo "TIME TO READ";
	?>	
		<form id='login' method='post' accept-charset='UTF-8'>
		<fieldset >
			<legend>Read Data</legend>
			<!--<input type='hidden' name='submitted' id='submitted' value='1'/>-->
			<label for='userId' >User remote id:</label>
			<input type='text' name='userid' id='userid'  maxlength="50" />		
			<br>
			<br>
			<label> Types of Data </label>
			<br>
			<input type="checkbox" name="ratings" value="ratings">Ratings<br>
			<input type="checkbox" name="taggings" value="taggings">Taggings<br>
			<input type="checkbox" name="reviewings" value="reviewings">Reviewings<br>
			
			<input type='submit' name='Submit' value='Submit' />
		</fieldset>
		</form>
	<?php		
			break;
		case "write":
			echo "TIME TO WRITE";
	?>
		<form id='ratings' method='post' accept-charset='UTF-8'>
		<fieldset >
			<legend>Write Rating</legend>
			<input type='hidden' name='submitted' id='submitted' value='1'/>
			<label for='userId' >User remote id:</label>
			<input type='text' name='userid' id='userid'  maxlength="50" />		
			<br>	
			<label for='resource' >Resource Url:</label>
			<input type='text' name='resource' id='resource'  maxlength="50" />		
			<br>
			<br>
			<label> Preferences </label>
			<br>
			<label for='pref1Dim' >Preference Dimension:</label>
			<input type="text" name="pref1dim">	
			<label for='pref1Dim' >Preference Value:</label>
			<input type="text" name="pref1val">
			<br>	
			
			<label for='pref2Dim' >Preference Dimension:</label>
			<input type="text" name="pref2dim">	
			<label for='pref2Dim' >Preference Value:</label>
			<input type="text" name="pref2val">
			<br>

			<label for='pref3Dim' >Preference Dimension:</label>
			<input type="text" name="pref3dim">	
			<label for='pref3Dim' >Preference Value:</label>
			<input type="text" name="pref3val">
			<br>
			
			<input type='submit' name='Submit' value='Submit' />
		</fieldset>
		</form>

		<form id='taggings' method='post' accept-charset='UTF-8'>
		<fieldset >
			<legend>Write Tagging</legend>
			<input type='hidden' name='submitted' id='submitted' value='2'/>
			<label for='userId' >User remote id:</label>
			<input type='text' name='userid' id='userid'  maxlength="50" />		
			<br>	
			<label for='resource' >Resource Url:</label>
			<input type='text' name='resource' id='resource'  maxlength="50" />		
			<br>
			<br>
			<label> Taggings </label>
			<br>
			<label for='pref1lan' >Tag Language:</label>
			<input type="text" name="pref1lan">	
			<label for='pref1Val' >Tag Value:</label>
			<input type="text" name="pref1val">
			<br>	
			
			<label for='pref2Lan' >Tag Language:</label>
			<input type="text" name="pref2lan">	
			<label for='pref2Val' >Tag Value:</label>
			<input type="text" name="pref2val">
			<br>

			<label for='prefLan' >Tag Language:</label>
			<input type="text" name="pref3lan">	
			<label for='pref3Val' >Tag Value:</label>
			<input type="text" name="pref3val">
			<br>
			
			<input type='submit' name='Submit' value='Submit' />
		</fieldset>
		</form>

		<form id='reviews' method='post' accept-charset='UTF-8'>
		<fieldset >
			<legend>Write Review</legend>
			<input type='hidden' name='submitted' id='submitted' value='3'/>
			<label for='userId' >User remote id:</label>
			<input type='text' name='userid' id='userid'  maxlength="50" />		
			<br>	
			<label for='resource' >Resource Url:</label>
			<input type='text' name='resource' id='resource'  maxlength="50" />		
			<br>
			<br>
			<label> Review </label>
			<input size="100" type="text" name="review">	
			<br>			
			<input type='submit' name='Submit' value='Submit' />
		</fieldset>
		</form>


	<?php
			break;
		default:
			echo "Choose Action";
			exit();
			break;
		}
	?>
	</div><!-- end #content -->

	<div style="clear: both;">&nbsp;</div>
</div>
<!-- end #page -->

<div id="footer">
	<p>Copyright (c) 2012 AgroKnow Technologies. All rights reserved. Design by <a href="http://www.freecsstemplates.org/">Free CSS Templates</a>.</p>
</div>
<!-- end #footer -->

</body>
</html>

