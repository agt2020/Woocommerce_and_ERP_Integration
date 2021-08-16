<?php

if (sizeof($_POST))
{
	require_once ('lib/db.php');
	// CREATE DB CONNECTION
	$db = new DB();
	if ($db->db_error != '')
	{
		echo $db->db_error;
		die();
	}

	$query = 'SELECT id
			  FROM sg_users
			  WHERE status = 1 AND username="'.$_POST['username'].'" AND password="'.md5($_POST['password']).'"';
	$result = $db->conn->query($query);
	$row = $result->fetch_assoc();
	if ($row['id'] != '')
	{
		session_start();
		$_SESSION['sg_session_id'] = session_id();
		header('location: index.php');
		die();
	}
	else
	{
		header('location: login.php');
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>ورود به تنظیمات راهکاران</title>
	<link href="css/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="css/login.css">
	<script type="text/javascript" src="js/login.js"></script>
</head>
<body>
	<div class="login-page">
		<div class="form">
			<form class="login-form" method="POST" action="#">
				<input id="username" name="username" type="text" placeholder="نام کاربری"/>
				<input id="password" name="password" type="password" placeholder="رمز عبور"/>
				<button>ورود</button>
			</form>
		</div>
	</div>
</body>
</html>