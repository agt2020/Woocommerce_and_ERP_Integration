<?php
	/************************************
		Author:	Abolfazl Ghaffari
		Mail  : agt2020@gmail.com
		Phone : 09128997081
	************************************/
	require_once "phpseclib/Crypt/RSA.php";
	require_once "CURL.php";

	class RestServiceClient
	{
		const BaseAddressResolverServiceRelativeAddress = "/BaseAddressResolver.svc";
		const AddressManagementServiceRelativeAddress = "/General/AddressManagement/Services/AddressManagementWebService.svc";
		const CurrencyManagementServiceRelativeAddress = "/General/CurrencyManagement/Services/CurrencyWebService.svc";
		const AuthenticationServiceRelativeAddress = "/Services/Framework/AuthenticationService.svc";
		const CostCenterServiceRelativeAddress = "/Services/General/CostCenterService.svc";
		const PartyManagementServiceRelativeAddress = "/Services/General/PartyManagementService.svc";
		const COAManagementServiceRelativeAddress = "/Services/Financial/COAManagementService.svc";
		const OrderManagementServiceRelativeAddress = "/Services/Sales/OrderManagementService.svc";
		const FrameworkServiceRelativeAddress = "/Framework/Services/DataService.svc";
		const ERA_ServiceRelativeAddress = "/System/BusinessRuleEngine/Service.svc";
		const VoucherManagementService = "/Services/Financial/VoucherManagementService.svc";
		const ReceiptAndPaymentAddress = "/ReceiptAndPayment/ReceiptManagement/Services/ReceiptManagementService.svc";
		const RetailServiceRelativeAddress = "/Services/Retail/ESales.svc";
		const RetailCashRegisterManagement = "/Retail/CashRegisterManagement/Services/RetailAuthenticationServices.svc";

		public $AuthenticationServiceAddress;
		public $CostCenterServiceAddress;
		public $PartyManagementServiceAddress;
		public $COAManagementServiceAddress;
		public $OrderManagementServiceAddress;
		public $AddressManagementServiceAddress;
		public $FrameworkServiceRelativeAddress;
		public $VoucherManagementService;
		public $ReceiptAndPaymentAddress;
		public $RetailServiceAddress;
		public $ERA_Address;
		public $RetailCashRegisterManagement;


		public function RestServiceClient($baseWebAddress, $configureBasedOnSgVirtualPath)
		{
			try
			{
				if (!$configureBasedOnSgVirtualPath)
				{
					$this->AuthenticationServiceAddress = $baseWebAddress . self::AuthenticationServiceRelativeAddress;
					$this->CostCenterServiceAddress = $baseWebAddress . self::CostCenterServiceRelativeAddress;
					$this->PartyManagementServiceAddress = $baseWebAddress . self::PartyManagementServiceRelativeAddress;
					$this->COAManagementServiceAddress = $baseWebAddress . self::COAManagementServiceRelativeAddress;
					$this->OrderManagementServiceAddress = $baseWebAddress . self::OrderManagementServiceRelativeAddress;
					$this->AddressManagementServiceAddress = $baseWebAddress . self::AddressManagementServiceRelativeAddress;
					$this->CurrencyManagementServiceAddress = $baseWebAddress . self::CurrencyManagementServiceRelativeAddress;
					$this->FrameworkServiceRelativeAddress = $baseWebAddress . self::FrameworkServiceRelativeAddress;
					$this->ERA_Address = $baseWebAddress . self::ERA_ServiceRelativeAddress;
					$this->VoucherManagementService = $baseWebAddress . self::VoucherManagementService;
					$this->ReceiptAndPaymentAddress = $baseWebAddress . self::ReceiptAndPaymentAddress;
					$this->RetailServiceAddress = $baseWebAddress . self::RetailServiceRelativeAddress;
					$this->RetailCashRegisterManagement = $baseWebAddress . self::RetailCashRegisterManagement;
				}
				else
				{
					$baseAddressResolverService = $baseWebAddress . self::BaseAddressResolverServiceRelativeAddress;
					$client = new WebClient();
					$redirectorAddress = $client . DownloadString($baseAddressResolverService . "/GetBaseAddress");
					$baseWebAddress = substr($baseWebAddress, 0, strripos($baseWebAddress, "/"));
					$this->AuthenticationServiceAddress = $baseWebAddress . $redirectorAddress . self::AuthenticationServiceRelativeAddress;
					$this->CostCenterServiceAddress = $baseWebAddress . $redirectorAddress . self::CostCenterServiceRelativeAddress;
					$this->ERA_Address = $baseWebAddress . $redirectorAddress . self::ERA_ServiceRelativeAddress;
				}
			}
			catch (Exception $webEx)
			{
				error_log($webEx);
			}
		}


		public function Login($client, $username, $password, &$authCookie)
		{
			$authCookie = "";
			try
			{
				$session = $client->get($this->AuthenticationServiceAddress . "/session");
				$m = $session->rsa->M;
				$e = $session->rsa->E;
				$rsa = new Crypt_RSA();
				$rsa->loadKey(array(
					'e' => new Math_BigInteger($e, 16),
					'n' => new Math_BigInteger($m, 16),));
				$sessionId = $session->id;
				$sessionPlusPassword = $sessionId . "**" . $password;
				$rsa->setEncryptionMode(2);
				$encrypted = $rsa->encrypt($sessionPlusPassword);
				$hexPassword = strtoupper(bin2hex($encrypted));
				$arr = json_encode(array(
					'sessionId' => $sessionId,
					'username' => $username,
					'password' => $hexPassword,));
				$loginRes = $client->post($this->AuthenticationServiceAddress . "/login", $arr);
				if ($loginRes != "")
				{
					error_log($loginRes);
				}
				return $session->id;
			}
			catch (Exception $webEx)
			{
				error_log($webEx);
			}
		}
	}

	class CustomerBody
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

	class CustomerAddress
	{
		public $customerId;
        public $addressData;
	}

	class CustomerAddressData
	{
		public $id;
        public $cityId;
        public $customerId;
        public $name;
        public $phone;
        public $email;
        public $zipcode;
        public $details;
        public $isDefault;
	}

	class CustomerAttribute
	{
		public $Key;
        public $Value;
	}

	class InvoiceBody
	{
		public $id;
        public $datetime;
        public $customerId;
        public $currencyId;
        public $storeId;
        public $settlementPolicyId;
        public $documentPatternId;
        // public $price;
        // public $netprice;
        public $items;
	}

	class InvoiceItems
	{
		public $id;
        public $productId;
        public $unitId;
        public $quantity;
        public $storeId;
        public $fee;
        // public $price;
        // public $netprice;
	}
?>