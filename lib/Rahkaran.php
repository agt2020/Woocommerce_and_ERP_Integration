<?php
	/************************************
		Author:	Abolfazl Ghaffari
		Mail  : agt2020@gmail.com
		Phone : 09128997081
	************************************/
	require_once("SystemGroup.php");
	require_once("utils.php");

	class Rahkaran
	{
		private $connection; 		// CONNECTION INFO
		public $errorMessage; 		// ERROR MESSAGE
		public $client;
		public $machineName;
		public $HTTP_STATUS_CODE;
        	public $Curl_Result;
        	public $Curl_Result_Size;
        	public $Config;

		// CONSTRUCTOR CLASS
		function __construct()
		{
			$this->client = new Curl();
			$this->connection = array();
			$this->Curl_Result = array();

			// LOCAL SERVER
			// $this->connection['URL'] = "http://localhost/sg";
			// $this->connection['USERNAME'] = "hessam";
			// $this->connection['PASSWORD'] = "Agt@agT33";

			// MAIN SERVER DETAIL

			//$this->connection['URL'] = "http://2.144.245.210:9000/hamoon";
			$this->connection['URL'] = "http://31.171.223.174:9000/hamoon";
			$this->connection['USERNAME'] = "admin";
			$this->connection['PASSWORD'] = "h09122728638hamoon";

			$this->restServiceClient = new RestServiceClient($this->connection['URL'], false);
			// RETRIEVE SETTINGS
			$this->Config = Config();
			if (sizeof($this->Config))
			{
				$this->machineName = $this->Config['Invoice']['cashiername'];
			}
		}

		// DESTRUCT CLASS
		function __destruct()
		{
			return true;
		}


		//////////////////////////////////////////
	        public function removeBomUtf8($s)
	        {
	            if(substr($s,0,3)==chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF')))
	            {
	                return substr($s,3);
	            }
	            else
	            {
	                return $s;
	            }
	        }


		// GET PRODUCTS FROM RAHKARAN
		public function Get_Products($StoreId, $PageIndex = 0, $PageSize = 100)
		{
        	// CREATE DB CONNECTION
			$db = new DB();
			try
			{
				$curl = new Curl();
				$restServiceClient = new RestServiceClient($this->connection['URL'], false);
				$sessionId = $restServiceClient->Login($curl, $this->connection['USERNAME'], $this->connection['PASSWORD'], $cookie);
                echo $restServiceClient->RetailServiceAddress."/products?storeId=".$StoreId."&from=".$PageIndex."&numberOfRecords=".$PageSize.'<br>';
				$curl->get($restServiceClient->RetailServiceAddress."/products?storeId=".$StoreId."&from=".$PageIndex."&numberOfRecords=".$PageSize);
		                $this->http_status_code = $curl->http_status_code;
		                $response = json_decode($curl->response);
		                if (json_last_error())
		                {
		                    $response = $this->removeBomUtf8($curl->response);
		                    $response = json_decode($response);
		                }

		                if ($response->metadata->isSuccessfull == 1)
		                {
		                	array_push($this->Curl_Result, $response->result);
                            echo 'Page index : '.$PageIndex.' - size : '.sizeof($response->result);
                            $prod_num_qu = 'UPDATE sg_config SET value = '.($PageIndex+sizeof($response->result)).' WHERE category = "Product" AND name = "lastFetchedPage"';
							$db->conn->query($prod_num_qu);
		                	if (sizeof($response->result) == 100)
		                	{
		                		$this->Get_Products($StoreId,$PageIndex+100);
		                	}
                            return array('result' => true, 'data' => $this->Curl_Result, 'message' => 'Done');
		                }
		                else
		                {
		                	return array('result' => false, 'data' => null, 'message' => $response->metadata->errorMessage);
		                }
			}
			catch (Exception $ex)
			{
				return false;
			}
		}

		// GET PRODUCTS CONTENT BY PRODUCT ID AND STORE ID
		public function Get_Products_Remaining($ProcutId, $StoreId = 1)
		{
			try
			{
				$curl = new Curl();
				$restServiceClient = new RestServiceClient($this->connection['URL'], false);
				$sessionId = $restServiceClient->Login($curl, $this->connection['USERNAME'], $this->connection['PASSWORD'], $cookie);
				$data = array(
		                        'productId' => $ProcutId,
		                        'storeId'=> $StoreId,
		                        'dateTime'=> date('Y-m-d').'T'.date('H:i:s').'.5198047+04:30',
		                );
				$curl->post($restServiceClient->RetailServiceAddress."/remaining", json_encode($data));
		                $this->http_status_code = $curl->http_status_code;
		                $response = json_decode($curl->response);

		                if (json_last_error())
		                {
		                    $response = $this->removeBomUtf8($curl->response);
		                    $response = json_decode($response);
		                }
		                
		                if ($response->metadata->isSuccessfull == 1)
		                {
		                	return array('result' => true, 'data' => $response->result, 'message' => 'Done');
		                }
		                else
		                {
		                	return array('result' => false, 'data' => null, 'message' => $response->metadata->errorMessage);
		                }
			}
			catch (Exception $ex)
			{
				return array('result' => false, 'data' => null, 'message' => $ex);
			}
		}
        
        	// GET PRODUCTS PRICE BY PRODUCT ID AND STORE ID
		public function Get_Products_Price($items)
		{
			try
			{
				$curl = new Curl();
				$restServiceClient = new RestServiceClient($this->connection['URL'], false);
				$sessionId = $restServiceClient->Login($curl, $this->connection['USERNAME'], $this->connection['PASSWORD'], $cookie);
				
				$data = array(
				        'customerId' => 1,
		                        'currencyId' => 1,
		                        'retailShopId'=> 6,
		                        'salesAreaId' => 1,
		                        'date'=> null,
		                        'items'=> $items,
               			);

				$curl->post($restServiceClient->RetailServiceAddress."/price", json_encode($data));
		                $response = json_decode($curl->response);

		                if (json_last_error())
		                {
		                    $response = $this->removeBomUtf8($curl->response);
		                    $response = json_decode($response);
		                }

		                if($curl->http_status_code == 200)
				{
				    return array('result' => true, 'data' => $response->result, 'message' => 'Done');
				}
				else
				{
				    return array('result' => false, 'data' => null, 'message' => $response->metadata->errorMessage);
				}
			}
			catch (Exception $ex)
			{
				return array('result' => false, 'data' => null, 'message' => $ex);
			}
		}
		
		// GET CUSTOMERS FROM RAHKARAN
		public function Get_Customers($PageIndex = 0, $PageSize = 50)
		{
			try
			{
				$curl = new Curl();
				$restServiceClient = new RestServiceClient($this->connection['URL'], false);
				$sessionId = $restServiceClient->Login($curl, $this->connection['USERNAME'], $this->connection['PASSWORD'], $cookie);

				$curl->get($restServiceClient->RetailServiceAddress."/customers?from=".$PageIndex."&numberOfRecords=".$PageSize);
                		$this->http_status_code = $curl->http_status_code;
                
		                $response = json_decode($curl->response);
		                if (json_last_error())
		                {
		                    $response = $this->removeBomUtf8($curl->response);
		                    $response = json_decode($response);
		                }

		                if ($response->metadata->isSuccessfull == 1)
		                {
		                	return array('result' => true, 'data' => $response->result, 'message' => 'Done');
		                }
		                else
		                {
		                	return array('result' => false, 'data' => null, 'message' => $response->metadata->errorMessage);
		                }
			}
			catch (Exception $ex)
			{
				return array('result' => false, 'data' => null, 'message' => $ex);
			}
		}
		
        // GET CUSTOMERS FROM RAHKARAN BY MOBILE
		public function Get_Customers_By_Mobile($Mobile)
		{
			try
			{
				$curl = new Curl();
				$restServiceClient = new RestServiceClient($this->connection['URL'], false);
				$restServiceClient->Login($curl, $this->connection['USERNAME'], $this->connection['PASSWORD'], $cookie);
				$curl->get($restServiceClient->RetailServiceAddress."/customers?mobile=".$Mobile);

		                if ($curl->http_status_code == 200)
		                {
					$response = json_decode($curl->response);
					if (json_last_error())
					{
					    $response = $this->removeBomUtf8($curl->response);
					    $response = json_decode($response);
					}
		                	return array('result' => true, 'data' => $response, 'message' => 'Done');
		                }
		                else
		                {
		                	return array('result' => false, 'data' => null, 'message' => $curl->response);
		                }
			}
			catch (Exception $ex)
			{
				return array('result' => false, 'data' => null, 'message' => $ex);
			}
		}
        
		// INSERT CUSTOMER INTO RAHKARAN
		public function Post_Customer($data = array())
		{
			$Customer = new CustomerBody();
			$CustomerAddress = new CustomerAddress();
			$CustomerAddressData = new CustomerAddressData();
			// MAIN BODY
			$Customer->ID = 0;
			$Customer->Code = 0;
			$Customer->Gender = 1;
			$Customer->FirstName = $data['first_name'];
			$Customer->LastName = $data['last_name'];
			$Customer->NationalCode = '';
			$Customer->Birthdate = '';
			$Customer->Tel = '';
			$Customer->Mobile = str_replace('+98', '0', $data['mobile']);
			$Customer->RepresenterId = 1;
			$Customer->Addresses = null;
			$Customer->Attributes = null;

			try
			{
				$curl = new Curl();
				$restServiceClient = new RestServiceClient($this->connection['URL'], false);
				$sessionId = $restServiceClient->Login($curl, $this->connection['USERNAME'], $this->connection['PASSWORD'], $cookie);
				$curl->post($restServiceClient->RetailServiceAddress."/customer", json_encode($Customer));
				if ($curl->http_status_code == 200 || $curl->http_status_code == 201)
				{
					$response = json_decode($curl->response);
			                if (json_last_error())
			                {
			                    $response = $this->removeBomUtf8($curl->response);
			                    $response = json_decode($response);
			                }

			                if ($response->metadata->isSuccessfull == 1 && $response->result != '')
			                {
		                		// INSERT ADDRESS
		                		$CustomerAddressData->id = 0;
		                		$CustomerAddressData->cityId = 1;
								$CustomerAddressData->details = $data['details'];
								$CustomerAddressData->isDefault  = true;
								$CustomerAddressData->name  = 'اصلی';
								$CustomerAddressData->phone = str_replace('+98', '0', $data['mobile']);
								$CustomerAddressData->email = $data['email'];
								$CustomerAddressData->zipcode = $data['zipcode'];
								$CustomerAddress->customerId = $response->result;
								$CustomerAddress->addressData = $CustomerAddressData;
								$curl->post($restServiceClient->RetailServiceAddress."/address", json_encode($CustomerAddress));

		                		return array('result' => true, 'data' => $response->result, 'message' => 'Done');
		                	}
				        else
				        {
                        		if ($response->metadata->errorMessage == 'یک مشتری با شماره تماس/موبایل یکسان موجود می باشد')
                                {
                                    $repeatCustomer = $this->Get_Customers_By_Mobile(str_replace('+98', '0', $data['mobile']));
                                   	if($repeatCustomer['result'] == 1)
                                    {
                                        return array('result' => true, 'data' => $repeatCustomer['data']->result[0]->id, 'message' => 'Done');
                                    }
                                }
				                return array('result' => false, 'data' => null, 'message' => $response->metadata->errorMessage);
				        }
				}
				else
				{
                	
					return array('result' => false, 'data' => null, 'message' => $curl->curl_error_message);
				}
			}
			catch (Exception $ex)
			{
				return array('result' => false, 'data' => null, 'message' => $ex);
			}
		}

		// INSERT INVOICE INTO RAHKARAN
		public function Post_Invoice($data = array())
		{
			$Invoice = array();
			$Items = array();
			// ITEMS
			if (sizeof($data['items']))
			{
				foreach ($data['items'] as $value)
				{
					$Item = array();
				        $Item['productId'] = $value['productId'];
				        $Item['unitId'] = $value['unitId'];
				        $Item['quantity'] = $value['quantity'];
				        $Item['storeId'] = $data['storeId'];
				        $Item['fee'] = $value['site_price']*10;
				        $Item['cashierDiscount'] = (int)$value['cashierDiscount']*10;
					array_push($Items, $Item);
				}
			}
			// MAIN BODY
			//$Invoice['datetime'] = $data['datetime'];
			$Invoice['customerId'] = (int)$data['customerId'];
			$Invoice['currencyId'] = $data['currencyId'];
			$Invoice['storeId'] = $data['storeId'];
			$Invoice['settlementPolicyId'] = $data['settlementPolicyId'];
			$Invoice['documentPatternId'] = $data['documentPatternId'];
			//$Invoice['cashierDiscount'] = 0;
			$Invoice['items'] = $Items;
		    	try
			{
				$curl = new Curl();
				// GET SESSION
				$restServiceClient = new RestServiceClient($this->connection['URL'], false);
				$sessionId = $restServiceClient->Login($curl, $this->connection['USERNAME'], $this->connection['PASSWORD'], $cookie);
				// CASHIER LOGIN
				$CashierLogin = array(
					"machineName" => $this->machineName,
				);
				$curl->post($restServiceClient->RetailCashRegisterManagement."/CashierLogin", json_encode($CashierLogin));
				if ($curl->http_status_code == 200)
				{
					// INSERT INVOICE
					$payments[0]["key"] = "Cash";
					$payments[0]["amount"] = ((int)$data['gross_total']*10);
                  	//$payments[0]["attr"] = (object)array();
					$body = array(
						"document" => $Invoice,
						"payments" => $payments,
					);
					$curl->post($restServiceClient->RetailServiceAddress."/Invoice", json_encode($body));
					print_r($curl);
					echo('<<<<<<<<<<<<<<<<<<<<<<<<<INVOICE>>>>>>>>>>>>>>>>>>>>>>>><br>');
					if ($curl->http_status_code == 200)
					{
						$response = json_decode($curl->response);
				                if (json_last_error())
				                {
				                    $response = $this->removeBomUtf8($curl->response);
				                    $response = json_decode($response);
				                }
				                if ($response->metadata->isSuccessfull == 1)
			                	{
			                		return array('result' => true, 'data' => $response->result->id);
			                	}
			                	else
			                	{
			                		return array('result' => false, 'data' => $response->metadata->errorMessage);
			                	}
					}
					else
					{
						if($curl->curl_error_message == '')
                        {
                        	return array('result' => false, 'data' => null, 'message' => $curl->response);
                        }
                        return array('result' => false, 'data' => null, 'message' => $curl->curl_error_message);
					}
				}
				else
				{
                	if($curl->curl_error_message == '')
                    {
                    	return array('result' => false, 'data' => null, 'message' => $curl->response);
                    }
					return array('result' => false, 'data' => null, 'message' => $curl->curl_error_message);
				}
			}
			catch (Exception $ex)
			{
				return array('result' => false, 'data' => null, 'message' => $ex);
			}
		}
	}
?>