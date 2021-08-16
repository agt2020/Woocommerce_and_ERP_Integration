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
	require_once ('lib/Rahkaran.php');
	$Rah = new Rahkaran();
	//$result = $Rah->Get_Customers_By_Mobile('09375264080');
	$data = array('first_name' => 'test', 'last_name' => 'test', 'mobile' => '09369714984');
	$result = $Rah->Post_Customer($data);
	print_r($result);
	error_log('===============CUSTOMER MOBILE SCHEDULER==============> '.$result['message']);
?>