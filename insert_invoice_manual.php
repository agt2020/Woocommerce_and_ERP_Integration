<?php
	/************************************
		Rahkaran AND HamoonKP Integration
		Start Date 2020-12-13
	=====================================
		Author:	Abolfazl Ghaffari
		Mail  : agt2020@gmail.com
		Phone : 09128997081
	************************************/
	require_once ('lib/invoices.php');
	if (Fetch_Invoices())
	{
		header('Location: invoices.php');
		exit;
	}
	header('Location: invoices.php');
	exit;
?>