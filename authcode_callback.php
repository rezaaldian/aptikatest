<?php
// Start the session
session_start();
?>

<html>
<head>
	<title>Testing SSO Callback</title>
</head>

<body>
	<h1>Halaman Callback SSO</h1> 
<?php 
	if (isset($_GET)) {
		echo "state = " . ($_GET["state"]) . " <br> ";
		echo "session_state = " . ($_GET["session_state"]) . " <br> ";
		$_SESSION["code"] = $_GET["code"];
		echo "code = " . ($_SESSION["code"]) . " <br>"; 
		print_r ($_GET);
		echo "<br><br>";
		
		echo '<a href="get_token.php"> Get Token 2 </a>';
	} 
	elseif (isset($_POST)) {
		echo print_r($_POST);
		//foreach ($_POST as $x) {
		//	echo $x . " <br> ";
		//}
	}	
?>
</body>
</html>