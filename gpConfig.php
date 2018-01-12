<?php

//Include Google client library 
include_once 'google/src/Google_Client.php';
include_once 'google/src/contrib/Google_Oauth2Service.php';


/*
 * Configuration and setup Google API
 */
$clientId = '123485412918-glcbued2u5aliotscv9o6qhqe558j4cm.apps.googleusercontent.com';
$clientSecret = 'tl7JURhXS_nwpMM8txO9GCDY';
$redirectURL = 'http://localhost/google_facebook_login_php/';

//Call Google API
$gClient = new Google_Client();
$gClient->setApplicationName('Xodec Technology');
$gClient->setClientId($clientId);
$gClient->setClientSecret($clientSecret);
$gClient->setRedirectUri($redirectURL);

$google_oauthV2 = new Google_Oauth2Service($gClient);
?>