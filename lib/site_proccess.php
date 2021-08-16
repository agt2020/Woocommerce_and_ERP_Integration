<?php
	/************************************
		Rahkaran AND HamoonKP Integration
		Start Date 2021-07-29
	=====================================
		Author:	Abolfazl Ghaffari
		Mail  : agt2020@gmail.com
		Phone : 09128997081
	************************************/
	session_start();
	if ($_SESSION['sg_session_id'] == '')
	{
		header('location: ../login.php');
		die();
	}

	require_once ('utils.php');

	if($_REQUEST['type'] == 'remove_product_synch' && $_REQUEST['product_id'] != '')
	{
		Remove_Product_Sync($_REQUEST['product_id']);
		header('Location: ../products.php');
		exit;
	}
?>