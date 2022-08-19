<?php
	/************************************
		Rahkaran AND HamoonKP Integration
		Start Date 2020-12-13
	=====================================
		Author:	Abolfazl Ghaffari
		Mail  : agt2020@gmail.com
		Phone : 09128997081
	************************************/
	require_once ('lib/products.php');
	if($_REQUEST['offset'] == '' && $_REQUEST['limit'] == '')
    {
		$_REQUEST['offset'] = 0;
      	$_REQUEST['limit'] = 1000;
    }

	if($_REQUEST['product_id'] != '')
    {
     	Products_Remaining($_REQUEST['product_id'], 0, 1);
    }
	else
    {
		Products_Remaining(null, $_REQUEST['offset'], $_REQUEST['limit']);
    }
	header('Location: products.php');
	exit;
?>