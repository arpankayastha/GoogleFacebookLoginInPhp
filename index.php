<?php
// Include FB config file && User class
require_once 'gpConfig.php';

require_once 'fbConfig.php';

require_once 'User.php';


if(isset($accessToken)){
    if(isset($_SESSION['facebook_access_token'])){
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    }else{
            // Put short-lived access token in session
        $_SESSION['facebook_access_token'] = (string) $accessToken;
              // OAuth 2.0 client handler helps to manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();
            // Exchanges a short-lived access token for a long-lived one
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
        $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
            // Set default access token to be used in script
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    }
}
if (isset($_SESSION['facebook_access_token']) && !empty($_SESSION['facebook_access_token'])) {
        // Redirect the user back to the same page if url has "code" parameter in query string
    if(isset($_GET['code'])){
        header('Location: ./');
    }
        // Getting user facebook profile info
    try {
        $profileRequest = $fb->get('/me?fields=name,first_name,last_name,email,link,gender,locale,picture');
        $fbUserProfile = $profileRequest->getGraphNode()->asArray();
    } catch(FacebookResponseException $e) {
        echo 'Graph returned an error: ' . $e->getMessage();
        session_destroy();
            // Redirect user back to app login page
        header("Location: ./");
        exit;
    } catch(FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }

        // Initialize User class
    $user = new User();

        // Insert or update user data to the database
    $fbUserData = array(
        'oauth_provider'=> 'facebook',
        'oauth_uid'     => $fbUserProfile['id'],
        'first_name'    => $fbUserProfile['first_name'],
        'last_name'     => $fbUserProfile['last_name'],
        'email'         => $fbUserProfile['email'],
        'gender'        => $fbUserProfile['gender'],
        'locale'        => $fbUserProfile['locale'],
        'picture'       => $fbUserProfile['picture']['url'],
        'link'          => $fbUserProfile['link']
    );
    $userData = $user->checkUser($fbUserData);

        // Put user data into session
    $_SESSION['userData'] = $userData;

        // Get logout url
    $logoutURL = $helper->getLogoutUrl($accessToken, $redirectURL.'logout.php?from=fb');        
    
}else{

    if(isset($_GET['code'])){
        $gClient->authenticate($_GET['code']);
        $_SESSION['token'] = $gClient->getAccessToken();
        header('Location: ' . filter_var($redirectURL, FILTER_SANITIZE_URL));
    }

    if (isset($_SESSION['token'])) {
        $gClient->setAccessToken($_SESSION['token']);
    }

    if ($gClient->getAccessToken()) {
        //Get user profile data from google
        $gpUserProfile = $google_oauthV2->userinfo->get();
        
        //Initialize User class
        $user = new User();
        
        //Insert or update user data to the database
        $gpUserData = array(
            'oauth_provider'=> 'google',
            'oauth_uid'     => $gpUserProfile['id'],
            'first_name'    => $gpUserProfile['given_name'],
            'last_name'     => $gpUserProfile['family_name'],
            'email'         => $gpUserProfile['email'],
            'gender'        => $gpUserProfile['gender'],
            'locale'        => $gpUserProfile['locale'],
            'picture'       => $gpUserProfile['picture'],
            'link'          => $gpUserProfile['link']
        );
        $userData = $user->checkUser($gpUserData);
        
        //Storing user data into session
        $_SESSION['userData'] = $userData;
    }
}

$fbloginURL = $helper->getLoginUrl($redirectURL, $fbPermissions);
$authUrl = $gClient->createAuthUrl();
$gploginURL = filter_var($authUrl, FILTER_SANITIZE_URL);

?>
<html>
<head>
    <title>FaceBook &  Google Login</title>
    <style type="text/css">
    h1{font-family:Arial, Helvetica, sans-serif;color:#999999;}
</style>
</head>
<body>
    <!-- Display login button / Facebook profile information -->
    <?php 
    if (isset($_SESSION['userData']) && !empty($_SESSION['userData'])) { ?>
    <div>
        <span>Profile Picture </span>
        <span> : </span>
        <br>
        <span> <img src="<?php echo $_SESSION['userData']['picture']; ?>" height="300" width="200"> </span>
    </div>
    <div>
        <span>Login By </span>
        <span> : </span>
        <span> <?php echo $_SESSION['userData']['oauth_provider']; ?> </span>
    </div>
    <div>
        <span>First Name </span>
        <span> : </span>
        <span> <?php echo $_SESSION['userData']['first_name']; ?> </span>
    </div>
    <div>
        <span>Last Name </span>
        <span> : </span>
        <span> <?php echo $_SESSION['userData']['last_name']; ?> </span>
    </div>
    <div>
        <span>Email </span>
        <span> : </span>
        <span> <?php echo $_SESSION['userData']['email']; ?> </span>
    </div>
    <div>
        <span>logout </span>
        <span> : </span>
        <span> 
            <?php if($_SESSION['userData']['oauth_provider'] == "facebook"){ ?>
            <a href="logout.php?from=fb">Logout</a>
            <?php }else{ ?>
            <a href="logout.php?from=gp">Logout</a>
            <?php  }  ?>
        </span>   
    </div>
    <?php }else{ ?>
    <div>
        <a href="<?php echo htmlspecialchars($fbloginURL); ?>">
            <img src="images/fblogin-btn.png">
        </a>
        <a href="<?php echo htmlspecialchars($gploginURL); ?>">
            <img src="images/glogin.png">
        </a>
    </div>
    <?php } ?>
</body> 
</html>
