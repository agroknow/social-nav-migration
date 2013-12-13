<?php

if ( !empty($_POST['username']) && !empty($_POST['password']) ) {

	$username = trim($_POST['username']);
	$password = trim($_POST['password']);
	if ($username == "soc" && $password == "nav") {
		session_start();
		$_SESSION['user'] = $username;
		$_SESSION['userId'] = $password;
   		header( 'Location: client.php' ) ;
		exit;	
	}
}
?>

<div id="page">	
	<div id="content">  
		<form id='login' method='post' accept-charset='UTF-8'>
		<fieldset >
			<legend>Login</legend>
			<input type='hidden' name='submitted' id='submitted' value='1'/>
			<label for='username' >UserName*:</label>
			<input type='text' name='username' id='username'  maxlength="50" />
			<label for='password' >Password*:</label>
			<input type='password' name='password' id='password' maxlength="50" />
			<input type='submit' name='Submit' value='Submit' />
		</fieldset>
		</form>
</div><!-- end #content -->
