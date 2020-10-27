<?php 
namespace CanadaPost;

class Search {

	private $db;
	public $errors = array();


	public function __construct() {  
		$this->db = new Database();
	}
	


	public function getByOrderID($orderId = '') {
		if(empty($orderId)) {
			$this->errors[] = 'Order Id is empty';
			return;
		}

		$result = $this->db->query("SELECT t.*, tc.CarrierName, tc.TrackingURL,
							l.City AS StoreCity, l.SteetAddress AS StoreAddress, l.PostalCode AS StorePostalCode 
								FROM TrackingInfo AS t, TrackingCarrier AS tc, Locations AS l 
								WHERE t.TrackingCarrierID = tc.TrackingCarrierID 
								AND t.LocationID = l.LocationsID
								AND t.Void = 0 
								AND t.TrackingCode <> '' 
								AND t.OrderID = '" . $orderId . "' 
								ORDER BY t.TrackingInfoID DESC");

		$shipments = array();
		$error = 'Can not find Shipments for the Order: ' . $orderId;

		if(!$result) {
			$this->errors[] = $error;
			return;
		}

		while($row = $result->fetch_assoc()) {

			$shipments[] = array(
				'Id' => $row['TrackingInfoID'],
				'carrier' => $row['CarrierName'],
				'serivce' => $row['CourierService'],
				'trackingUrl' => str_replace('[TRACKING]', $row['TrackingCode'], $row['TrackingURL']),
				'orderId' => $row['OrderID'],
				'pin' => $row['TrackingCode'],
				'date' => $row['DateAdded'],
				'void' => $row['Void'],
				'label' => $row['Label'],
				'name' => $row['Name'],
				'admin' => $row['AdminName'],
				'phone' => $row['Phone'],
				'city' => $row['City'],
				'address' => $row['Address'],
				'postalCode' => $row['PostalCode'],
				'storeCity' => $row['StoreCity'],
				'storeAddress' => $row['StoreAddress'],
				'storePostalCode' => $row['StorePostalCode']
			);
		}

		if(empty($shipments)){
			$this->errors[] = $error;
		}

		return $shipments;
	}


	public function getByTrackingNumber($trackingNumber = 0) {
		if(empty($trackingNumber)) {
			$this->errors[] = 'Tracking Number is empty';
			return;
		}

		$result = $this->db->query("SELECT t.*, tc.CarrierName, tc.TrackingURL,
							l.City AS StoreCity, l.SteetAddress AS StoreAddress, l.PostalCode AS StorePostalCode 
								FROM TrackingInfo AS t, TrackingCarrier AS tc, Locations AS l 
								WHERE t.TrackingCarrierID = tc.TrackingCarrierID 
								AND t.LocationID = l.LocationsID
								AND t.Void = 0 
								AND t.TrackingCode = '" . $trackingNumber . "' 
								ORDER BY t.TrackingInfoID DESC");

		$shipments = array();
		$error = 'Can not find Shipments for the Tracking Number: ' . $trackingNumber;

		if(!$result) {
			$this->errors[] = $error;
			return;
		}

		while($row = $result->fetch_assoc()) {

			$shipments[] = array(
				'Id' => $row['TrackingInfoID'],
				'carrier' => $row['CarrierName'],
				'serivce' => $row['CourierService'],
				'trackingUrl' => str_replace('[TRACKING]', $row['TrackingCode'], $row['TrackingURL']),
				'orderId' => empty($row['OrderID']) ? 'n/a' : $row['OrderID'],
				'pin' => $row['TrackingCode'],
				'date' => $row['DateAdded'],
				'void' => $row['Void'],
				'label' => $row['Label'],
				'name' => $row['Name'],
				'phone' => $row['Phone'],
				'city' => $row['City'],
				'address' => $row['Address'],
				'postalCode' => $row['PostalCode'],
				'storeCity' => $row['StoreCity'],
				'storeAddress' => $row['StoreAddress'],
				'storePostalCode' => $row['StorePostalCode']
			);
		}
		
		if(empty($shipments)){
			$this->errors[] = $error;
		}

		return $shipments;
	}



	public function getByPackageReference($packageReference = '') {
		if(empty($packageReference)) {
			$this->errors[] = 'Package Reference is empty';
			return;
		}

		$result = $this->db->query("SELECT t.*, tc.CarrierName, tc.TrackingURL,
							l.City AS StoreCity, l.SteetAddress AS StoreAddress, l.PostalCode AS StorePostalCode 
								FROM TrackingInfo AS t, TrackingCarrier AS tc, Locations AS l 
								WHERE t.TrackingCarrierID = tc.TrackingCarrierID 
								AND t.LocationID = l.LocationsID
								AND t.Void = 0 
								AND t.TrackingCode <> '' 
								AND t.Reference = '" . $packageReference . "' 
								ORDER BY t.TrackingInfoID DESC"); 

		$shipments = array();
		$error = 'Can not find Shipments for the Package Reference: ' . $packageReference;

		if(!$result) {
			$this->errors[] = $error;
			return;
		}

		while($row = $result->fetch_assoc()) {

			$shipments[] = array(
				'Id' => $row['TrackingInfoID'],
				'carrier' => $row['CarrierName'],
				'serivce' => $row['CourierService'],
				'trackingUrl' => str_replace('[TRACKING]', $row['TrackingCode'], $row['TrackingURL']),
				'orderId' => empty($row['OrderID']) ? 'n/a' : $row['OrderID'],
				'pin' => $row['TrackingCode'],
				'date' => $row['DateAdded'],
				'void' => $row['Void'],
				'label' => $row['Label'],
				'name' => $row['Name'],
				'phone' => $row['Phone'],
				'city' => $row['City'],
				'address' => $row['Address'],
				'postalCode' => $row['PostalCode'],
				'storeCity' => $row['StoreCity'],
				'storeAddress' => $row['StoreAddress'],
				'storePostalCode' => $row['StorePostalCode']
			);
		}

		if(empty($shipments)){
			$this->errors[] = $error;
		}

		return $shipments;
	}


	public function getByPhoneNumber($phoneNumber = '') {
		if(empty($phoneNumber)) {
			$this->errors[] = 'Phone Number is empty';
			return;
		}

		$fixedPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
		$shortPhone = substr($fixedPhone, -7);
		$areaCodePhone = substr($fixedPhone, 0, 3) . "-" . substr($fixedPhone, 3);

		$result = $this->db->query("SELECT t.*, tc.CarrierName, tc.TrackingURL, l.City As StoreCity, 
							l.SteetAddress AS StoreAddress, l.PostalCode AS StorePostalCode 
								FROM TrackingInfo AS t, TrackingCarrier AS tc, Locations AS l 
								WHERE t.TrackingCarrierID = tc.TrackingCarrierID 
								AND t.LocationID = l.LocationsID
								AND t.Void = 0 
								AND t.TrackingCode <> '' 
								AND (t.Phone = '" . $fixedPhone . "' 
									OR t.Phone = '" . $phoneNumber . "' 
									OR t.Phone = '" . $shortPhone . "' 
									OR t.Phone = '" . $areaCodePhone . "') 
								ORDER BY t.TrackingInfoID DESC");

		$shipments = array();
		$error = 'Can not find Shipment for the Phone Number: ' . $phoneNumber;

		if(!$result) {
			$this->errors[] = $error;
			return;
		}

		while($row = $result->fetch_assoc()) {

			$shipments[] = array(
				'Id' => $row['TrackingInfoID'],
				'carrier' => $row['CarrierName'],
				'serivce' => $row['CourierService'],
				'trackingUrl' => str_replace('[TRACKING]', $row['TrackingCode'], $row['TrackingURL']),
				'orderId' => empty($row['OrderID']) ? 'n/a' : $row['OrderID'],
				'pin' => $row['TrackingCode'],
				'date' => $row['DateAdded'],
				'void' => $row['Void'],
				'label' => $row['Label'],
				'name' => $row['Name'],
				'phone' => $row['Phone'],
				'city' => $row['City'],
				'address' => $row['Address'],
				'postalCode' => $row['PostalCode'],
				'storeCity' => $row['StoreCity'],
				'storeAddress' => $row['StoreAddress'],
				'storePostalCode' => $row['StorePostalCode']
			);
		}

		if(empty($shipments)){
			$this->errors[] = $error;
		}

		return $shipments;
	}



	public function getByCustomerName($customerName = '') {
		if(empty($customerName)) {
			$this->errors[] = 'Customer Name is empty';
			return;
		}

		$result = $this->db->query("SELECT t.*, tc.CarrierName, tc.TrackingURL, l.City As StoreCity, 
							l.SteetAddress AS StoreAddress, l.PostalCode AS StorePostalCode 
								FROM TrackingInfo AS t, TrackingCarrier AS tc, Locations AS l 
								WHERE t.TrackingCarrierID = tc.TrackingCarrierID 
								AND t.LocationID = l.LocationsID
								AND t.Void = 0 
								AND t.TrackingCode <> '' 
								AND LOWER(t.Name) = '" . strtolower($customerName) . "'
								ORDER BY t.TrackingInfoID DESC");

		$shipments = array();

		$error = 'Can not find Shipment for the Customer Name: ' . $customerName;

		if(!$result) {
			$this->errors[] = $error;
			return;
		}

		while($row = $result->fetch_assoc()) {

			$shipments[] = array(
				'Id' => $row['TrackingInfoID'], 
				'carrier' => $row['CarrierName'], 
				'serivce' => $row['CourierService'], 
				'trackingUrl' => str_replace('[TRACKING]', $row['TrackingCode'], $row['TrackingURL']),
				'orderId' => empty($row['OrderID']) ? 'n/a' : $row['OrderID'],
				'pin' => $row['TrackingCode'],
				'date' => $row['DateAdded'],
				'void' => $row['Void'],
				'label' => $row['Label'],
				'name' => $row['Name'],
				'phone' => $row['Phone'],
				'city' => $row['City'],
				'address' => $row['Address'],
				'postalCode' => $row['PostalCode'],
				'storeCity' => $row['StoreCity'],
				'storeAddress' => $row['StoreAddress'],
				'storePostalCode' => $row['StorePostalCode']
			);
		}

		if(empty($shipments)){
			$this->errors[] = $error;
		}		

		return $shipments; 
	}
}