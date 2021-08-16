<?php
	/************************************
		Rahkaran AND HamoonKP Integration
		Start Date 2020-06-26
	=====================================
		Author:	Abolfazl Ghaffari
		Mail  : agt2020@gmail.com
		Phone : 09128997081
	************************************/
	require_once ('db.php');
	require_once ('Rahkaran.php');

	// INSERT NEW PRODUCT FROM RAHKARAN
	function Insert_Customers()
	{
		// CREATE DB CONNECTION
		$db = new DB();
		// MAKE RAHKARAN CLASS OBJECT
	    	$Rah = new Rahkaran();
	    	// CUSTOMER LIST
	    	$customers = array();
	    	// FETCH UNINSERTED CUSTOMER
	    	$sql = "SELECT sg.id,m.meta_key,m.meta_value
	    		FROM sg_customers sg
	    		LEFT JOIN wp_usermeta m
	    		ON sg.id = m.user_id
	    		WHERE sg.sg_id IS NULL AND (sg.description IS NULL OR sg.description NOT LIKE '%یک مشتری با شماره تماس/موبایل یکسان موجود می باشد%') AND (m.meta_key = 'wp_capabilities' OR m.meta_key = 'nickname' OR m.meta_key = 'first_name' OR m.meta_key = 'last_name' OR m.meta_key = 'billing_address_1' OR m.meta_key = 'billing_city' OR m.meta_key = 'billing_state' OR m.meta_key = 'billing_postcode' OR m.meta_key = 'billing_email' OR m.meta_key = 'digits_phone') ORDER BY sg.id DESC";
		$result = $db->conn->query($sql);
		if ($result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{
				if ($row["meta_key"] == 'digits_phone')
				{
					$row["meta_value"] = str_replace(' ', '', $row["meta_value"]);
					$row["meta_value"] = str_replace('-', '', $row["meta_value"]);
					$row["meta_value"] = str_replace('_', '', $row["meta_value"]);
					$row["meta_value"] = str_replace('(', '', $row["meta_value"]);
					$row["meta_value"] = str_replace(')', '', $row["meta_value"]);
				}
				$customers[$row['id']][$row['meta_key']] = $row['meta_value'];
			}
		}
		else
		{
			return array('result' => true, 'message' => 'ZERO CUSTOMERS FOUNDED');
		}

		// INSERT
		if (sizeof($customers))
		{
			$i = 0;
			foreach ($customers as $key => $value)
			{
				if ($value['first_name'] != '' && $value['last_name'] != '' && $value['digits_phone'] != '' && $value['wp_capabilities'] == 'a:1:{s:8:"customer";b:1;}' && ((strlen($value['digits_phone']) == 11 && substr( $value['digits_phone'], 0, 1 ) === "0") OR (strlen($value['digits_phone']) == 13 && substr( $value['digits_phone'], 0, 1 ) === "+")))
				{
					$customer = array();
					$customer['mobile'] = $value['digits_phone'];
					$customer['email'] = $value['billing_email'];
					$customer['first_name'] = $value['first_name'];
					$customer['last_name'] = $value['last_name'];
					$customer['zipcode'] = $value['billing_postcode'];
					$customer['details'] = $value['billing_address_1'];
					$result = $Rah->Post_Customer($customer);
					// IF FAILED TO CONNECT STOP
					if (strpos($result['message'], 'Failed to connect') !== false)
					{
						break;
					}
					// ELSE
					if ($result['result'])
					{
						$query = "UPDATE sg_customers
								  SET sg_id = '".$result['data']."', description = '".$result['message']."', date = '".date('Y-m-d H:i:s')."'
								  WHERE id = '".$key."'";
						$db->conn->query($query);
					}
					else
					{
						$query = "UPDATE sg_customers
								  SET description = '".$result['message']."', date = '".date('Y-m-d H:i:s')."'
								  WHERE id = '".$key."'";
						$db->conn->query($query);
					}
				}
				else
				{
					$query = "UPDATE sg_customers
							  SET description = 'incomplete'
							  WHERE id = '".$key."'";
					$db->conn->query($query);
				}
			}
			return array('result' => true, 'message' => sizeof($customers));
		}
		else
		{
			return array('result' => true, 'message' => 'ZERO CUSTOMERS INSERTED');
		}
	}

	// INSERT NEW META FOR CUSTOMER
	function Insert_Customer_Meta($user_id,$meta_key,$meta_value)
	{
		// CREATE DB CONNECTION
		$db = new DB();

		$query = 'INSERT INTO wp_usermeta(user_id, meta_key, meta_value) VALUES("'.$user_id.'","'.$meta_key.'","'.$meta_value.'")';
		$db->conn->query($query);

		// UNSET DB CONNECTION
		$db->conn->close();
	}

	// GET CUSTOMER
	function Get_Customer($PageIndex = 0)
	{
		// CREATE DB CONNECTION
		$db = new DB();
		// MAKE RAHKARAN CLASS OBJECT
		$Rah = new Rahkaran();
		$result = $Rah->Get_Customers_By_Mobile($PageIndex);
		if ($result['result'])
		{
			foreach ($result['data'] as $key => $value)
			{
				error_log($value->ID.' : '.$value->Mobile);
			}
			if (sizeof($result['data']) == 50 )
			{
				Get_Customer($PageIndex+50);
			}
		}
	}
?>