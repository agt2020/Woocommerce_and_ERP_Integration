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

	// GET INVENTORIES
	function Inventories()
	{
		// CREATE DB CONNECTION
		$db = new DB();
		$Inventories = array();
		// FETCH BY STORES
		$query = 'SELECT * FROM sg_inventories';
		$result = $db->conn->query($query);
		if ($result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{
				$Inventories[$row['id']] = $row;
			}
		}
		return $Inventories;
	}

	// GET Products Unit
	function Products_Unit()
	{
		// CREATE DB CONNECTION
		$db = new DB();
		$Products_Unit = array();
		// FETCH BY STORES
		$query = 'SELECT * FROM sg_products_units';
		$result = $db->conn->query($query);
		if ($result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{
				$Products_Unit[$row['id']] = $row;
			}
		}
		return $Products_Unit;
	}

	// GET CUSTOMER STATUS FROM SG_CUSTOMERS
	function Customer_Status()
	{
		// CREATE DB CONNECTION
		$db = new DB();
		$Customers = array();
		$Description = array('Done'=>'منتقل شده','incomplete'=>'نقص اطلاعات','Failed_To_Connect'=>'ناموق در ثبت (عدم ارتباط با سرور راهکاران)');
		// FETCH BY STORES
		$query = 'SELECT * FROM sg_customers';
		$result = $db->conn->query($query);
		if ($result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{	
				if (strrpos($row['description'], 'Failed') !== false)
				{
					$row['description'] = 'Failed_To_Connect';
				}
				$query_name = "SELECT meta_key,meta_value AS value
						  FROM wp_usermeta
						  WHERE (meta_key = 'first_name' OR meta_key = 'last_name') AND user_id = '".$row['id']."' ORDER BY meta_key ASC";
				$result_name = $db->conn->query($query_name);
				$first_name = '';
				$last_name = '';
				while ($row_name = $result_name->fetch_assoc())
				{
					if($row_name['meta_key'] == 'first_name' && $row_name['value'] != '')
					{
						$first_name = $row_name['value'];
					}
					elseif($row_name['meta_key'] == 'last_name' && $row_name['value'] != '')
					{
						$last_name = $row_name['value'];
					}
				}
				
				if ($Description[$row['description']] != '')
				{
					$row['description'] = $Description[$row['description']];
				}
				if($row['description'] == null || $row['description'] == '')
				{
					$row['description'] = 'در صف انتقال ...';
				}

				$row['name'] = '';
				if($first_name != '' || $last_name != '')
				{
					$row['name'] = $first_name.' '.$last_name;
				}
				// PHONE
				$query_phone = "SELECT meta_value AS phone
						  FROM wp_usermeta
						  WHERE meta_value IS NOT NULL AND meta_key = 'digits_phone' AND user_id = '".$row['id']."'";
				$result_phone = $db->conn->query($query_phone);
				$row_phone = $result_phone->fetch_assoc();
				$row['phone'] = str_replace('+98', '0', $row_phone['phone']);
				if ($row['name'] != '' )//&& $row['phone'] != '')
				{
					$Customers[$row['id']] = $row;
				}
			}
		}
		return $Customers;
	}

	// GET INVOICE STATUS FROM SG_INVOICES
	function Invoice_Status()
	{
		// CREATE DB CONNECTION
		$db = new DB();
		$Invoices = array();
		$Description = array('Done'=>'منتقل شده','MissMatchItems'=>'حداقل اطلاعات یکی از اقلام کامل نیست !','NoCustomer'=>'مشتری در راهکاران پیدا نشد');
		// FETCH BY STORES
		$query = 'SELECT * FROM sg_invoices';
		$result = $db->conn->query($query);
		if ($result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{	
				if (array_key_exists($row['description'], $Description))
				{
					$row['description'] = $Description[$row['description']];   
				}
				elseif($row['description'] == '' || $row['description'] == null)
				{
					$row['description'] = 'منتظر ارسال ..';
				}
				$Invoices[$row['id']] = $row;
			}
		}
		return $Invoices;
	}

	// GET PRODUCTS FROM SG_PRODUCTS_METADATA
	function Products_List()
	{
		// UNITS
		$Products_Unit = Products_Unit();
		// INVENTORIES
		$Inventories = Inventories();
		// CREATE DB CONNECTION
		$db = new DB();
		$Products = array();
		// FETCH BY STORES
		$query = 'SELECT * FROM sg_products_metadata';
		$result = $db->conn->query($query);
		if ($result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{	
				$StockQ = 'SELECT meta_id,meta_value FROM wp_postmeta WHERE meta_key = "_stock" AND post_id = "'.$row['product_id'].'"';
				$StockRes = $db->conn->query($StockQ);
				$StockRow = $StockRes->fetch_assoc();
				if($StockRow['meta_id'] != '')
				{
					$row['stock'] = $StockRow['meta_value'];
				}
				$row['sg_unit'] = $Products_Unit[$row['sg_unit']]['name'];
				$row['sg_store_id'] = $Inventories[$row['sg_store_id']]['name'];
				$Products[$row['product_id']] = $row;
			}
		}
		return $Products;
	}
	
	// GET PRODUCTS FROM SG_PRODUCTS_METADATA
	function Remove_Product_Sync($product_id)
	{
		$db = new DB();
		$query = 'DELETE FROM sg_products_metadata WHERE product_id = "'.$product_id.'"';
		$db->conn->query($query);
	}

	// GET CONFIG
	function Config($Category = null)
	{
		// CREATE DB CONNECTION
		$db = new DB();
		$Config = array();
		// FETCH BY STORES
		$query = 'SELECT * FROM sg_config';
		if($Category != null)
		{
		    $query = 'SELECT * FROM sg_config WHERE category = "'.$Category.'"';
		}
		$result = $db->conn->query($query);
		if ($result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{
				$Config[$row['category']][$row['name']] = $row['value'];
			}
		}
		return $Config;
	}
	
	// SAVE CONFIG
	function Save_Config($category,$name,$value)
	{
		// CREATE DB CONNECTION
		if($name == null|| $category == null || $value == null)
		{
			return false;    
		}

		$db = new DB();
		$query = 'SELECT * FROM sg_config WHERE category = "'.$category.'" AND name = "'.$name.'"';
		$result = $db->conn->query($query);
		$row = $result->fetch_assoc();
		if($row['name'] != '' && $row['value'] != '')
		{
			$query = 'UPDATE sg_config SET value = "'.$value.'" WHERE category = "'.$category.'" AND name = "'.$name.'"';
			$db->conn->query($query);  
		}
		else
		{
			$query = 'INSERT INTO sg_config (category, name, value) VALUES ("'.$category.'","'.$name.'","'.$value.'")';
			$db->conn->query($query); 
		}
		return true;
	}
?>