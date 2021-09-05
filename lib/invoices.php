<?php
	/************************************
		Rahkaran AND HamoonKP Integration
		Start Date 2020-07-10
	=====================================
		Author:	Abolfazl Ghaffari
		Mail  : agt2020@gmail.com
		Phone : 09128997081
	************************************/
	require_once ('db.php');
	require_once ('Rahkaran.php');

	// INSERT INVOICE INTO RAHKARAN
	function Fetch_Invoices()
	{
		// CREATE DB CONNECTION
		$db = new DB();
		// MAKE RAHKARAN CLASS OBJECT
	    	$Rah = new Rahkaran();
	    	// GET DEFAULT INVENTORY
	    	$query = "SELECT id FROM sg_inventories WHERE is_default = '1'";
		$res = $db->conn->query($query);
		$inv = $res->fetch_assoc();
		if ($inv['id'] == '')
		{
			return false;
		}
		$inv = $inv['id'];
	    	//
	    	$ids = '';
	    	$keys = array('_variation_id' => 'p_id','_qty' => 'quantity','_line_subtotal' => 'fee');
		$sql = "SELECT id AS order_id, `date` AS date_created, customer_id, shipping AS shipping_total, gross_total FROM sg_invoices WHERE sg_id IS NULL AND `date` >= '2021-06-10 00:00:00'";
		$result = $db->conn->query($sql);
		if ($result->num_rows > 0)
		{
			$temp_invoices = array();
			while($row = $result->fetch_assoc())
			{
				if ($row['order_id'] != '')
				{
					$row['storeId'] = $inv;
					$row['currencyId'] = 1;
					$row['settlementPolicyId'] = 4;
		    		$row['documentPatternId'] = 5;
					// CUSTOMER ID
					$sql_customer = "SELECT sg_id FROM sg_customers WHERE id ='".$row['customer_id']."'";
					$result_customer = $db->conn->query($sql_customer);
					if ($result_customer->num_rows > 0)
					{
						$customer_id = $result_customer->fetch_assoc();
						$row['customerId'] = $customer_id['sg_id'];
					}
					else
					{
						$row['customerId'] = '';
					}
					$date_created = explode(' ', $row['date_created']);
				    	$row['datetime'] = $date_created[0].'T'.$date_created[1].'.5198047+04:30';
				    	unset($row['date_created']);
					// LINE ITEMS
					$sql_items = "SELECT * FROM vw_sg_invoices_items WHERE order_id ='".$row['order_id']."'";
					$result_items = $db->conn->query($sql_items);
					if ($result_items->num_rows > 0)
					{
						$row['items'] = array();
						while($row_items = $result_items->fetch_assoc())
						{
							if ($row_items['order_item_id'] != '')
							{
								$order_item_id = $row_items['order_item_id'];
								$meta_key = $row_items['meta_key'];
								$row['items'][$order_item_id][$keys[$meta_key]] = $row_items['meta_value'];

								if ($row_items['meta_key'] == '_variation_id')
								{
									$q = 'SELECT * FROM sg_products_metadata WHERE product_id = "'.$row_items['meta_value'].'"';
									$re = $db->conn->query($q);
									$ro = $re->fetch_assoc();
									if ($ro['product_id'] != '')
									{
										$row['items'][$order_item_id]['productId'] = $ro['sg_id'];
										$row['items'][$order_item_id]['storeId'] = $ro['sg_store_id'];
										$row['items'][$order_item_id]['unitId'] = $ro['sg_unit'];
									}
								}
								if($row_items['order_item_name'] != '')
								{
									$row['items'][$order_item_id]['product_name'] = $row_items['order_item_name'];
								}
							}
						}
					}
					Insert_Invoice($row);
				}
			}
		}
	}

	// INSERT NEW META
	function Insert_Invoice($Invoice)
	{
		if (sizeof($Invoice))
		{
			// CREATE DB CONNECTION
			$db = new DB();
			// MAKE RAHKARAN CLASS OBJECT
			$Rah = new Rahkaran();
			// CHECK CUSTOMER
			if ($Invoice['customerId'] == '')
        		{
        			$q = 'UPDATE sg_invoices SET description = "NoCustomer" WHERE id = "'.$Invoice['order_id'].'"';
                		$db->conn->query($q);
                		return false;
        		}
        		
    			foreach ($Invoice as $key => $value)
    			{
    				if ($key == 'items')
    				{
    					foreach ($value as $k => $v)
    					{
    						if ($v['productId'] == '')
    						{
    							$q = 'UPDATE sg_invoices SET description = "حداقل یکی از محصولات با راهکاران سینک نیست ('.$v['product_name'].')" WHERE id = "'.$Invoice['order_id'].'"';
							$db->conn->query($q);
							return false;
    						}
						elseif ($v['storeId'] == '' || $v['unitId'] == '' || $v['quantity'] == '')
    						{
							$q = 'UPDATE sg_invoices SET description = "MissMatchItems" WHERE id = "'.$Invoice['order_id'].'"';
							$db->conn->query($q);
							return false;
						}
    					}
    				}
    			}

    			foreach ($Invoice as $key => $value)
    			{
	            		if ($key == 'items')
	            		{
	            			foreach ($value as $k => $v)
	            			{
	            				$q = 'SELECT meta_value AS site_price FROM wp_postmeta WHERE meta_key = "_regular_price" AND post_id = "'.$v['p_id'].'"';
	            				$re = $db->conn->query($q);
						$ro = $re->fetch_assoc();
						$ro['site_price'] = $ro['site_price'];
						$Invoice[$key][$k]['site_price'] = (int)$ro['site_price']*(int)$v['quantity'];
						$v['cashierDiscount'] = $Invoice[$key][$k]['site_price']-(int)$v['fee'];
						$Invoice[$key][$k]['cashierDiscount'] = $v['cashierDiscount'];
    					}
    				}
    			}
	            	// SHIPPING
	            	if ($Invoice['shipping_total'] != '' && $Invoice['shipping_total'] != 0)
	            	{
	            		$ship = Shipping_Item((int)$Invoice['shipping_total']);
	            		if (sizeof($ship))
	            		{
	            			array_push($Invoice['items'], $ship);
	            		}
			}
			print_r($Invoice);
			echo '<br>===================================================<br>';
			// INSERT    
			$response = $Rah->Post_Invoice($Invoice);
			print_r($response);
			echo '<br>+++++++++++++++++++++++++++++++++++++++++++++++++++<br>';
        		if ($response['result'] && $response['data'] != '')
        		{
            			$q = 'UPDATE sg_invoices SET description = "Done", sg_id = "'.$response['data'].'" WHERE id = "'.$Invoice['order_id'].'"';
            			$db->conn->query($q);
            			return true;
        		}
        		else
        		{
            			$q = 'UPDATE sg_invoices SET description = "'.$response['data'].'" WHERE id = "'.$Invoice['order_id'].'"';
            			$db->conn->query($q);
        			return false;
        		}
		}
		return false;
	}
	// INSERT NEW META
	function Insert_Meta($user_id,$meta_key,$meta_value)
	{
		// CREATE DB CONNECTION
		$db = new DB();

		$query = 'INSERT INTO wp_usermeta(user_id, meta_key, meta_value) VALUES("'.$user_id.'","'.$meta_key.'","'.$meta_value.'")';
		$db->conn->query($query);

		// UNSET DB CONNECTION
		$db->conn->close();
	}

	// SHIPPING ITEM
	function Shipping_Item($fee)
	{
		$item = array();
		if ($fee == 0)
		{
			$item['productId'] = 6519;
		}
		elseif($fee == 13000)
		{
			$item['productId'] = 6520;
		}
		elseif($fee == 14000)
		{
			$item['productId'] = 11063;
		}
		elseif($fee == 15000)
		{
			$item['productId'] = 43519;
		}
		elseif($fee == 16000)
		{
			$item['productId'] = 6522;
		}
		else
		{
			return $item;
		}
		$item['storeId'] = 3;
		$item['unitId'] = 1;
		$item['fee'] = $fee;
		$item['site_price'] = $fee;
		$item['quantity'] = 1;
		$item['cashierDiscount'] = 0;
		return $item;
	}

	function Prepare_Invoices()
	{
		// CREATE DB CONNECTION
		$db = new DB();
		//
	    	$query = "SELECT order_id AS ID, date_created AS `date`, customer_id, shipping_total AS shipping, gross_total
		    	  FROM vw_sg_invoices
		    	  WHERE `status` = 'wc-processing' AND date_created >= '2021-06-10 00:00:00'";
		$res = $db->conn->query($query);
		while ($row = $res->fetch_assoc())
		{
			$inQuery = 'INSERT IGNORE INTO sg_invoices SET id = '.$row['ID'].', date = "'.$row['date'].'", customer_id = '.$row['customer_id'].', shipping = '.$row['shipping'].', gross_total= '.$row['gross_total'];
			$db->conn->query($inQuery);
		}
		return true;
	}

	function Temp_Invoices()
	{
		// CREATE DB CONNECTION
		$db = new DB();
		$Invoices = array();
		$keys = array('_customer_user' => 'customer_id', '_order_shipping' => 'shipping', '_order_total' => 'gross_total');
	    	//
	    	$query = "SELECT p.ID,p.post_date, m.meta_key,m.meta_value
		    	  FROM wp_posts p
		    	  LEFT JOIN wp_postmeta m
		    	  ON p.ID = m.post_id
		    	  WHERE p.post_type LIKE 'shop_order' AND (m.meta_key = '_customer_user' OR m.meta_key = '_order_shipping' OR m.meta_key = '_order_total')";
		$res = $db->conn->query($query);
		while ($row = $res->fetch_assoc())
		{
			$Invoices[$row['ID']]['date'] = $row['post_date'];
			$Invoices[$row['ID']][$keys[$row['meta_key']]] = $row['meta_value'];
		}

		if (sizeof($Invoices))
		{
			foreach ($Invoices as $key => $value)
			{
				$query = 'UPDATE sg_invoices SET `date` = "'.$value['date'].'", customer_id = '.$value['customer_id'].', shipping = '.$value['shipping'].', gross_total= '.$value['gross_total'].' WHERE id = '.$key;
				$db->conn->query($query);
			}
		}
		return true;
	}
?>