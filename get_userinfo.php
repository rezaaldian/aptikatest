<?php 
	session_start();

    require_once 'ssolib/Grant.php';
    require_once 'ssolib/KeyCloak.php';
    require_once 'ssolib/Token.php';
    require_once 'ssolib/backend-call.php';
?>

<html>
<head>
	<title>Calling SSO for User Info</title>
</head>

<body>
<?php 
	try {
      echo '<br> Accessing userinfo....<br>';
      $headers = array(
          'Content-Type: application/x-www-form-urlencoded',
          'Authorization: Bearer '.$_SESSION["access_token"]
      );
      $config = file_get_contents('ssolib/keycloak.json');
      $kc = new \OnionIoT\KeyCloak\KeyCloak($config);
      $response = $kc->send_request('GET', '/protocol/openid-connect/userinfo', $headers, '');
      // Shit has failed
      if ($response['code'] < 200 || $response['code'] > 299) {
          echo "<br>Error request userinfo. <br>";
          var_dump($response);
      } else {
          echo "<br>Userinfo Response: <br>";
          var_dump($response);
      }  

    	echo '<br><br><a href="https://sso.jabarprov.go.id:8443/auth/realms/demo/protocol/openid-connect/logout?redirect_uri=http://localhost/apsso/logout_callback.php"> Logout </a>';
	}
	catch(Exception $ex) {
  		$code = $ex->getCode();
  		$message = $ex->getMessage();
  		$file = $ex->getFile();
  		$line = $ex->getLine();
  		echo "Exception thrown in $file on line $line: [Code $code] $message";
	}
    

?>
</body>
</html>