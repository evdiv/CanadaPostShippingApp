<?php 

namespace CanadaPost;

class Manifest {

	private $db;
	private $incomingData;

	private $client;
	private $request;
	private $response;	


	public $manifestIds = array();
	public $manifestArtifacts = array();
	public $errors = array();



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


  		$client = new \SoapClient( "./wsdl/manifest.wsdl", array(
						                                    'location'	=>	CP_MANIFEST_URL,
						                                    'features' => SOAP_SINGLE_ELEMENT_ARRAYS, 
						                                    'stream_context' => $ctx
						                                )
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




	private function populateTransmitShipmentsRequest() {

		$postalCode = str_replace(' ', '', $this->incomingData['senderPostalCode']);

		$this->request = array(
	    	'transmit-shipments-request' => array(
				'locale'			=> CP_LOCALE,
				'mailed-by'			=> CP_CUSTOMER_NUMBER,
				'transmit-set'		=> array(
					'group-ids' 		=> array(
						'group-id' 			=> date('dmY') . "-" . $this->incomingData['senderLocationId'],
					),
					'requested-shipping-point' => $postalCode,
					'cpc-pickup-indicator' 	=> true,
					'detailed-manifests' 	=> false,
					'method-of-payment' 	=> 'Account',
					'manifest-address' 		=> array(
						'phone-number' 			=> $this->incomingData['senderPhoneAreaCode'] . ' ' . $this->incomingData['senderPhone'],
						'manifest-company' 		=> $this->incomingData['senderCompany'],
						'address-details' 		=> array(
							'address-line-1' 		=> $this->incomingData['senderAddress'],
							'city' 					=> $this->incomingData['senderCity'],
							'prov-state' 			=> $this->incomingData['senderProvince'],
							'postal-zip-code'		=> $postalCode
						)
					)
				)	
			)
		);
		return $this->request;
	}


	public function create() {

		$this->createTransmitShipmentsRequest();

		//Set delay for 2 sec. to make the data have been transmitted.
		sleep(2);

		if(count($this->manifestIds) > 0) {
			$this->createManifestArtifactRequest();
		}

		return $this;
	}


	public function getforDate() {

		$date = !empty($this->incomingData['date']) ? $this->incomingData['date'] : date('Y-m-d');
		$this->request = array(
	    	'get-manifests-request' => array(
				'locale'			=> CP_LOCALE,
				'mailed-by'			=> CP_CUSTOMER_NUMBER,
				'start'				=> $date,
				'end'				=> $date     
			)
		);

		try {
			$this->response = $this->client->__soapCall('GetManifests', $this->request, NULL, NULL);
			$this->manifestIds = $this->response->manifests->{'manifest-id'};

		} catch(Exception $e) {

			$this->errors[] = 'Fault Code: ' . trim($e->faultcode) . ' Msg: ' . trim($e->getMessage());
		}

		return $this;
	}


	public function getManifestById() {

		$this->manifestIds[] = $this->incomingData['manifestIds'];

		if(count($this->manifestIds) > 0) {
			$this->createManifestArtifactRequest();
		}

		return $this->manifestArtifacts;
	}


	private function createTransmitShipmentsRequest() {

		$this->populateTransmitShipmentsRequest();

		try {
			$this->response = $this->client->__soapCall('TransmitShipments', $this->request, NULL, NULL);
			$this->parseTransmitShipmentsResponse($this->response);

		} catch(Exception $e) {

			$this->errors[] = 'Fault Code: ' . trim($e->faultcode) . ' Msg: ' . trim($e->getMessage());
			return;
		}
	}



	private function populateManifestArtifactRequest($manifestId = '') {

		$this->request = array(
	    	'get-manifest-artifact-id-request' => array(
	    		'manifest-id'		=> $manifestId,
				'locale'			=> CP_LOCALE,
				'mailed-by'			=> CP_CUSTOMER_NUMBER,	
			)
		);

		return $this->request;
	}


	private function createManifestArtifactRequest() {

		foreach ($this->manifestIds as $manifestId) {
			$this->populateManifestArtifactRequest($manifestId);

			try {
				$this->response = $this->client->__soapCall('GetManifestArtifactId', $this->request, NULL, NULL);
				$this->parseManifestArtifactResponse($this->response);

			} catch(Exception $e) {

				$this->errors[] = 'Fault Code: ' . trim($e->faultcode) . ' Msg: ' . trim($e->getMessage());
				return;
			}
		}
	}



	private function parseTransmitShipmentsResponse($response) {

		if (isset($response->{'manifests'})) {

			foreach ( $response->{'manifests'}->{'manifest-id'} as $manifestId ) {  
				$this->manifestIds[] = $manifestId;
			}

			return $this->manifestIds;
		}

		foreach ( $response->{'messages'}->{'message'} as $message ) {
			$this->errors[]  = 'Error Code: ' . $message->code . ' Error Msg: ' . $message->description;
		}

		return;
	}	


	private function parseManifestArtifactResponse($response) {

		if (isset($response->{'manifest'})) {

			$this->manifestArtifacts[] = $response->{'manifest'}->{'artifact-id'};
			return $this->manifestArtifacts;
		}

		foreach ( $response->{'messages'}->{'message'} as $message ) {
			$this->errors[]  = 'Error Code: ' . $message->code . ' Error Msg: ' . $message->description;
		}

		return;
	}	
}