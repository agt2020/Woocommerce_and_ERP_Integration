<?php
	/************************************
		Rahkaran AND HamoonKP Integration
		Start Date 2020-12-13
	=====================================
		Author:	Abolfazl Ghaffari
		Mail  : agt2020@gmail.com
		Phone : 09128997081
	************************************/
	require_once ('lib/utils.php');
	// CREATE DB CONNECTION
        $db = new DB();
        $Inventories = array();
        // FETCH BY STORES
        // $query = "SELECT `ID`,`user_login`,`user_registered` FROM `wp_users` WHERE `user_login` LIKE '9%' ORDER BY ID ASC";
        // $result = $db->conn->query($query);
        // if ($result->num_rows > 0)
        // {
        //         while($row = $result->fetch_assoc())
        //         {
        //                 print_r($row);
        //                 $db->conn->query("INSERT IGNORE INTO `sg_customers`(`id`, `sg_id`, `description`, `date`) VALUES ('".$row['ID']."',NULL,NULL,'".$row['user_registered']."')");
        //                 echo '<br>';
        //         }
        // }

        $query = "SELECT * FROM `sg_customers` WHERE `description` LIKE 'incomplete' ORDER BY `id` ASC";
        $result = $db->conn->query($query);
        if ($result->num_rows > 0)
        {
                while($row = $result->fetch_assoc())
                {
                        $q = "SELECT * FROM `wp_usermeta` WHERE `user_id` = '".$row['id']."' AND meta_key = 'billing_phone' AND meta_value LIKE '09%'";
                        $res = $db->conn->query($q);
                        $ro = $res->fetch_assoc();
                        if($res->num_rows)
                        {
                                $mobile = ltrim($ro['meta_value'],'0');
                                $qq = "SELECT * FROM `wp_usermeta` WHERE `user_id` = '".$ro['user_id']."' AND meta_key = 'digits_phone'";
                                $resres = $db->conn->query($qq);
                                $roro = $resres->fetch_assoc();
                                if($resres->num_rows == 0)
                                {
                                        echo $qq.' => '.$ro['user_id'].'<br>';
                                        $qqq = "INSERT INTO `wp_usermeta`(`user_id`, `meta_key`, `meta_value`) VALUES ('".$ro['user_id']."','digits_phone','+98".$mobile."')";
                                        $db->conn->query($qqq);
                                        $qqq = "INSERT INTO `wp_usermeta`(`user_id`, `meta_key`, `meta_value`) VALUES ('".$ro['user_id']."','digt_countrycode','+98')";
                                        $db->conn->query($qqq);
                                        $qqq = "INSERT INTO `wp_usermeta`(`user_id`, `meta_key`, `meta_value`) VALUES ('".$ro['user_id']."','digits_phone_no','".$mobile."')";
                                        $db->conn->query($qqq);
                                }
                        }
                        else
                        {
                                //echo $q.' => '.$row['user_id'].'<br>';
                        }
                }
        }
        
?>