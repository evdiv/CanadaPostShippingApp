<?php 
namespace CanadaPost;

class Artifact {

	private $db;
	private $incomingData;
	private $client;
	private $request;
	private $response;	

	public $pdfUrl;
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


  		$client = new \SoapClient( "./wsdl/artifact.wsdl", array(
						                                    'location'	=>	CP_ARTIFACT_URL,
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

		$artifactId = intval($this->incomingData['pin']);

		$this->request = array(
	    	'get-artifact-request' => array(
				'locale'			=> CP_LOCALE,
				'mailed-by'			=> CP_CUSTOMER_NUMBER,
				'artifact-id'		=> $artifactId,
				'page-index'		=> '0'				
			)
		);
		return $this->request;
	}



	public function create() {

		$this->populateRequest();

		try {
			$this->response = $this->client->__soapCall('GetArtifact', $this->request, NULL, NULL);

		} catch(Exception $e) {

			$this->errors[] = 'Fault Code: ' . trim($e->faultcode) . ' Msg: ' . trim($e->getMessage());
			return;
		}

		return $this->parseResponse($this->response);
	}



	private function getArtifactType() {
		$type = 'label';
		if(!empty($this->incomingData['type']) && $this->incomingData['type'] == 'manifest') {
			$type = 'manifest';
		}

		return $type;
	}

	private function parseResponse($response) {

		if (isset($response->{'artifact-data'})) {

			if (strpos($response->{'artifact-data'}->{'mime-type'}, 'application/pdf' ) !== FALSE ) {
				$labelName = $this->incomingData['pin'] .'.pdf';
			} 

			$label64BaseSring = $response->{'artifact-data'}->{'image'};
			$this->pdfUrl = self::getFilePathOnServer($label64BaseSring, $labelName, $this->getArtifactType());

			// set timeout to make sure that the file is created on the Server.
			sleep(1);
		} 

		if(isset($response->{'messages'}->{'message'})) {
			foreach ( $response->{'messages'}->{'message'} as $message ) {
				$this->errors[]  = 'Error Code: ' . $message->code . ' Error Msg: ' . $message->description;
			}
		}

		return $this;
	}	


	public function cropLabel() {
		if(empty($this->pdfUrl)) {
			return;
		}

		//for resolution: 400x400
		$width = 1700;
		$height = 2300;
		$startX = 2550;
		$startY = 400;


		$tmpImg = APP_PATH . '/labels/tmp.jpg';


		$image = new \Imagick(); 
		$image->setResolution(400,400);
		$image->readImage($this->pdfUrl);
		$image->setImageFormat('jpeg');
		$image->cropImage($width, $height, $startX, $startY);
		$image->writeImage($tmpImg);

		$image->readImage($tmpImg);
		$image->setImageFormat('pdf');
		$image->writeImage($this->pdfUrl);

		$image->clear(); 
		$image->destroy();

	}

	public static function getFilePathOnServer($label64BaseSring = '', $labelName  = '', $type = 'label') {
		if(empty($label64BaseSring) || empty($labelName)) {
			return '';
		}

		$labelPath = ($type == 'manifest') ? "./manifests/" : "./labels/"; 
		$labelPath = $labelPath . $labelName;

	    file_put_contents($labelPath, base64_decode($label64BaseSring));

	    return $labelPath;
	}


	public static function getLastCreatedFileOnServer($type = 'manifests') {

		$folder = './'.$type . '/';
		$files = scandir($folder, SCANDIR_SORT_DESCENDING);
		$newest_file = $files[0];

		return $folder . $newest_file;
	}




}