<?php 
namespace CanadaPost;

class Customer {

	private $db;

    public function __construct() {
		$this->db = new Database();
    }


	public function getByOrderId($id) {
		$customer = array();

		//Check if the Customer is a Store Location
		if(strtolower($id[0]) == 'l' && strlen($id) == 4) {
			return $this->getLocationByCode($id);
		}

		//Check Web Store Orders
		$customer = $this->getByOrderIdWebStoreOrders($id);

		//If the record is missing, try to pull the shipping data from the Account table
		if(empty($customer['ShippingName']) || empty($customer['StreetName'])) {
			$customer = $this->getByOrderIdFromAccount($id);
		}

		//Check POS Orders
		if(count($customer) < 1) {
			$customer = $this->getByOrderIdShipmentsTable($id);
		}

		return $customer;
	}



	private function getByOrderIdWebStoreOrders($id) {

		$customer = array();

		$result = $this->db->query("SELECT a.AccountsID, cs.DeliveryEmail, cs.DeliveryPhone, o.Active, 
								cs.DeliveryPostalCode, cs.DeliveryCity, cs.DeliveryAddressLine2, cs.DeliveryAddressLine1, 
								o.OrdersID, cs.DeliveryProvince, cs.DeliveryName, o.ShippingName, o.TotalBeforeGift, o.TransAmount,
								o.CourierSelected, o.CourierService, o.ShipDirect 

								FROM Accounts a, Orders o, CanparShipments cs
								WHERE a.AccountsID = o.AccountsID
								AND o.OrdersID = cs.OrdersID
								AND o.OrdersID = " . $id . " 
								ORDER BY cs.DateAdded DESC
								LIMIT 1"); 
		if($result) {
			$row = $result->fetch_assoc();

			$customer['CustomerCode'] = $row['AccountsID']; 
			$customer['OrderActive'] = $row['Active']; 
			$customer['ShippingName'] = !empty($row['ShippingName']) ? $row['ShippingName'] : $row['DeliveryName']; 
			$customer['AttentionTo'] = $customer['ShippingName'];

			$customer['CourierSelected'] = $row['CourierSelected'];
			$customer['CourierService'] = $row['CourierService'];

			$customer['StreetNumber'] = Address::getStreetNumber($row['DeliveryAddressLine1']);
			$customer['StreetName'] = Address::getStreetName($row['DeliveryAddressLine1']);


			$AddressTemp = $row['DeliveryAddressLine2'];
			
				if(strlen(trim($AddressTemp)) > 30){

					$AddressArray = Address::splitAddress($AddressTemp);
					$Address2 = $AddressArray[0];
					$Address3 = $AddressArray[1];

				} else {
					$Address2 = $AddressTemp;
					$Address3 = '';
				}

	        $customer['AddressLine2'] =  Address::cleanAddressLine($Address2);
	        $customer['AddressLine3']  = Address::cleanAddressLine($Address3);
	        $customer['City'] = Address::cleanCityName($row['HomeCity']);
	        $customer['ProvinceCode'] =  $row['DeliveryProvince'];
	        $customer['PostalCode'] = Address::getPostalCode($row['DeliveryPostalCode']); 
	        $customer['Country'] = CP_COUNTRY_CODE; 
	        $customer['PhoneAreaCode'] = Address::getPhoneAreaCode($row['DeliveryPhone']); 
	        $customer['Phone'] = Address::getPhone($row['DeliveryPhone']);
	        $customer['Email'] = $row['DeliveryEmail'];
			$customer['sigRequired']  = false;
			$customer['shipDirect'] = $row['ShipDirect']; 


	        if($row['TransAmount'] > 100) {
				$customer['sigRequired'] = true;
	        
	        } elseif($row['TotalBeforeGift'] > 100) {
				$customer['sigRequired'] = true;
	        }


	       	//Encode everything to UTF8
			foreach ($customer as &$val) {
				$val = utf8_encode($val);
			}
		}
		return $customer;
	}


	private function getByOrderIdFromAccount($id) {

		$customer = array();

		$result = $this->db->query("SELECT a.AccountsID, a.Email, a.HomePhone, a.PostalCode, a.HomeCity, a.AptUnitl, a.HomeAddress, 
								o.OrdersID, a.ProvincesID, a.FirstName, a.LastName, o.ShippingName, o.TotalBeforeGift, o.TransAmount, 
								o.Active, o.CourierSelected, o.CourierService, o.ShipDirect 
									FROM Accounts a, Orders o
									WHERE a.AccountsID = o.AccountsID
									AND o.OrdersID = " . $id . " 
									LIMIT 1");
		if($result) {
			$row = $result->fetch_assoc();

			$customer['CustomerCode'] = $row['AccountsID']; 
			$customer['OrderActive'] = $row['Active']; 
			$customer['ShippingName'] = !empty($row['ShippingName']) ? $row['ShippingName'] : $row['FirstName'] . " " . $row['LastName']; 
			$customer['ShippingName'] = ucwords($customer['ShippingName']);
			$customer['AttentionTo'] = $customer['ShippingName'];

			$customer['shipDirect'] = $row['ShipDirect']; 
			$customer['CourierSelected'] = $row['CourierSelected'];
			$customer['CourierService'] = $row['CourierService'];

			$customer['StreetNumber'] = Address::getStreetNumber($row['HomeAddress']);
			$customer['StreetName'] = Address::getStreetName($row['HomeAddress']);

			$AddressTemp = $row['AptUnitl'];
			
				if(strlen(trim($AddressTemp)) > 30){

					$AddressArray = Address::splitAddress($AddressTemp);
					$Address2 = $AddressArray[0];
					$Address3 = $AddressArray[1];

				} else {
					$Address2 = $AddressTemp;
					$Address3 = '';
				}

	        $customer['AddressLine2'] =  Address::cleanAddressLine($Address2);
	        $customer['AddressLine3']  = Address::cleanAddressLine($Address3);
	        $customer['City'] = Address::cleanCityName($row['HomeCity']);
	        $customer['ProvinceCode'] =  $this->GetProvinceCodeByAccountsID($row['AccountsID']);
	        $customer['PostalCode'] = Address::getPostalCode($row['PostalCode']);
	        $customer['Country'] = CP_COUNTRY_CODE; 
	        $customer['PhoneAreaCode'] = Address::getPhoneAreaCode($row['HomePhone']); 
	        $customer['Phone'] = Address::getPhone($row['HomePhone']);
	        $customer['Email'] = $row['Email'];
			$customer['sigRequired']  = false;


	        if($row['TransAmount'] > 100) {
				$customer['sigRequired'] = true;
	        
	        } elseif($row['TotalBeforeGift'] > 100) {
				$customer['sigRequired'] = true;
	        }


	       	//Encode everything to UTF8
			foreach ($customer as &$val) {
				$val = utf8_encode($val);
			}
		}
		return $customer;
	}



	private function getByOrderIdShipmentsTable($id) {

		$customer = array();

		$result = $this->db->query("SELECT * FROM CanparShipments
							WHERE OrdersID = '" . $id . "' 
							ORDER BY DateAdded DESC 
							LIMIT 1");							

		if($result) {
			$row = $result->fetch_assoc();

			$customer['CustomerCode'] = $row['CanparShipmentsID']; 
			$customer['OrderActive'] = 1;
			$customer['ShippingName'] = $row['DeliveryName']; 
			$customer['AttentionTo'] = $row['DeliveryName'];

			$customer['CourierSelected'] = $row['CourierSelected'];
			$customer['CourierService'] = $row['CourierService'];

			$customer['StreetNumber'] = Address::getStreetNumber($row['DeliveryAddressLine1']);
			$customer['StreetName'] = Address::getStreetName($row['DeliveryAddressLine1']);


			$AddressTemp = $row['DeliveryAddressLine2'];
			
				if(strlen(trim($AddressTemp)) > 30){

					$AddressArray = Address::splitAddress($AddressTemp);
					$Address2 = $AddressArray[0];
					$Address3 = $AddressArray[1];

				} else {
					$Address2 = $AddressTemp;
					$Address3 = '';
				}

	        $customer['AddressLine2'] =  Address::cleanAddressLine($Address2);
	        $customer['AddressLine3']  = Address::cleanAddressLine($Address3);
	        $customer['City'] = Address::cleanCityName($row['DeliveryCity']);
	        $customer['ProvinceCode'] =  strtoupper($row['DeliveryProvince']);
	        $customer['PostalCode'] = Address::getPostalCode($row['DeliveryPostalCode']);
	        $customer['Country'] = "CA"; 
	        $customer['PhoneAreaCode'] = Address::getPhoneAreaCode($row['DeliveryPhone']); 
	        $customer['Phone'] = Address::getPhone($row['DeliveryPhone']);
	        $customer['Email'] = $row['DeliveryEmail'];

	        $customer['sigRequired']  = true;


	       	//Encode everything to UTF8
			foreach ($customer as &$val) {
				$val = utf8_encode($val);
			}
		}

		return $customer;
	}


	private function getLocationByCode($code) {

		$customer = array();

		$result = $this->db->query("SELECT l.*, p.ProvinceName, p.ProvinceCode 
									FROM Locations AS l, Provinces AS p 
									WHERE l.ProvincesID = p.ProvincesID 
									AND l.LocationCode = '" . $code . "' LIMIT 1");
		if($result) {
			$row = $result->fetch_assoc();

			$customer['CustomerCode'] = $row['LocationCode']; 
			$customer['ShippingName'] = COMPANY_NAME; 
			$customer['AttentionTo'] = COMPANY_NAME;

			$customer['StreetNumber'] = Address::getStreetNumber($row['SteetAddress']);
			$customer['StreetName'] = ucfirst(Address::getStreetName($row['SteetAddress']));
	        $customer['AddressLine2'] =  '';
	        $customer['AddressLine3']  = '';
	        $customer['City'] = Address::cleanCityName($row['ActualCityName']);

	        $customer['ProvinceCode'] =  strtoupper($row['ProvinceCode']);
	        $customer['PostalCode'] = Address::getPostalCode($row['PostalCode']);
	        $customer['Country'] = "CA"; 
	        $customer['PhoneAreaCode'] = Address::getPhoneAreaCode($row['Phone']); 
	        $customer['Phone'] = Address::getPhone($row['Phone']);
	        $customer['Email'] = $row['Email'];


	       	//Encode everything to UTF8
			foreach ($customer as &$val) {
				$val = utf8_encode($val);
			}
		}

		return $customer;
	}


	private function GetProvinceCodeByAccountsID($accountId = 0) {
		$result = $this->db->query("SELECT p.ProvinceCode
                                	FROM Provinces p, Accounts a
                                	WHERE p.ProvincesID = a.ProvincesID
                                	AND a.AccountsID = " . $accountId . "
                                	LIMIT 1");

		if($result) {
			$row = $result->fetch_assoc();
        	return $row['ProvinceCode'];
		}
	}	
}