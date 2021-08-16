<?php
    /************************************
        Rahkaran AND HamoonKP Integration
        Start Date 2020-06-06
    =====================================
        Author  Abolfazl Ghaffari
        Mail : agt2020@gmail.com
        Phone : 09128997081
    ************************************/
    require "phpseclib/Crypt/RSA.php";
    require "CURL.php";
	
	class SystemGroup
    {
		const BaseAddressResolverServiceRelativeAddress = "/BaseAddressResolver.svc";
        const AuthenticationServiceRelativeAddress = "/Services/Framework/AuthenticationService.svc";
        const CostCenterServiceRelativeAddress = "/Services/General/CostCenterService.svc";
		const ERA_ServiceRelativeAddress = "/System/BusinessRuleEngine/Service.svc";
        const FrameworkServiceRelativeAddress = "/Framework/Services/DataService.svc";
        const PartyManagementServiceRelativeAddress = "/Services/General/PartyManagementService.svc";
        const RetailServiceRelativeAddress = "/Retail/eSalesApi/ESalesService.svc";
       
		public $AuthenticationServiceAddress;
        public $FrameworkServiceRelativeAddress;
        public $CostCenterServiceAddress;
		public $ERA_Address;
        public $PartyManagementServiceAddress;
        public $RetailServiceAddress;

        public $URL;
        public $UserName;
        public $Password;
        public $SessionId;
		
        public $HTTP_STATUS_CODE;
        public $Curl_Result;
        public $Curl_Result_Size;

        // CONSTRUCTOR
        public function __construct()
        {
            $curl = new Curl();
            // $this->URL  = "http://2.144.245.210:9000/HamoonKP";
            // $this->UserName  = "admin";
            // $this->Password  = "@dmin";

            $this->URL  = "http://localhost/sg";
            $this->UserName  = "hessam";
            $this->Password  = "Agt@agT33";
        }

        // SYSTEM GROUP FUNCTION
        public function SystemGroup($baseWebAddress, $configureBasedOnSgVirtualPath)
        {
            try
            {
                if (!$configureBasedOnSgVirtualPath)
                {
                    $this->AuthenticationServiceAddress = $baseWebAddress . self::AuthenticationServiceRelativeAddress;      
                    $this->CostCenterServiceAddress = $baseWebAddress . self::CostCenterServiceRelativeAddress; 
                    $this->FrameworkServiceRelativeAddress = $baseWebAddress . self::FrameworkServiceRelativeAddress;   
					$this->ERA_Address = $baseWebAddress . self::ERA_ServiceRelativeAddress;
                    $this->PartyManagementServiceAddress = $baseWebAddress . self::PartyManagementServiceRelativeAddress;
                    $this->RetailServiceAddress = $baseWebAddress . self::RetailServiceRelativeAddress;
                }
                else
                {
                    $baseAddressResolverService =$baseWebAddress . self::BaseAddressResolverServiceRelativeAddress;
                    $client = new WebClient();
                    $redirectorAddress = $client.DownloadString($baseAddressResolverService . "/GetBaseAddress");				
					$baseWebAddress =substr($baseWebAddress, 0,strripos($baseWebAddress,"/"));
                    $this->AuthenticationServiceAddress = $baseWebAddress . $redirectorAddress . self::AuthenticationServiceRelativeAddress;
                    $this->CostCenterServiceAddress = $baseWebAddress . $redirectorAddress . self::CostCenterServiceRelativeAddress;
                    $this->ERA_Address = $baseWebAddress . $redirectorAddress. self::ERA_ServiceRelativeAddress;   
                }
            }
            catch (Exception $webEx)
            {
                echo "<pre>";
                echo $webEx;
                echo "</pre>";
            }
        }

        // LOGIN IN TO SYSTEM GROUP (Rahkaran)
		public function Login($client,$username, $password, &$authCookie)
        {

            $authCookie = "";
            try
            {
                $session  = $client->get($this->AuthenticationServiceAddress . "/session");
                $m = $session->rsa->M ;
                $e = $session->rsa->E ;				   
                $client->setHeader("content-type","application/json; charset=UTF-8");
                $rsa = new Crypt_RSA();
                $rsa->loadKey(array(
                        'e'=> new Math_BigInteger($e,16),
                        'n'=> new Math_BigInteger($m,16)
                    )
                );
                $sessionId =  $session->id;
                $sessionPlusPassword = $sessionId . "**" . $password;
                $rsa->setEncryptionMode(2);
                
                $encrypted = $rsa->encrypt($sessionPlusPassword);

                $hexPassword = strtoupper(bin2hex($encrypted));
                $arr = json_encode(array(
                    'sessionId' =>$sessionId ,
                    'username'=> $username,
                    'password'=>$hexPassword));              
                $loginRes = $client->post($this->AuthenticationServiceAddress . "/login", $arr);

                if($loginRes != "")
                {
                    throw new Exception($loginRes);
                }
                $this->SessionId = $session->id;
                return $session->id;
            }
            catch (Exception $webEx)
            {
				error_log("<pre>".$webEx."</pre>");
            }
        }

        // GET PRODUCTS FROM RAHKARAN
        public function Get_Products($PageIndex = 0, $PageSize = 50)
        {
            $curl = new Curl();
            $this->SystemGroup($this->URL, false);
            $this->Login($curl,$this->UserName,$this->Password,$cookie);
            if (isset($this->SessionId))
            {
                $curl->get($this->RetailServiceAddress."/products?from=".$PageIndex."&numberOfRecords=".$PageSize);
                $this->http_status_code = $curl->http_status_code;
                $response = json_decode($curl->response);
                if (json_last_error())
                {
                    $response = $this->removeBomUtf8($curl->response);
                    $response = json_decode($response);
                }
                $result = $response->result;
                if (sizeof($result))
                {
                    $this->Curl_Result[$PageIndex] = $result;
                    $this->Curl_Result_Size = $PageIndex+sizeof($result);
                    if (sizeof($result) == $PageSize)
                    {
                        $this->Get_Products($PageIndex+$PageSize);
                    }
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }

        // GET CUSTOMERS FROM RAHKARAN
        public function Get_Customers($PageIndex = 0, $PageSize = 50)
        {
            $curl = new Curl();
            $this->SystemGroup($this->URL, false);
            $this->Login($curl,$this->UserName,$this->Password,$cookie);
            if (isset($this->SessionId))
            {
                $curl->get($this->RetailServiceAddress."/customers?from=".$PageIndex."&numberOfRecords=".$PageSize);
                $this->http_status_code = $curl->http_status_code;
                $response = json_decode($curl->response);
                if (json_last_error())
                {
                    $response = $this->removeBomUtf8($curl->response);
                    $response = json_decode($response);
                }
                $result = $response->result;
                if (sizeof($result))
                {
                    $this->Curl_Result[$PageIndex] = $result;
                    $this->Curl_Result_Size = $PageIndex+sizeof($result);
                    if (sizeof($result) == $PageSize)
                    {
                        $this->Get_Products($PageIndex+$PageSize);
                    }
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }

        public function ONEWAY_CRM_AS_ACCOUNT()
        {
            $curl = new Curl();
            $this->SystemGroup($this->URL, false);
            $this->Login($curl,$this->UserName,$this->Password,$cookie);
            if (isset($this->SessionId))
            {
                $curl = new Curl();
                // $Add = new Addresses();
                // $Add->ID = 0;
                // $Add->Name = 'main';
                // $Add->CityId = '3';
                // $Add->Details = 'test';
                // $Add->ZipCode = '1196643383';
                // $Add->Phone = '09128997081';
                // $Add->Email = 'agt2020@gmail.com';
                // $Add->IsDefault  = true;

                // $Address = array($Add);
                // $Attr = array();

                // $Acc = new Account();
                // $Acc->ID = 0;
                // $Acc->Code = 123456;
                // $Acc->Gender = 1;
                // $Acc->FirstName = 'ابوالفضل';
                // $Acc->LastName = 'غفاری';
                // $Acc->NationalCode = '4870031973';
                // $Acc->Birthdate = "2020-06-20T00:49:00.5198047+04:30";
                // $Acc->Tel = '02155491311';
                // $Acc->Mobile = '09128997081';
                // $Acc->RepresenterId = 1;
                // $Acc->Addresses = $Address;
                // $Acc->Attributes = $Attr;

                // $e = new EraRequeset();
                // $e->Job = $Acc;
                // $Acc = json_encode($Acc);
                $data = json_encode(
                    array(
                        'ID' => 0,
                        'Code'=>123456,
                        'Gender'=>1,
                        'FirstName'=>'حسن',
                        'LastName'=>'محمدی',
                        'NationalCode'=>'4311182880',
                        'Birthdate'=>"1990-09-06T00:49:00.5198047+04:30",
                        'Tel'=>'02155491311',
                        'Mobile'=>'09128997081',
                        'RepresenterId'=>1,
                        'Addresses'=>array(),
                        'Attributes' => array(),
                    )
                );
                echo "<br><br><br>";
                $curl->setCookie("sg-dummy" , "-");
                $curl->setCookie("sg-auth-sg" , $this->SessionId);
                $curl->post($this->RetailServiceAddress."/customer", $data);
                $this->http_status_code = $curl->http_status_code;
                echo $this->http_status_code.' => '.$curl->error_message."<br>";
                error_log(print_r($curl,1));
                $response = json_decode($curl->response);
                if (json_last_error())
                {
                    $response = $this->removeBomUtf8($curl->response);
                    $response = json_decode($response);
                }
                $result = $response->result;
                print_r($result);
            }
        }
        public function ONEWAY_CRM_AS_ACCOUNT2()
        {
            $data = json_encode(array(
                array(
                    'CompanyName' => 'TEST',
                    'FirstName'=>'حسن',
                    'Gender'=>1,
                    'LastName'=>'محمدی 2',
                    'NationalID'=>'4311182880',
                    'Type' => 2),));

            $curl = new Curl();
            $this->SystemGroup($this->URL, false);
            $sessionId = $this->Login($curl, $this->UserName, $this->Password, $cookie);
            // CREATE PARTY
            $result = $curl->post($this->PartyManagementServiceAddress . "/GenerateParty", $data);
            error_log(print_r($curl,1));
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
	}

    class Account
    {
        public $ID;
        public $Code;
        public $Gender;
        public $FirstName;
        public $LastName;
        public $NationalCode;
        public $Birthdate;
        public $MarriageDate;
        public $Tel;
        public $Mobile;
        public $RepresenterId;
        public $Addresses;
        public $Attributes;
    }

    class Addresses
    {
        public $ID;
        public $Name;
        public $CityId;
        public $Details;
        public $ZipCode;
        public $Phone;
        public $Fax;
        public $Email;
        public $IsDefault;
    }
?>