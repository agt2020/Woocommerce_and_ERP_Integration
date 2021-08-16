<?php
	/************************************
		Rahkaran AND HamoonKP Integration
		Start Date 2020-06-06
	=====================================
		Author:	Abolfazl Ghaffari
		Mail  : agt2020@gmail.com
		Phone : 09128997081
	************************************/
	error_reporting(1);
	require_once ('lib/customers.php');

	$result = Insert_Customers();

	error_log('===============CUSTOMER SCHEDULER==============> '.$result['message']);
?>