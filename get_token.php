<?php 
	session_start();

    require_once 'ssolib/Grant.php';
    require_once 'ssolib/KeyCloak.php';
    require_once 'ssolib/Token.php';
    require_once 'ssolib/backend-call.php';
?>

<html>
<head>
	<title>Calling SSO for Access Token</title>
</head>

<body>
<?php 
	$headers = array(
		    'Content-Type: application/x-www-form-urlencoded'
		);
	$config = file_get_contents('ssolib/keycloak.json');
    $kc = new \OnionIoT\KeyCloak\KeyCloak($config);

	//$url = 'http://localhost:8080/auth/realms/demo/protocol/openid-connect/token';
 	$params = array('grant_type' => 'authorization_code', 
 				'scope' => 'openid profile', 
 				'client_id' => $kc->getClientId(),
 				'client_secret' => $kc->getClientSecret(),
 				'code' => $_SESSION["code"],
 				'redirect_uri' => $kc->getRedirectUri());

	try {
	  	$response = $kc->send_request('POST', '/protocol/openid-connect/token', $headers, http_build_query($params));
	  	// Shit has failed
    	if ($response['code'] < 200 || $response['code'] > 299) {
        	echo "Error request access token. <br>";
        	var_dump($response);
    	} else {
        	$kc->grant = new \OnionIoT\KeyCloak\Grant($response['body']);

        	echo "<br>Response:"."<br>";
        	echo "Code:".$response['code']."<br>";
        	$body = json_decode($response['body']);
			    foreach($body as $key => $value) {
  				    echo $key . " : " . $value . "<br>";
              if ($key == 'access_token') {
                  $_SESSION["access_token"] = $value;
              }
			    }
			    //var_dump($response);
          echo "<br>Payload Access Token: <br>";
          //print_r ($kc->grant->access_token->payload);
          foreach($kc->grant->access_token->payload as $key => $value) {
              if (!is_array($value)) {
                  echo $key . " : " . $value . "<br>";
              } else {
                  echo $key . ":<br>";
                  foreach ($value as $subkey => $subvalue) {
                      if (!is_array($subvalue)) {
                          echo "&nbsp &nbsp" . $subkey . " : " . $subvalue . "<br>";
                      } else {
                          echo "&nbsp &nbsp" . $subkey . " :<br>&nbsp &nbsp &nbsp &nbsp ";
                          print_r ($subvalue);
                          echo "<br>";
                      }
                  }
              }  
          }

          echo "<br>Payload Access Token: <br>";
          //print_r ($kc->grant->access_token->payload);

    	}

          //Validation
          $is_token_valid = true;
          $er_msg = "";
          $claim = $kc->grant->access_token->payload;
          
          //check the issuer
          if ($claim['iss'] != $kc->realm_url) {
              $is_token_valid = false;
              $er_msg += "<br>Issuer Claim (iss) is not matched with OpenID Provider";
          }

          //validation "aud" --> sementara di-skip dulu karena nilai dan definisinya belum begitu jelas
          
          //check the authorized party
          if ($claim['azp'] != $kc->client_id) {
              $is_token_valid = false;
              $er_msg += "<br>Authorized party (azp) is not matched with Client Id";
          }

          //check the expiry time
          if ($claim['exp'] <= time()) {
              $is_token_valid = false;
              $er_msg += "<br>Token expired";
          }

          //check the issued time, not more than one minute ago
          if ($claim['iat'] + 60 <= time()) {
              $is_token_valid = false;
              $er_msg += "<br>Token issued is outdated";
          }          

          //acr: not checked. It is used for monetary value

          //auth_time: time when the user is authenticated (using login form) 
          //If more than x minutes, user must login again. --> not checked for a while;

          
          if ($is_token_valid) {
              echo "<br> Access token valid.";
          } else {
              echo "<br> Access token tidak valid:".$er_msg;
          }

    	echo "<br><br>Session code: " . $_SESSION["code"];

      /* $access_token = "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJ5NXAwa2Ntb0ZkTDRuMzF3Z2R1ckhVNDNiZXpfaVFnR29XVmw3ZlpWNmI0In0.eyJleHAiOjE2MTAwOTAzNzAsImlhdCI6MTYxMDA5MDA3MCwiYXV0aF90aW1lIjoxNjEwMDkwMDY2LCJqdGkiOiJiNzA2NDNjOS0wMWFmLTQyYjMtYmMzMS00MTgwNGI3ZmU2MzQiLCJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwODAvYXV0aC9yZWFsbXMvZGVtbyIsImF1ZCI6ImFjY291bnQiLCJzdWIiOiI1OGZjN2FmZS03MTc4LTRjOWItOTE4OC1hYjdiNjBmMTM4ZGQiLCJ0eXAiOiJCZWFyZXIiLCJhenAiOiJhcHNzbyIsInNlc3Npb25fc3RhdGUiOiI1YzliYjAyOC0xODQ4LTRlZWQtYWQyNC03MzVmZmQzNTA5ODkiLCJhY3IiOiIxIiwiYWxsb3dlZC1vcmlnaW5zIjpbImh0dHA6Ly9sb2NhbGhvc3QvYXBzc28vIl0sInJlYWxtX2FjY2VzcyI6eyJyb2xlcyI6WyJvZmZsaW5lX2FjY2VzcyIsInVtYV9hdXRob3JpemF0aW9uIl19LCJyZXNvdXJjZV9hY2Nlc3MiOnsiYWNjb3VudCI6eyJyb2xlcyI6WyJtYW5hZ2UtYWNjb3VudCIsIm1hbmFnZS1hY2NvdW50LWxpbmtzIiwidmlldy1wcm9maWxlIl19fSwic2NvcGUiOiJwcm9maWxlIGVtYWlsIiwiZW1haWxfdmVyaWZpZWQiOmZhbHNlLCJuYW1lIjoiQWd1c3RpbnVzIEFuZHJpeWFudG8iLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJqb2huZG9lIiwiZ2l2ZW5fbmFtZSI6IkFndXN0aW51cyIsImZhbWlseV9uYW1lIjoiQW5kcml5YW50byIsImVtYWlsIjoiYWFuZHJpeWFAamFiYXJwcm92LmdvLmlkIn0.fp6JVN8U53GsdeNnWnhRAiYEUtd0HP-ZbcWmMh3oOLs6bIdPYiFx-I-sqWOY7Ui78zXsP835MIbSUxwx--T7Xqc3j3jsbikYvnhRfuphjP8vQGwycAf37Sy3Ts6NEN5RdPqajMHKkxCvRXqYipYb0pD3LBIVNxDzpXceqMybpUZnUz_PGFoCHp4B1tpzvOUPTIu5jt27s_uW0Zk28EdebsIfB2YChdhow0vFZdV9vEWWZVhGYuEa4B8HM46jMSO5KI5RIk81nI7ysGw85KbdkUjpIjKzaOJZAxg9Q2y8hwimLzt3ipR5iqBpCfEHnHc3xzdVculEhKy9jdxgb68tTA";
      $parts = explode('.', $access_token);
      echo "<br><br>Header: " . \OnionIoT\KeyCloak\KeyCloak::url_base64_decode($parts[0]);
      echo "<br><br>Payload: " . \OnionIoT\KeyCloak\KeyCloak::url_base64_decode($parts[1]);
      echo "<br><br>Signature: " . \OnionIoT\KeyCloak\KeyCloak::url_base64_decode($parts[2]);    
    	echo "<br>"; */


    	/*$params = array('grant_type' => 'client_credentials', 
 				'scope' => 'openid', 
 				'client_id' => $kc->getClientId(),
 				'code' => $_SESSION["code"],
 				'client_secret' => $kc->getClientSecret());
    	$response = $kc->send_request('POST', '/protocol/openid-connect/userinfo', $headers, http_build_query($params));
    	var_dump($response);*/

    	//echo '<a href="get_account.php"> Get User Account </a> <br>';

      /*echo '<br> Accessing userinfo....<br>';
      $headers = array(
          'Content-Type: application/x-www-form-urlencoded',
          'Authorization: Bearer '.$_SESSION["access_token"]
      );
      //$params = array('access_token' => $access_token); 
      //$response = $kc->send_request('POST', '/protocol/openid-connect/userinfo', $headers, http_build_query($params));
      $response = $kc->send_request('GET', '/protocol/openid-connect/userinfo', $headers, '');
      // Shit has failed
      if ($response['code'] < 200 || $response['code'] > 299) {
          echo "<br>Error request userinfo. <br>";
          var_dump($response);
      } else {
          echo "<br>Userinfo Response: <br>";
          var_dump($response);
      }  */

      echo '<br><br><a href="http://localhost/apsso/get_userinfo.php"> Get User Info </a>';

    	echo '<br><a href="https://sso.jabarprov.go.id:8443/auth/realms/demo/protocol/openid-connect/logout?redirect_uri=http://localhost/apsso/logout_callback.php"> Logout </a>';
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