<?php
	/************************************
		Rahkaran AND HamoonKP Integration
		Start Date 2021-01-22
	=====================================
		Author:	Abolfazl Ghaffari
		Mail  : agt2020@gmail.com
		Phone : 09128997081
	************************************/
	session_start();

	if ($_SESSION['sg_session_id'] == '')
	{
		header('location: login.php');
		die();
	}

	require_once ('lib/utils.php');
        require_once ('lib/customers.php');

	$company_name = 'فروشگاه RT';

	if ($_REQUEST['action'] == "logout")
	{
		session_destroy($_SESSION['sg_session_id']);
		unset($_SESSION['sg_session_id']);
		header('location: login.php');
		die();
        }
  
        if ($_POST['manualSynchRahkaranId'] != "" && $_POST['manualSynchInput'] != "")
	{
                // CREATE DB CONNECTION
		$db = new DB();
		$query = 'SELECT * FROM sg_customers WHERE id = "'.$_POST['manualSynchInput'].'"';
                $result = $db->conn->query($query);
                $row = $result->fetch_assoc();
                if ($row['sg_id'] == '')
                {
                        $query = "UPDATE `sg_customers` SET `description` = 'Done', `date` = '".date('Y-m-d H:i:s')."', sg_id = ".$_POST['manualSynchRahkaranId']." WHERE id = '".$_POST['manualSynchInput']."'";
                        $db->conn->query($query);
                }
                header('location: customers.php');
        }
        else
        {
                session_destroy($_SESSION['sg_session_id']);
                header('location: login.php');
        }
	die();
?>