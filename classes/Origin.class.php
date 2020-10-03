<?php 

namespace CanadaPost;

class Origin {

	private $db;
    private $incomingData;


    public function __construct($incomingData = '') {

    	$this->db = new Database();
        $this->incomingData = $incomingData;
    }


	public function getAll() {

		$locations = array();

		$result = $this->db->query("SELECT l.LocationsID, l.City, p.ProvinceName 
									FROM Locations AS l, Provinces AS p 
									WHERE l.ProvincesID = p.ProvincesID
									ORDER BY l.City");
		if($result) {
			while($row = $result->fetch_assoc()) {

				$locations[] = array(

					'Id' => $row['LocationsID'],
					'city' => utf8_encode($row['City']),
					'province' => utf8_encode($row['ProvinceName'])
				);
			}
		}
		return $locations;
	}


	public function getById($id) {

		$location = array();

		$result = $this->db->query("SELECT l.*, p.ProvinceName, p.ProvinceCode 
									FROM Locations AS l, Provinces AS p
									WHERE l.ProvincesID = p.ProvincesID
									AND l.LocationsID = " . $id . "
									LIMIT 1");

		if($result) {
			$row = $result->fetch_assoc();

			$location['Id'] = $row['LocationsID'];
			$location['Name'] = COMPANY_NAME;
			$location['Company'] = COMPANY_NAME;
			$location['StreetNumber'] = Address::getStreetNumber($row['SteetAddress']);
			$location['StreetName'] = Address::getStreetName($row['SteetAddress']);
			$location['City'] = Address::cleanCityName($row['ActualCityName']);
			$location['Province'] = $row['ProvinceCode'];
			$location['Country'] = CP_COUNTRY_CODE;
			$location['PostalCode'] = $row['PostalCode'];
			$location['PhoneAreaCode'] = Address::getPhoneAreaCode($row['ShippingAccountPhone']);
			$location['Phone'] = Address::getPhone($row['ShippingAccountPhone']);
			$location['LocationName'] = $row['City'];
		}

	    //Encode everything to UTF8
		foreach ($location as &$val) {
			$val = utf8_encode($val);
		}

		return $location;
	}


	private function getByOrderId($id = 0) {

		if(empty($id) || !is_numeric($id)) {
			return;
		}

		$location = array();

		$result = $this->db->query("SELECT l.*, p.ProvinceName, p.ProvinceCode 
									FROM Locations AS l, Provinces AS p, TrackingInfo AS t
									WHERE l.ProvincesID = p.ProvincesID
									AND t.TrackingCarrierID = 2 
									AND l.LocationsID = t.LocationID
									AND t.OrderID = " . $id . "
									LIMIT 1");

		if($result) {
			$row = $result->fetch_assoc();

			$location['Id'] = $row['LocationsID'];
			$location['Name'] = COMPANY_NAME;
			$location['Company'] = COMPANY_NAME;
			$location['StreetNumber'] = Address::getStreetNumber($row['SteetAddress']);
			$location['StreetName'] = Address::getStreetName($row['SteetAddress']);
			$location['City'] = Address::cleanCityName($row['ActualCityName']);
			$location['Province'] = $row['ProvinceCode'];
			$location['Country'] = CP_COUNTRY_CODE;
			$location['PostalCode'] = $row['PostalCode'];
			$location['PhoneAreaCode'] = Address::getPhoneAreaCode($row['ShippingAccountPhone']);
			$location['Phone'] = Address::getPhone($row['ShippingAccountPhone']);
			$location['LocationName'] = $row['City'];
		}

	    //Encode everything to UTF8
		foreach ($location as &$val) {
			$val = utf8_encode($val);
		}
		return $location;
	}
}