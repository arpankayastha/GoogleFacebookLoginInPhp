<?php
session_start();
// Include FB config file
if(isset($_GET['from']) && $_GET['from'] == "fb"){
	require_once 'fbConfig.php';
	// Remove access token from session
	unset($_SESSION['facebook_access_token']);
	// Remove user data from session
	unset($_SESSION['userData']);
}else{
	//Include GP config file
	include_once 'gpConfig.php';
	//Unset token and user data from session
	unset($_SESSION['token']);
	unset($_SESSION['userData']);
	//Reset OAuth access token
	$gClient->revokeToken();
	//Destroy entire session
	session_destroy();
}
// Redirect to the homepage
header("Location:index.php");
?>