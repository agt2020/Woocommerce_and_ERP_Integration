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

	// INSERT NEW PRODUCT FROM RAHKARAN
	function Insert_Products()
	{
		// CREATE DB CONNECTION
		$db = new DB();
		// MAKE RAHKARAN CLASS OBJECT
	    $Rah = new Rahkaran();

	    if ($db->db_error == '')
		{
		    $result = $Rah->Get_Products();
		    if ($result['result'] == true)
			{
				if (sizeof($result['data']))
				{
					$New_Products = array();
					foreach ($result['data'] as $key => $value)
					{
						foreach ($value as $k => $v)
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
									$query = "SELECT ID
											  FROM wp_posts
											  WHERE ID = ".$v->specification;
									$result = $db->conn->query($query);
									$row = $result->fetch_assoc();
									if ($row["ID"] != '')
									{
										$query = "SELECT meta_id,meta_key
												  FROM wp_postmeta
												  WHERE meta_key = 'sg_id' AND post_id = ".$v->specification;
										$result = $db->conn->query($query);
										$row = $result->fetch_assoc();
										if ($row["meta_id"] == '')
										{
											// INSERT SG ID AND SG CODE AS META FOR PRODUCTS
											Insert_Product_Meta($v->specification,'sg_id',$v->id);
											Insert_Product_Meta($v->specification,'sg_code',$v->code);
										}
									}
								}
								else
								{
									$explode_name = explode('-', $v->name);
									$query = "SELECT ID,post_title,post_status,post_parent,post_type
											  FROM wp_posts
											  WHERE post_parent = '0' AND post_title = '".$explode_name[0]."'";
									$result = $db->conn->query($query);
									$row = $result->fetch_assoc();

									if ($row["ID"] == '')
									{
										if (!isset($New_Products[$explode_name[0]]))
										{
											$New_Products[$explode_name[0]] = array();
										}
										array_push($New_Products[$explode_name[0]], $v);
									}
								}
							}
						}
					}
					// INSERT NEW PRODUCTS
					if (sizeof($New_Products))
					{
						foreach ($New_Products as $key => $value)
						{
							$post_parent = Insert_Product($key,1);
							if (sizeof($value) && $post_parent != '')
							{
								foreach ($value as $num => $data)
								{
									$data->post_parent = $post_parent;
									$post_id = Insert_Product($data);
								}
							}
						}
					}
				}
				else
				{
					// UNSET DB CONNECTION
					$db->conn->close();
					return array('result' => false, 'message' => 'No Data');
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
	function Products_Remaining()
	{
		// CREATE DB CONNECTION
		$db = new DB();
		// MAKE RAHKARAN CLASS OBJECT
	    $Rah = new Rahkaran();

		$query = 'SELECT p.ID,p.post_title,m.meta_key,m.meta_value
				  FROM wp_posts p
				  LEFT JOIN wp_postmeta m
				  ON p.ID = m.post_id
				  WHERE p.post_title <> "" AND p.post_type = "product_variation" AND p.post_status = "publish" AND m.meta_key = "sg_id" AND m.meta_value IS NOT NULL';
		$result = $db->conn->query($query);
		if ($result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{
				$Content = $Rah->Get_Products_Remaining($row['meta_value'], 3);
				if ($Content['result'] == true)
				{
					Update_Product_Meta($row['ID'],'_stock',$Content['data']);
					if ($Content['data'] > 0)
					{
						Update_Product_Meta($row['ID'],'_stock_status','instock');
					}
					else
					{
						Update_Product_Meta($row['ID'],'_stock_status','outofstock');
					}
				}
			}
		}

		// UNSET DB CONNECTION
		$db->conn->close();
		return true;
	}

	// INSERT PRODUCTS
	function Insert_Product($product,$Is_Parent = 0)
	{
		// CREATE DB CONNECTION
		$db = new DB();

		if ($Is_Parent)
		{
			$query = 'INSERT INTO wp_posts(post_author, post_date, post_date_gmt, post_title, post_status, comment_status, ping_status, post_name, post_modified, post_modified_gmt, post_parent, menu_order, post_type, comment_count) VALUES (4018,"'.date('Y-m-d H:i:s').'","'.gmdate('Y-m-d H:i:s').'","'.$product.'","draft","closed","closed","'.urlencode(str_replace(' ', '-', $product)).'","'.date('Y-m-d H:i:s').'","'.gmdate('Y-m-d H:i:s').'",0,0,"product",0)';
			$db->conn->query($query);
			$query_id = 'SELECT LAST_INSERT_ID() AS ID;';
			$result = $db->conn->query($query_id);
			$row = $result->fetch_assoc();
			$id = $row['ID'];
			if ($id != '')
			{
				Insert_Product_Meta($id,'_default_attributes','a:1:{s:7:"pa_size";s:1:"l";}');
				$q = "INSERT INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) VALUES ('".$id."', '4', '0')";
				$db->conn->query($query_id);
			}
		}
		else
		{
			$explode_name = explode('-', $product->name);

			$parent_name = $explode_name[0];
			$color = $explode_name[1];
			$size = $explode_name[2];
			$post_excerpt = 'رنگ: '.$color.', سایز: '.$size;
			$color_array = array('مشکی' => 'black','سبز روشن' => 'light-green','آبی' => 'blue','سفید' => 'white','خاکستری' => 'grey','' => 'sliver','زرد' => 'yellow','فیروزه ای' => 'cyan');

			$query = 'INSERT INTO wp_posts(post_author, post_date, post_date_gmt, post_title, post_excerpt, post_status, comment_status, ping_status, post_name, post_modified, post_modified_gmt, post_parent, menu_order, post_type, comment_count) VALUES (4018,"'.date('Y-m-d H:i:s').'","'.gmdate('Y-m-d H:i:s').'","'.$product->name.'", "'.$post_excerpt.'","draft","closed","closed","'.urlencode(str_replace(' ', '-', $product->name)).'","'.date('Y-m-d H:i:s').'","'.gmdate('Y-m-d H:i:s').'","'.$product->post_parent.'",1,"product_variation",0)';
			$db->conn->query($query);
			$query_id = 'SELECT LAST_INSERT_ID() AS ID;';
			$result = $db->conn->query($query_id);
			$row = $result->fetch_assoc();
			$id = $row['ID'];

			if ($id != '' )
			{
				if (Color($color))
				{
					$color = Color($color);
				}
				else
				{
					$color = urlencode($color);
				}

				Insert_Product_Meta($id,'fb_product_description','');
				Insert_Product_Meta($id,'fb_visibility','1');
				Insert_Product_Meta($id,'_backorders','no');
				Insert_Product_Meta($id,'_variation_description','');
				Insert_Product_Meta($id,'_manage_stock','no');
				Insert_Product_Meta($id,'_wc_review_count',0);
				Insert_Product_Meta($id,'_wc_average_rating',0);
				Insert_Product_Meta($id,'total_sales',0);
				Insert_Product_Meta($id,'_sku',$id);
				Insert_Product_Meta($id,'_stock',0);
				Insert_Product_Meta($id,'_stock_status','instock');
				Insert_Product_Meta($id,'_tax_status','taxable');
				Insert_Product_Meta($id,'_tax_class','parent');
				Insert_Product_Meta($id,'sg_id',$product->id);
				Insert_Product_Meta($id,'sg_code',$product->code);
				Insert_Product_Meta($id,'attribute_pa_color',$color);
				Insert_Product_Meta($id,'attribute_pa_size',strtolower($size));

				$up_query = 'UPDATE wp_posts SET guid = "https://rtonlinestore.com/?post_type=product_variation&p='.$id.'" WHERE ID = "'.$id.'"';
				$db->conn->query($up_query);
			}
		}
		// UNSET DB CONNECTION
		$db->conn->close();
		return $id;
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