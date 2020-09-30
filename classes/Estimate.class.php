<?php 
namespace CanadaPost;

class Estimate {

	private $db;
	private $incomingData;
	private $client;
	private $request;
	private $response;	
	private $package = array();

	public $services = array();
	public $rates = array();
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
													'verify_peer'=> false, 
													'cafile' => CP_CERT, 
													'CN_match' => CP_HOSTNAME 
												)
										)

		);	


  		$client = new \SoapClient( "./wsdl/rating.wsdl", array(
						                                    'location'	=>	CP_ESTIMATING_URL,
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


	private function populateRequest() {

		$senderPostalCode = strtoupper(str_replace(' ', '', $this->incomingData['senderPostalCode']));
		$receiverPostalCode = strtoupper(str_replace(' ', '', $this->incomingData['receiverPostalCode']));

		$this->request = array(
	    	'get-rates-request' => array(
				'locale' => 'EN',
				'mailing-scenario' => array(
					'customer-number' => CP_CUSTOMER_NUMBER,
					'parcel-characteristics' => array( 

						'weight' => $this->package['weight'],
						'dimensions' => array(
							'length' => $this->package['length'],
							'width' => $this->package['width'], 
							'height' =>  $this->package['height']
						)
					),
					'origin-postal-code' => $senderPostalCode,
					'destination' => array( 'domestic' => array( 'postal-code' => $receiverPostalCode ))
				)
			)
		);

		return $this->request;
	}



	public function get() {

		foreach($this->incomingData['packages'] as $package) {
			$this->package = $package;

			$this->getFullEstimate();
			$this->getErrors();
			$this->getServicesRates();
		}
	}



	private function getFullEstimate() {

		$this->populateRequest();

		try {
			$this->response = $this->client->__soapCall('GetRates', $this->request, NULL, NULL);

		} catch(Exception $e) {

			$this->errors[] = 'Fault Code: ' . trim($e->faultcode) . ' Msg: ' . trim($e->getMessage());
		}
	}


	private function getErrors(){

		if(isset($this->response->{'messages'})) {
			foreach($this->response->{'messages'}->{'message'} as $error){
				$this->errors[] = $error->code . ' ' . $error->description;
			}
		}

		return count($this->errors);
	}


	private function getServicesRates() {

		if(!isset($this->response->{'price-quotes'})) {
			return;
		}

		$rates = array();

		foreach ($this->response->{'price-quotes'}->{'price-quote'} as $serv) {
			$rates[] = array(
            			'service_name' => $serv->{'service-name'},
            			'charge' => $serv->{'price-details'}->{'due'}
            );
		}

		//Sort Services with the cheapest first
		usort($rates, function($a, $b) {
			if($a['charge'] == $b['charge']) {
				return 0;
			}
			return ($a['charge'] < $b['charge']) ? -1 : 1;
		});

		
		$this->services[] = array(
			'package_reference' => $this->package['reference'],
			'package_weight' => $this->package['weight'],
			'rates' => $rates
		);

		return count($this->services);
	}
}