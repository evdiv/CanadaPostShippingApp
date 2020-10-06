<?php 
namespace CanadaPost;

class Shipment {

	private $db;

	private $incomingData;
	private $client;
	private $request;
	private $response;	
	private $package = array();

	public $shipmentId;
	public $labelId;
	public $trackingPin; 

	public $label = '';
	public $labels = array(); 
	public $groups = array(); 
	public $errors = array(); 
	public $voided = '';


	public function __construct($incomingData = '') {  

		$this->db = new Database();

		$this->incomingData = $incomingData;
		$this->client = $this->createClient();

		$this->request = new \stdClass();
		$this->response = new \stdClass();
	}
	
    
	private function createClient() {

		// SSL Options 
		$ctx = stream_context_create( array(
										'ssl' => array( 
											'verify_peer'=> true, 
											'cafile' => CP_CERT, 
											'peer_name' => CP_HOSTNAME 
											),
											'http' => array(
        										'protocol_version' => 1.0,
    										)
										)
									);	


  		$client = $this->customSoapClient( "./wsdl/shipment.wsdl", array(
  												'location'	=>	CP_SHIPMENT_URL,
						                		'features' => SOAP_SINGLE_ELEMENT_ARRAYS, 
						                		'stream_context' => $ctx)
  										);


		// Set WS Security UsernameToken
		$WSSENS = CP_WS_SECURITY_TOKEN;
		$usernameToken = new \stdClass(); 
		$usernameToken->Username = new \SoapVar(CP_USERNAME, XSD_STRING, null, null, null, $WSSENS);
		$usernameToken->Password = new \SoapVar(CP_PASS, XSD_STRING, null, null, null, $WSSENS);

		$content = new \stdClass(); 
		$content->UsernameToken = new \SoapVar($usernameToken, SOAP_ENC_OBJECT, null, null, null, $WSSENS);
		$header = new \SOAPHeader($WSSENS, 'Security', $content);
		$client->__setSoapHeaders($header); 

  		return $client;
	}


	private function customSoapClient($wsdl, $options) {
		if(isset($this->incomingData['action']) && $this->incomingData['action'] == 'createShipment') {
			return new CPSoapClient($wsdl, $options);
		}
		return new \SoapClient($wsdl, $options);
	}


	private function populateRequest() {


		$this->request = array(
	    	'create-shipment-request' => array(
				'locale'			=> CP_LOCALE,
				'mailed-by'			=> CP_CUSTOMER_NUMBER,
				'shipment' 				=> array(
					'groupIdOrTransmitShipment' => $this->populateGroupId(),
					'requested-shipping-point'	=> strtoupper(str_replace(' ', '', $this->incomingData['senderPostalCode'])),
					'cpc-pickup-indicator'	=> 'true',
					'expected-mailing-date'		=> date('Y-m-d'),
					'delivery-spec'		=> array(

						'service-code'				=> $this->getServiceCode(),
						'sender'					=> $this->populateSender(),
						'destination'				=> $this->populateDestination(),
						'parcel-characteristics'   	=> $this->populateParcel(),

						'notification' 	=> array(
							'email'			=> COMPANY_EMAIL,
							'on-shipment'	=> false,
							'on-exception'	=> false,
							'on-delivery'	=> false
						),

						'print-preferences' => array(
							'output-format'		=> CP_PRINT_OUTPUT_FORMAT
						),

						'preferences' 	=> array(
							'show-packing-instructions'	=> false,
							'show-postage-rate'			=> false,
							'show-insured-value'		=> true
						),	

						'settlement-info' => array(
							'contract-id'					=> CP_AGREEMENT_NUMBER,
							'paid-by-customer'              => CP_PAID_BY_CUSTOMER, 
							'intended-method-of-payment'	=> CP_PAYMENT_METHOD
						),

						'references' 	=> array(
							'cost-centre'	=> $this->getCostCentre(),
							'ref'	=> $this->incomingData['orderID'],
						)
					)
				)
			)
		);


		if(boolval($this->incomingData['sigRequired'])) {
			$this->request['create-shipment-request']['shipment']['delivery-spec']['options'] = array(
				'option'  => array( 'option-code' => 'SO'));
		}
		return $this->request;
	}


	private function populateGroupId() {
		return array('ns1:group-id'	=> date('dmY') . "-" . $this->incomingData['senderLocationId']);
	}


	private function populateDestination() {

		$name = str_replace("&#39;", "'", Common::fixAccents($this->incomingData['receiverName']));
		$address1 = str_replace("&#39;", "'", Common::fixAccents($this->incomingData['receiverStreetNumber'] . ' ' . $this->incomingData['receiverStreetName']));
		$address2 = str_replace("&#39;", "'", Common::fixAccents($this->incomingData['receiverAddress2']));
		$city = str_replace("&#39;", "'", Common::fixAccents($this->incomingData['receiverCity'])); 


		$destination = array(
			'name' => $name,	
			'company'			=> '',	
			'address-details'	=> array(
				'address-line-1'	=> $address1,	
				'address-line-2'	=> $address2,		
				'city'				=> $city,	
				'prov-state'		=> $this->incomingData['receiverProvince'],	
				'country-code'		=> CP_COUNTRY_CODE,	
				'postal-zip-code'	=> strtoupper(str_replace(' ' , '', $this->incomingData['receiverPostalCode']))		
			)					
		);

		return $destination;
	}


	private function populateSender() {

		$sender = array(
			'name'				=> Common::fixAccents($this->incomingData['senderName']),	
			'company'			=> COMPANY_NAME,	
			'contact-phone'		=> COMPANY_PHONE,	
			'address-details'	=> array(
				'address-line-1'	=> Common::fixAccents($this->incomingData['senderStreetNumber'] . ' ' . $this->incomingData['senderStreetName']),
				'city'				=> Common::fixAccents($this->incomingData['senderCity']),	
				'prov-state'		=> $this->incomingData['senderProvince'],
				'country-code'		=> CP_COUNTRY_CODE,	
				'postal-zip-code'	=> strtoupper(str_replace(' ', '', $this->incomingData['senderPostalCode']))	
			)
		);

		return $sender;
	}


	private function populateParcel() {

		$parcel = array(
			'weight'		=> $this->package['weight'],
			'dimensions'	=> array(
				'length'		=>	$this->package['length'],
				'width'			=> 	$this->package['width'],
				'height'		=> 	$this->package['height']
			),
			'unpackaged'	=> false,
			'mailing-tube'	=> false
		);

		return $parcel;
	}


	private function getServiceCode() {

		//By default the service code is DOM.RP
		$services = array(
			'DOM.RP' => 'Regular Parcel', 
			'DOM.EP' => 'Expedited Parcel',
			'DOM.XP' => 'Xpresspost',
			'DOM.PC' => 'Priority',
			'DOM.LIB' => 'Library Books');

		$serviceCode = array_search($this->incomingData['serviceID'], $services);
		return ($serviceCode !== false) ? $serviceCode : "DOM.RP";
	}


	// Create new Shipment 
	public function create() {

		foreach($this->incomingData['packages'] as $package) {
			$this->package = $package;
			$this->populateRequest();

			try {
				$this->response = $this->client->__soapCall('CreateShipment', $this->request, NULL, NULL);
				$this->parseResponse($this->response);
				$this->store();

			} catch(Exception $e) {

				$this->errors[] = 'Fault Code: ' . trim($e->faultcode) . ' Msg: ' . trim($e->getMessage());
			}
		}
	}  


	//Store Shipments in the DB
	public function store() {

		$orderID = !empty($this->incomingData['orderID']) ? $this->incomingData['orderID'] : '';
		$locationID= !empty($this->incomingData['senderLocationId']) ? $this->incomingData['senderLocationId'] : '';
		$serviceID = !empty($this->incomingData['serviceID']) ? $this->incomingData['serviceID'] : '';

		$packageSQL = "";
		$packageSQL .= !empty($this->package['length']) ? " Length = " . $this->package['length'] . ", " : "";
		$packageSQL .= !empty($this->package['width']) ? " Width = " . $this->package['width'] . ", " : "";
		$packageSQL .= !empty($this->package['height']) ? " Height = " . $this->package['height'] . ", " : "";
		$packageSQL .= !empty($this->package['weight']) ? " Weight = " . $this->package['weight'] . ", " : "";				
		$packageSQL .= !empty($this->package['reference']) ? " Reference = '" . $this->package['reference'] . "', " : "";				
		$packageSQL .= !empty($this->package['note']) ? " Note = '" . $this->package['note'] . "', " : "";


		$this->db->query("INSERT INTO TrackingInfo SET 
							OrderID = '" . $orderID . "', 
							TrackingCarrierID = 1, 
							TrackingCode = '" . $this->trackingPin . "', 
							TrackingIdentifier = '" . $this->shipmentId . "', 
							Label = '" . $this->label . ".pdf',
							LocationID = '" . $locationID . "',  
							" . $packageSQL . "
							CourierService = '" . $serviceID . "'");
		return $this;		
	}



	// Void Existing Shipment
	public function void() {

        $this->request = array(
	    	'void-shipment-response' => array(
				'locale'			=> CP_LOCALE,
				'mailed-by'			=> CP_CUSTOMER_NUMBER,
				'shipment-id'		=> $this->incomingData['shipmentId'])
			);

        try {
            $this->response = $this->client->__soapCall('VoidShipment', $this->request, NULL, NULL);       

        } catch(Exception $e) {
            $this->errors[] = 'Fault Code: ' . trim($e->faultcode) . ' Msg: ' . trim($e->getMessage());
        }
        $this->getVoided();
        $this->updateAsVoidedinDB();

        return $this;
	}


	// Get the full list of groups eligible for use in a Transmit Shipments request.
	public function getGroups() {

		try {
			$result = $this->client->__soapCall('GetGroups', array(
												    'get-groups-request' => array(
													'locale'			=> CP_LOCALE,
													'mailed-by'			=> CP_CUSTOMER_NUMBER,
													)
												), NULL, NULL);

			// Parse Response
			if ( isset($result->{'groups'}) ) {
				if ( isset($result->{'groups'}->{'group-id'}) ) {
					foreach ( $result->{'groups'}->{'group-id'} as $groupId ) {  
						$this->groups[] = $groupId;	
					}
				} else {
					$this->errors[] = 'No groups returned.';	
				}			
			} else {
				foreach ( $result->{'messages'}->{'message'} as $message ) {
					$this->errors[] = $message->code . ' ' . $message->description;
				}
			}

		} catch(SoapFault $exception) {
            die($exception);
		}

		return $this;
	}


	//Get all Shipments by selected Date
	public function getByDate($date = '') {
		$shipments = array();

		if(empty($date)) {
			$this->errors[] = "'Date can not be empty'";
		}

		$result = $this->db->query("SELECT * FROM TrackingInfo
									WHERE TrackingCarrierID = 1 
									AND OrderID <> '0' 
									AND TrackingCode <> '' 
									AND DATE(DateAdded) = '" . $date . "'
									GROUP BY TrackingCode 
									ORDER BY DateAdded DESC");


		if($result) {
			while($row = $result->fetch_assoc()) {
				
				$shipments[] = array(

					'Id' => $row['TrackingInfoID'],
					'orderId' => $row['OrderID'],
					'locationId' => isset($row['LocationsID']) ? $row['LocationsID'] : 0,
					'shipmentId' => $row['TrackingIdentifier'],
					'pin' => $row['TrackingCode'],
					'date' => $row['DateAdded'],
					'time' => str_replace($date, '', $row['DateAdded']),
					'void' => $row['Void'],
					'label' => $row['Label'],
					'locationID' => $row['LocationID']
				);
			}
		} else {
			$this->errors[] = 'There are no Shipments for the Selected Date';
		}
		return $shipments;
	}



	//Get Shipment details by Tracking Number
	public function getByTrackingNumber($pin = '') {

		$shipment = array();


		if(empty($pin)) {
			$this->errors[] = "'Canada Post Tracking Code can not be empty'";
		}

		$result = $this->db->query("SELECT t.*, l.City, l.SteetAddress, l.PostalCode, l.LocationsID  
									FROM TrackingInfo AS t
									LEFT JOIN Locations AS l ON t.LocationID = l.LocationsID
									WHERE t.TrackingCode =  '" . $pin . "'
									LIMIT 1");

		if($result) {
			$row = $result->fetch_assoc();

			$shipment['date'] = $row['DateAdded'];
			$shipment['orderId'] = $row['OrderID'];
			$shipment['service'] = $row['CourierService'];
			$shipment['senderLocationId'] = $row['LocationsID'];
			$shipment['senderCity'] = $row['City'];
			$shipment['senderAddress'] = $row['SteetAddress'];
			$shipment['senderPostalCode'] = $row['PostalCode'];
			$shipment['label'] = $row['Label'];
			$shipment['voided'] = $row['Void'];
		
		} else {
			$this->errors[] = 'Can not find Shipment Details for this Tracking Code';
		}
		return $shipment;
	}


	//Get Shipment details by Tracking Identifier 
	// !!! it is not a Tracking Number, it is a Canada Post internal Identifier
	public function getByTrackingIdentifier($shipmentId = '') {

		$shipment = array();


		if(empty($shipmentId)) {
			$this->errors[] = "'Canada Post Tracking Identifier can not be found'";
		}

		$result = $this->db->query("SELECT t.*, l.City, l.SteetAddress, l.PostalCode, l.LocationsID  
									FROM TrackingInfo AS t
									LEFT JOIN Locations AS l ON t.LocationID = l.LocationsID
									WHERE t.TrackingIdentifier =  '" . $shipmentId . "'
									LIMIT 1");

		if($result) {
			$row = $result->fetch_assoc();

			$shipment['date'] = $row['DateAdded'];
			$shipment['orderId'] = $row['OrderID'];
			$shipment['service'] = $row['CourierService'];
			$shipment['senderLocationId'] = $row['LocationsID'];
			$shipment['senderCity'] = $row['City'];
			$shipment['senderAddress'] = $row['SteetAddress'];
			$shipment['senderPostalCode'] = $row['PostalCode'];
			$shipment['label'] = $row['Label'];
			$shipment['voided'] = $row['Void'];
		
		} else {
			$this->errors[] = 'Can not find Shipment Details for this Tracking Identifier';
		}
		return $shipment;
	}




	public function getPackagesByOrderId($id) {
		$packages = array();

		$result = $this->db->query("SELECT t.Length, t.Width, t.Height, t.Weight, t.Reference, t.Note
										FROM TrackingInfo AS t
										WHERE t.TrackingCarrierID = 2 
										AND t.Length IS NOT NULL 
										AND t.Width IS NOT NULL 
										AND t.Height IS NOT NULL
										AND t.Weight IS NOT NULL 
										AND t.OrderID = '" . $id ."'");

		if($result) {
			while($row = $result->fetch_assoc()) {

				$packages[] = array(
					'length' => $row['Length'],
					'width' => $row['Width'],
					'height' => $row['Height'],
					'weight' => $row['Weight'],
					'reference' => $row['Reference'],
					'note' => $row['Note']
				);
			}
		}

		return $packages;
	}



	public function getShippingBoxes() {
		$boxes = array();

		$result = $this->db->query("SELECT * FROM ProductsBoxes");

		if($result) {
			while($row = $result->fetch_assoc()) {
				$boxes[] = array(
					'id' => $row['ProductsBoxesID'],
					'description' => $row['Description'],
					'weightLimit' => $row['WeightLimit'],
					'length' => $row['Length'],
					'width' => $row['Width'],
					'height' => $row['Height']
				);
			}
		}
		return $boxes;
	}



	private function parseResponse($response) {

		if(isset($response->{'shipment-info'})) {

			$this->trackingPin = $response->{'shipment-info'}->{'tracking-pin'};
			$this->shipmentId = $response->{'shipment-info'}->{'shipment-id'};

			foreach ( $response->{'shipment-info'}->{'artifacts'}->{'artifact'} as $artifact ) {  

				$this->label = $artifact->{'artifact-id'};
				$this->labels[] = $this->label;
			}

		} else {
			foreach ( $response->{'messages'}->{'message'} as $message ) {
				$this->errors[]  = 'Error Code: ' . $message->code . ' Error Msg: ' . $message->description;
			}
		}
	}


    private function getVoided() {

		if ( isset($this->response->{'void-shipment-success'}) ) {
			if ( $this->response->{'void-shipment-success'} ) {
				$this->voided = $this->incomingData['shipmentId'];
			}
		} else {
			foreach ( $this->response->{'messages'}->{'message'} as $message ) {
				$this->errors[]  = 'Error Code: ' . $message->code . ' Error Msg: ' . $message->description;
			}
		}
    }



	private function updateAsVoidedinDB() {
		if(empty($this->voided)) {
			return;
		}

		$this->db->query("UPDATE TrackingInfo SET  Void = 1 WHERE TrackingIdentifier = '" . $this->voided . "' LIMIT 1");
	}


	private function getCostCentre() {
		$result = $this->db->query("SELECT CpAPIGL FROM Locations WHERE CpAPICustomerNumber = '" . CP_ACCOUNT_NUMBER . "'");

		if(!$result) {
			return 'ccent';
		}

		$row = $result->fetch_assoc();
		return !empty($row['CpAPIGL']) ? $row['CpAPIGL'] : 'ccent';
	}
}
