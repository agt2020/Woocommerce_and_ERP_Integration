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
<!doctype html>
<html lang="en">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="stylesheet" href="vendor/css/fonts.css">
        <link rel="stylesheet" href="vendor/css/style.css">
        <link rel="stylesheet" href="vendor/css/owl.carousel.min.css">
        <link rel="stylesheet" href="vendor/css/bootstrap/bootstrap.min.css">
        <link rel="stylesheet" href="vendor/css/bootstrap/style.css">

        <title>مدیریت حسابداری RT</title>
        <body>
                <div class="content">
                        <div class="container">
                                <div class="row">
                                        <div class="col-md-6">
                                                <img src="vendor/images/vector.svg" alt="Image" class="img-fluid">
                                        </div>
                                        <div class="col-md-6 contents">
                                                <div class="row justify-content-center">
                                                        <div class="col-md-8">
                                                                <div class="mb-4">
                                                                        <h3>RT</h3>
                                                                        <p class="mb-4"></p>
                                                                </div>
                                                                <form action="#" method="post">
                                                                        <div class="form-group">
                                                                                <label for="username">نام کاربری</label>
                                                                                <input type="text" class="form-control" id="username" name="username">
                                                                        </div>
                                                                        <div class="form-group last mb-4">
                                                                                <label for="password" style="direction:rtl;float:right" class="pull-right">رمز عبور</label>
                                                                                <input type="password" class="form-control" id="password" name="password">
                                                                        </div>
                                                                        <input type="submit" value="ورود به RT" class="btn btn-block btn-primary" style="direction:rtl;">
                                                                </form>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>
                <script src="vendor/js/jquery-3.3.1.min.js"></script>
                <script src="vendor/js/popper.min.js"></script>
                <script src="vendor/js/bootstrap.min.js"></script>
        </body>
</html>