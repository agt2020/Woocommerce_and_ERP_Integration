<?php
	/************************************
		Rahkaran AND HamoonKP Integration
		Start Date 2020-06-20
	=====================================
		Author:	Abolfazl Ghaffari
		Mail  : agt2020@gmail.com
		Phone : 09128997081
	************************************/
	require_once ('db.php');
	require_once ('Rahkaran.php');

	// FETCH PRODUCTS BY STORE
	function Products()
	{
		// CREATE DB CONNECTION
		$db = new DB();
		// FETCH BY STORES
		if ($db->db_error == '')
		{
			$query = 'SELECT id FROM sg_inventories WHERE is_default = 1';
			$result = $db->conn->query($query);
			$row = $result->fetch_assoc();
			if ($row['id'] != '')
			{
				return Insert_Products($row['id']);
			}
			else
			{
				return 'انبار پیش فرض مشخص نیست !';
			}
		}
		else
		{
			return $db->db_error;
		}
	}

	// INSERT NEW PRODUCT FROM RAHKARAN
	function Insert_Products($storeId = 1)
	{
		// CREATE DB CONNECTION
		$db = new DB();
		// MAKE RAHKARAN CLASS OBJECT
	    $Rah = new Rahkaran();
		// LOAD UNITS
		$units = array();
		$query = 'SELECT * FROM sg_products_units';
		$res = $db->conn->query($query);
		if ($res->num_rows > 0)
		{
			while($row = $res->fetch_assoc())
			{
				$units[$row['name']] = $row['id'];
			}
		}
        
	    if ($db->db_error == '')
		{
			$PageIndex = 0;
			$prod_num_qu = 'SELECT value AS PageIndex FROM sg_config WHERE category = "Product" AND name = "lastFetchedPage"';
			$prod_num_res = $db->conn->query($prod_num_qu);
			$prod_num_row = $prod_num_res->fetch_assoc();
			if($prod_num_row['PageIndex'] != '')
			{
				$PageIndex = (int)$prod_num_row['PageIndex'];
			}
		    $result = $Rah->Get_Products($storeId,$PageIndex);
		    if ($result['result'] && sizeof($result['data']))
			{
				foreach ($result['data'] as $value)
				{
					foreach ($value as $v)
					{
						// SKEEP PRIDUCT
						if (strpos($v->specification, '1-') !== false)
						{
							continue;
						}
						else
						{
							if ($v->specification != '')
							{									
								if (sizeof($v->units))
								{
									foreach ($v->units as $key_unit => $value_unit)
									{
										if ($value_unit->isDefault == 1)
										{
											$v->unit_id = $units[$value_unit->name];
											$v->unit_name = $value_unit->name;
											$v->unit_number = $value_unit->id;
										}
									}
									unset($v->units);
								}
								$up = 'INSERT INTO sg_products_metadata(product_id, sg_name, sg_id, sg_code, sg_store_id, sg_unit) VALUES ("'.$v->specification.'","'.$v->name.'","'.$v->id.'","'.$v->code.'","'.$storeId.'","'.$v->unit_id.'") ON DUPLICATE KEY UPDATE sg_name = "'.$v->name.'", sg_id = "'.$v->id.'", sg_code = "'.$v->code.'", sg_store_id = "'.$storeId.'", sg_unit = "'.$v->unit_id.'"';
								$db->conn->query($up);
							}
						}
					}
				}
			}
			else
			{
				// UNSET DB CONNECTION
				$db->conn->close();
				return array('result' => false, 'message' => $result['message']);
			}
		}
		else
		{
			// UNSET DB CONNECTION
			$db->conn->close();
			return array('result' => false, 'message' => $db->db_error);
		}
	}

	// UPDATE PRODUCTS STOCK FROM RAHKARAN
	function Products_Remaining($product_id = null, $Offset = 0, $Limit = 1000)
	{
		// CREATE DB CONNECTION
		$db = new DB();
		// MAKE RAHKARAN CLASS OBJECT
	    $Rah = new Rahkaran();
      
		$query = 'SELECT product_id,sg_id FROM sg_products_metadata WHERE sg_id IS NOT NULL ORDER BY sg_id DESC LIMIT '.$Offset.', '.$Limit;
		if($product_id != null)
		{
			$query = 'SELECT product_id,sg_id FROM sg_products_metadata WHERE sg_id = "'.$product_id.'"';
		}
		$result = $db->conn->query($query);
		if ($result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{
				$Content = $Rah->Get_Products_Remaining($row['sg_id'], 3);
				if ($Content['result'] == true)
				{
					Update_Product_Meta($row['product_id'],'_stock',(string)$Content['data'].".000000");
					if ($Content['data'] > 0)
					{
						Update_Product_Meta($row['product_id'],'_stock_status','instock');
					}
					else
					{
						Update_Product_Meta($row['product_id'],'_stock_status','outofstock');
					}
				}
			}
		}
		// UNSET DB CONNECTION
		$db->conn->close();
		return true;
	}
	
	// GET PRODUCT LIST PRICE
	function Products_Price($limit = 0)
	{
		// CREATE DB CONNECTION
		$db = new DB();
		// MAKE RAHKARAN CLASS OBJECT
	    	$Rah = new Rahkaran();
		// VARIABLES
		$items = array();
		$site_price = array();
		$list = array();
		$i = 0;
		// FETCH ITEMS
		$query = 'SELECT spm.product_id, spm.sg_id, spm.sg_unit, wp.meta_value AS price 
			  FROM sg_products_metadata spm 
			  LEFT JOIN wp_postmeta wp ON spm.product_id = wp.post_id 
			  WHERE spm.sg_id IS NOT NULL AND spm.sg_unit IS NOT NULL AND wp.meta_key = "_regular_price" 
			  LIMIT '.$limit.',50';
		$res = $db->conn->query($query);
		if ($res->num_rows > 0)
		{
			while($row = $res->fetch_assoc())
			{
				$items[$i] = array(
					'itemId' => $row['sg_id'],
					'productId' => $row['sg_id'],
					'unitId' => $row['sg_unit'],
					'quantity' => 1,
				);
				$list[$row['sg_id']] = $row['product_id'];
				$site_price[$row['sg_id']]['product_id'] = $row['product_id'];
				$site_price[$row['sg_id']]['price'] = $row['price'];
				$i++;
			}
		}
		$result = $Rah->Get_Products_Price($items);
      	print_r($result);
		if(sizeof($result['data']))
		{
			foreach($result['data'] as $key => $value)
			{
				$q = 'SELECT meta_value FROM wp_postmeta WHERE meta_key = "_regular_price" AND post_id = "'.$list[$value->itemId].'"';
				$r = $db->conn->query($q);
				$ro = $r->fetch_assoc();
				$price = ($value->fee/10); // RIAL TO TOOMAN
				if($ro['meta_value'] != $price && $price != '')
				{
					$uq = 'UPDATE wp_postmeta SET meta_value = "'.$price.'" WHERE meta_key = "_regular_price" AND post_id = "'.$list[$value->itemId].'"';
					$db->conn->query($uq);
				}
			}
		}
		if($i == 50)
		{
		    Products_Price($limit+50);
		}
	}

	// INSERT NEW META FOR PRODUCTS
	function Insert_Product_Meta($post_id,$meta_key,$meta_value)
	{
		// CREATE DB CONNECTION
		$db = new DB();

		$query = 'INSERT INTO wp_postmeta(post_id, meta_key, meta_value) VALUES("'.$post_id.'","'.$meta_key.'","'.$meta_value.'")';
		$db->conn->query($query);

		// UNSET DB CONNECTION
		$db->conn->close();
	}

	// UPDATE EXIST PRODUCT METTA
	function Update_Product_Meta($post_id,$meta_key,$meta_value)
	{
		// CREATE DB CONNECTION
		$db = new DB();

		$query = 'UPDATE wp_postmeta SET meta_value = "'.$meta_value.'" WHERE post_id = "'.$post_id.'" AND  meta_key = "'.$meta_key.'"';		
		$db->conn->query($query);

		// UNSET DB CONNECTION
		$db->conn->close();
	}


	// COLOR 
	function Color($color)
	{
		$color_array = 
			array(
				"مشکی" => "black",
				"آبی" => "blue",
				"سبز روشن" => "light-green",
				"قرمز" => "red",
				"صورتی" => "pink",
				"آبی روشن" => "cyan",
				"قهوه ای" => "brown",
				"خاکستری" => "grey",
				"زرد" => "yellow",
				"سفید" => "white",
				"بنفش" => "purple",
				"زرشکی" => "maroon",
				"گلبهی" => "%da%af%d9%84%d8%a8%d9%87%db%8c",
				"طوسی ملانژ" => "%d8%b7%d9%88%d8%b3%db%8c-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"سرمه ای" => "navy-blue",
				"زرشکی-دودی" => "%d8%b2%d8%b1%d8%b4%da%a9%db%8c-%d8%af%d9%88%d8%af%db%8c",
				"زرشکی-سرمه ای" => "%d8%b2%d8%b1%d8%b4%da%a9%db%8c-%d8%b3%d8%b1%d9%85%d9%87-%d8%a7%db%8c",
				"سبز-سرمه ای" => "%d8%b3%d8%a8%d8%b2-%d8%b3%d8%b1%d9%85%d9%87-%d8%a7%db%8c",
				"سبز-ملانژ" => "%d8%b3%d8%a8%d8%b2-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"سفید-زرشکی" => "%d8%b3%d9%81%db%8c%d8%af-%d8%b2%d8%b1%d8%b4%da%a9%db%8c",
				"سفید-سدری" => "%d8%b3%d9%81%db%8c%d8%af-%d8%b3%d8%af%d8%b1%db%8c",
				"سفید-سرمه ای" => "%d8%b3%d9%81%db%8c%d8%af-%d8%b3%d8%b1%d9%85%d9%87-%d8%a7%db%8c",
				"سفید-مشکی" => "%d8%b3%d9%81%db%8c%d8%af-%d9%85%d8%b4%da%a9%db%8c",
				"صورتی-سرمه ای" => "%d8%b5%d9%88%d8%b1%d8%aa%db%8c-%d8%b3%d8%b1%d9%85%d9%87-%d8%a7%db%8c",
				"صورتی-ملانژ" => "%d8%b5%d9%88%d8%b1%d8%aa%db%8c-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"گلبهی-سرمه ای" => "%da%af%d9%84%d8%a8%d9%87%db%8c-%d8%b3%d8%b1%d9%85%d9%87-%d8%a7%db%8c",
				"سرمه ای-گلبهی" => "%d8%b3%d8%b1%d9%85%d9%87-%d8%a7%db%8c-%da%af%d9%84%d8%a8%d9%87%db%8c",
				"سرمه ای-سفید" => "%d8%b3%d8%b1%d9%85%d9%87-%d8%a7%db%8c-%d8%b3%d9%81%db%8c%d8%af",
				"سرمه ای-صورتی" => "%d8%b3%d8%b1%d9%85%d9%87-%d8%a7%db%8c-%d8%b5%d9%88%d8%b1%d8%aa%db%8c",
				"خاکستری ملانژ" => "%d8%ae%d8%a7%da%a9%d8%b3%d8%aa%d8%b1%db%8c-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"شیری" => "%d8%b4%db%8c%d8%b1%db%8c",
				"سبز یشمی" => "%d8%b3%d8%a8%d8%b2-%db%8c%d8%b4%d9%85%db%8c",
				"کرم" => "%da%a9%d8%b1%d9%85",
				"سبز کدر" => "%d8%b3%d8%a8%d8%b2-%da%a9%d8%af%d8%b1",
				"سبز" => "green",
				"نارنجی" => "%d9%86%d8%a7%d8%b1%d9%86%d8%ac%db%8c",
				"سدری" => "%d8%b3%d8%af%d8%b1%db%8c",
				"بادمجانی" => "%d8%a8%d8%a7%d8%af%d9%85%d8%ac%d8%a7%d9%86%db%8c",
				"لیمویی" => "%d9%84%db%8c%d9%85%d9%88%db%8c%db%8c",
				"صورتی چرک" => "%d8%b5%d9%88%d8%b1%d8%aa%db%8c-%da%86%d8%b1%da%a9",
				"فیروزه ای" => "%d9%81%db%8c%d8%b1%d9%88%d8%b2%d9%87-%d8%a7%db%8c",
				"قرمز نارنجی" => "%d9%82%d8%b1%d9%85%d8%b2-%d9%86%d8%a7%d8%b1%d9%86%d8%ac%db%8c",
				"طوسی" => "%d8%b7%d9%88%d8%b3%db%8c",
				"فیلی" => "%d9%81%db%8c%d9%84%db%8c",
				"سفید-صورتی" => "%d8%b3%d9%81%db%8c%d8%af-%d8%b5%d9%88%d8%b1%d8%aa%db%8c",
				"سرمه ای ملانژ" => "%d8%b3%d8%b1%d9%85%d9%87-%d8%a7%db%8c-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"فیلی-ملانژ" => "%d9%81%db%8c%d9%84%db%8c-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"قهوه ای-ملانژ" => "%d9%82%d9%87%d9%88%d9%87-%d8%a7%db%8c-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"زرشکی ملانژ" => "%d8%b2%d8%b1%d8%b4%da%a9%db%8c-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"آبی نفتی" => "%d8%a2%d8%a8%db%8c-%d9%86%d9%81%d8%aa%db%8c",
				"سرخابی" => "%d8%b3%d8%b1%d8%ae%d8%a7%d8%a8%db%8c",
				"صورتی روشن-ملانژ" => "%d8%b5%d9%88%d8%b1%d8%aa%db%8c-%d8%b1%d9%88%d8%b4%d9%86-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"صورتی تیره-ملانژ" => "%d8%b5%d9%88%d8%b1%d8%aa%db%8c-%d8%aa%db%8c%d8%b1%d9%87-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"زرد-ملانژ" => "%d8%b2%d8%b1%d8%af-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"زرد لیمویی" => "%d8%b2%d8%b1%d8%af-%d9%84%db%8c%d9%85%d9%88%db%8c%db%8c",
				"گلبهی تیره-ملانژ" => "%da%af%d9%84%d8%a8%d9%87%db%8c-%d8%aa%db%8c%d8%b1%d9%87-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"گلبهی روشن ملانژ" => "%da%af%d9%84%d8%a8%d9%87%db%8c-%d9%85%d9%84%d8%a7%d9%86%da%98-2",
				"سفید ملانژ" => "%d8%b3%d9%81%db%8c%d8%af-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"سبز ملانژ" => "%d8%b3%d8%a8%d8%b2-%d9%85%d9%84%d8%a7%d9%86%da%98-2",
				"سدری ملانژ" => "%d8%b3%d8%af%d8%b1%db%8c-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"خردلی" => "%d8%ae%d8%b1%d8%af%d9%84%db%8c",
				"گلبهی-مشکی" => "%da%af%d9%84%d8%a8%d9%87%db%8c-%d9%85%d8%b4%da%a9%db%8c",
				"گلبهی تیره" => "%da%af%d9%84%d8%a8%d9%87%db%8c-%d8%aa%db%8c%d8%b1%d9%87",
				"آجری" => "%d8%a2%d8%ac%d8%b1%db%8c",
				"کالباسی" => "%da%a9%d8%a7%d9%84%d8%a8%d8%a7%d8%b3%db%8c",
				"قرمز جگری" => "%d9%82%d8%b1%d9%85%d8%b2-%d8%ac%da%af%d8%b1%db%8c",
				"ارغوانی" => "%d8%a7%d8%b1%d8%ba%d9%88%d8%a7%d9%86%db%8c",
				"سرمه ای پهن-صورتی" => "%d8%b3%d8%b1%d9%85%d9%87-%d8%a7%db%8c-%d9%be%d9%87%d9%86-%d8%b5%d9%88%d8%b1%d8%aa%db%8c",
				"صورتی پهن-سرمه ای" => "%d8%b5%d9%88%d8%b1%d8%aa%db%8c-%d9%be%d9%87%d9%86-%d8%b3%d8%b1%d9%85%d9%87-%d8%a7%db%8c",
				"سبز پسته ای" => "%d8%b3%d8%a8%d8%b2-%d9%be%d8%b3%d8%aa%d9%87-%d8%a7%db%8c",
				"آبی چرک" => "%d8%a2%d8%a8%db%8c-%da%86%d8%b1%da%a9",
				"آلبالویی" => "%d8%a2%d9%84%d8%a8%d8%a7%d9%84%d9%88%db%8c%db%8c",
				"بنفش چرک" => "%d8%a8%d9%86%d9%81%d8%b4-%da%86%d8%b1%da%a9",
				"سبز آبی روشن" => "%d8%b3%d8%a8%d8%b2-%d8%a2%d8%a8%db%8c-%d8%b1%d9%88%d8%b4%d9%86",
				"کله غازی" => "%da%a9%d9%84%d9%87-%d8%ba%d8%a7%d8%b2%db%8c",
				"خاکی" => "%d8%ae%d8%a7%da%a9%db%8c",
				"خاکستری تیره" => "%d8%ae%d8%a7%da%a9%d8%b3%d8%aa%d8%b1%db%8c-%d8%aa%db%8c%d8%b1%d9%87",
				"سرمه ای تیره" => "%d8%b3%d8%b1%d9%85%d9%87-%d8%a7%db%8c-%d8%aa%db%8c%d8%b1%d9%87",
				"نوک مدادی" => "%d9%86%d9%88%da%a9-%d9%85%d8%af%d8%a7%d8%af%db%8c",
				"عاجی" => "%d8%b9%d8%a7%d8%ac%db%8c",
				"مشکی(قرمز)" => "%d9%85%d8%b4%da%a9%db%8c%d9%82%d8%b1%d9%85%d8%b2",
				"مشکی(سبز)" => "%d9%85%d8%b4%da%a9%db%8c%d8%b3%d8%a8%d8%b2",
				"مشکی(صورتی)" => "%d9%85%d8%b4%da%a9%db%8c%d8%b5%d9%88%d8%b1%d8%aa%db%8c",
				"مشکی(زرد)" => "%d9%85%d8%b4%da%a9%db%8c%d8%b2%d8%b1%d8%af",
				"نیلی" => "%d9%86%db%8c%d9%84%db%8c",
				"دودی" => "%d8%af%d9%88%d8%af%db%8c",
				"سرمه ای باریک" => "%d8%b3%d8%b1%d9%85%d9%87-%d8%a7%db%8c-%d8%a8%d8%a7%d8%b1%db%8c%da%a9",
				"سرمه ای پهن" => "%d8%b3%d8%b1%d9%85%d9%87-%d8%a7%db%8c-%d9%be%d9%87%d9%86",
				"آبی ملانژ" => "%d8%a2%d8%a8%db%8c-%d9%85%d9%84%d8%a7%d9%86%da%98",
				"آبی کبریتی" => "%d8%a2%d8%a8%db%8c-%da%a9%d8%a8%d8%b1%db%8c%d8%aa%db%8c",
				"ماشی" => "%d9%85%d8%a7%d8%b4%db%8c",
				"زیتونی سیر" => "%d8%b2%db%8c%d8%aa%d9%88%d9%86%db%8c-%d8%b3%db%8c%d8%b1",
				"آبی آسمانی" => "%d8%a2%d8%a8%db%8c-%d8%a2%d8%b3%d9%85%d8%a7%d9%86%db%8c",
				"یاسی" => "%db%8c%d8%a7%d8%b3%db%8c",
				"هلویی" => "%d9%87%d9%84%d9%88%db%8c%db%8c",
				"یشمی محو" => "%db%8c%d8%b4%d9%85%db%8c-%d9%85%d8%ad%d9%88",
				"سبز ارتشی" => "%d8%b3%d8%a8%d8%b2-%d8%a7%d8%b1%d8%aa%d8%b4%db%8c",
				"زیتونی" => "%d8%b2%db%8c%d8%aa%d9%88%d9%86%db%8c"
			);

		if (array_key_exists($color, $color_array))
		{
			return $color_array[$color];
		}
		else
		{
			return false;
		}
	}
?>