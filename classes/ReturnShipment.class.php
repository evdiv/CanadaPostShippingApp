<?php 

namespace CanadaPost;

class ReturnShipment {

    private $db;
    private $incomingData;
    private $client; 
    private $request;
    public $response;  

    public $errors = array();
    public $pins = array();
    public $labels = array();


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
                                            )
                                        )
                                    );  


        $client = new \SoapClient( "./wsdl/authreturn.wsdl", array(
                                        'location'  =>  CP_AUTHRETURN_URL,
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
        $this->request = array(
            'create-authorized-return-request' => array(
                'locale'            => CP_LOCALE,
                'mailed-by'         => CP_CUSTOMER_NUMBER,
                'authorized-return'         => array(
                    'service-code'      => 'DOM.EP',
                    'returner'          => $this->populateReturner(),
                    'receiver'          => $this->populateReceiver(),

                    'parcel-characteristics'    => array(
                        'weight'        => $this->populateTotalWeight(),
                    ),
                    'print-preferences'     => array(
                        'encoding'  => 'PDF'
                    ),
                    'references' => array(
                        'customer-ref-1' => !empty($this->incomingData['orderID']) ? $this->incomingData['orderID'] : '',
                    ),

                    'settlement-info'   => array(
                        'contract-id'   => CP_AGREEMENT_NUMBER,
                        'paid-by-customer'              => CP_PAID_BY_CUSTOMER, 
                        'intended-method-of-payment'    => CP_PAYMENT_METHOD
                    )                                                                       
                )
            )
        );
    }


    public function create() {

        $this->populateRequest();

        try {
            $this->response = $this->client->__soapCall('CreateAuthorizedReturn', $this->request, NULL, NULL);
            $this->parseResponse($this->response);

        } catch(Exception $e) {

            $this->errors[] = 'Fault Code: ' . trim($e->faultcode) . ' Msg: ' . trim($e->getMessage());
        }
    }


    public function store() {

        $orderID = !empty($this->incomingData['orderID']) ? $this->incomingData['orderID'] : '';
    
        $locationCode = !empty($this->incomingData['senderLocationCode']) ? $this->incomingData['senderLocationCode'] : '';
        $serviceID = !empty($this->incomingData['serviceID']) ? $this->incomingData['serviceID'] : '';

        $counter = 0;
        foreach ($this->labels as $label) {

            $packageSQL = '';

            if(empty($label)) { 
                continue;
            }

            if(!empty($this->incomingData['packages'][$counter])) {
                $packageSQL = " Length = " . $this->incomingData['packages'][$counter]['length'] . ", ";
                $packageSQL .= " Width = " . $this->incomingData['packages'][$counter]['width'] . ", ";
                $packageSQL .= " Height = " . $this->incomingData['packages'][$counter]['height'] . ", ";
                $packageSQL .= " Weight = " . $this->incomingData['packages'][$counter]['weight'] . ", ";               
                $packageSQL .= " Reference = '" . $this->incomingData['packages'][$counter]['reference'] . "', ";               
                $packageSQL .= " Note = '" . $this->incomingData['packages'][$counter]['note'] . "', ";             
            }


            $sql = "INSERT INTO TrackingReturnsInfo SET 
                                OrderID = '" . $orderID . "', 
                                TrackingCarrierID = 1, 
                                TrackingCode = '" . $label . "', 
                                LocationCode = '" . $locationCode . "',  
                                " . $packageSQL . "
                                CourierService = '" . $serviceID . "'";

            $this->db->query($sql);
            $counter++;
        }
    }


    //In the Return Shipment Customer is a Returner
    private function populateReturner() {

        return array(
            'name'      => sanitize($this->incomingData['receiverName']),
            'company'   => sanitize($this->incomingData['receiverName']),   
            'domestic-address'  => array(
                'address-line-1'    => sanitize($this->incomingData['receiverStreetNumber'] . ' ' . $this->incomingData['receiverStreetName']),
                'city'              => sanitize($this->incomingData['receiverCity']),  
                'province'      => $this->incomingData['receiverProvince'],    
                'postal-code'   => strtoupper(str_replace(' ' , '', $this->incomingData['receiverPostalCode'])) 
            )
        );
    }


    //In the Return Shipment Your Comany is a Receiver
    private function populateReceiver() {

        return array(
            'name'      => COMPANY_NAME,  
            'company'   => COMPANY_NAME, 
            'domestic-address'  => array(
                'address-line-1'    => sanitize($this->incomingData['senderStreetNumber'] . ' ' . $this->incomingData['senderStreetName']),  
                'city'              => sanitize($this->incomingData['senderCity']),    
                'province'      => $this->incomingData['senderProvince'],    
                'postal-code'   => strtoupper(str_replace(' ', '', $this->incomingData['senderPostalCode'])) 
            )                   
        );
    }


    private function populateTotalWeight() {
        if (isset($this->incomingData['totalWeight']) && $this->incomingData['totalWeight'] > 1) {
            return $this->incomingData['totalWeight'];
        } 
        return 1;
    }



    private function parseResponse($response) {

        if(isset($response->{'authorized-return-info'})) {
            $this->trackingPin = $response->{'authorized-return-info'}->{'tracking-pin'};

            foreach ( $response->{'authorized-return-info'}->{'artifacts'}->{'artifact'} as $artifact ) {  
                $this->labels[] = $artifact->{'artifact-id'};
            }

            return $this->labels;

        } else {
            foreach ( $response->{'messages'}->{'message'} as $message ) {
                $this->errors[]  = 'Error Code: ' . $message->code . ' Error Msg: ' . $message->description;
            }
        }
    }
}