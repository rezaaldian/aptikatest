<?php 
	session_start();
?>

<html>
<head>
	<title>Logout Callback</title>
</head>

<body>
	<h1>You have successfully logged out</h1>
	<a href="https://sso.jabarprov.go.id:8443/auth/realms/demo/protocol/openid-connect/auth?response_type=code&client_id=apsso&state=a1b2c3d4e5&scope=profile&redirect_uri=http://localhost/apsso/authcode_callback.php">
	Login again</a>

<?php
// remove all session variables
session_unset();

// destroy the session
session_destroy();
?>

</body>
</html>