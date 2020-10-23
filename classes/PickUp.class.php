<?php 

namespace CanadaPost;

class PickUp {

    private $db;
    private $incomingData;
    private $request = [];

    private $soapLocationUri = CP_PICKUP_REQUEST_URL;
    private $wsdlName = 'pickuprequest.wsdl';

    public $response = [];  
    public $errors = array();
    public $schedulledPickUps = array();


    public function __construct($incomingData = '') {

        $this->db = new Database();
        $this->incomingData = $incomingData;
    }


    private function createClient() {

        $wsdl = "./wsdl/" . $this->wsdlName;



        // SSL Options
        $ctx = stream_context_create( array(
                                        'ssl' => array( 
                                                    'verify_peer'=> false, 
                                                    'cafile' => CP_CERT, 
                                                    'CN_match' => CP_HOSTNAME 
                                                )
                                        )
        );  


        $client = new \SoapClient( $wsdl, array(
                                                'location'  => $this->soapLocationUri,
                                                'features' => SOAP_SINGLE_ELEMENT_ARRAYS, 
                                                'stream_context' => $ctx,
                                                'soap_version' => SOAP_1_2
                                            )
        );

        $client->__setSoapHeaders($this->createSoapHeader()); 

        return $client;
    }


    private function createSoapHeader() {

        // Set WS Security UsernameToken
        $WSSENS = CP_WS_SECURITY_TOKEN;
        $usernameToken = new \stdClass(); 
        $usernameToken->Username = new \SoapVar(CP_USERNAME, XSD_STRING, null, null, null, $WSSENS);
        $usernameToken->Password = new \SoapVar(CP_PASS, XSD_STRING, null, null, null, $WSSENS);

        $content = new \stdClass(); 
        $content->UsernameToken = new \SoapVar($usernameToken, SOAP_ENC_OBJECT, null, null, null, $WSSENS);
        $header = new \SOAPHeader($WSSENS, 'Security', $content);

        return $header;
    }


    //***************************************************
    // Get Pickup Availability 

    private function populateGetPickupAvailabilityRequest(){

        $id = getAdminLocationID(DEFAULT_LOCATION_ID);
        $location = (new Origin())->getById($id);

        $this->request = array(
            'get-pickup-availability-request' => array(
                'locale'        => 'EN',
                'postal-code'   => $location['PostalCode']
            )
        );

        return $this->request;
    }


    private function getPickupAvailability() {

        $this->wsdlName = 'pickup.wsdl';
        $this->soapLocationUri = CP_PICKUP_AVAILABILITY_URL;

        try {
            $this->response = $this->createClient()->__soapCall('GetPickupAvailability', $this->populateGetPickupAvailabilityRequest(), NULL, NULL);

            if(isset($this->response->{'pickup-availability'})) {
                $pickup = $this->response->{'pickup-availability'};

                return array(
                    'postal_code' => $pickup->{'postal-code'},
                    'on_demand_tour' => $pickup->{'on-demand-tour'},
                    'on_demand_cut_off' => $pickup->{'on-demand-cutoff'}
                );
            }

            //Handle Errors
            foreach ( $this->response->{'messages'}->{'message'} as $error ) {
                $this->errors[] = 'Error Code: ' . $error->code . ' Error Msg: ' . $error->description;
            }

        } catch(SoapFault $exception) {
            $this->errors[] = ' SOAP Fault Code: ' . trim($exception->faultcode) . ' Error Msg: ' . trim($exception->getMessage());
        }
    }


    //***************************************************
    // Create Pickup Request

    private function populateCreatePickupRequest() {

        $id = getAdminLocationID(DEFAULT_LOCATION_ID);
        $location = (new Origin())->getById($id);

        $this->request = array(
            'create-pickup-request-request' => array(
                'customer-number' => CP_CUSTOMER_NUMBER,
                'locale' => 'EN',
                'pickup-request-details' => array(
                    'pickup-type'   => 'OnDemand',
                    'pickup-location' => array(
                        'business-address-flag' =>  'true'
                    ),
                    'contact-info' => array(
                        'contact-name'  =>  (new Origin())->getMainContactByLocationId($id),
                        'email'         =>  $location['Email'],
                        'contact-phone' =>  $location['RequestPickupPhone']
                    ),
                    'location-details' => array(
                        'pickup-instructions'   =>  $this->incomingData['pickUpLocation']
                    ),
                    'pickup-volume' => $this->incomingData['pickUpTotalPieces'] . ' parcels',
                    'pickup-times' => array(
                        'on-demand-pickup-time' => array(
                            'date'              =>  date("Y-m-d", strtotime($this->incomingData['pickUpDate'])),
                            'preferred-time'    =>  $this->getCorrectTime($this->incomingData['anyTimeAfter']),
                            'closing-time'      =>  $this->getCorrectTime($this->incomingData['untilTime'])
                        )
                    )
                )
            )
        );

        return $this->request;
    }



    public function createPickupRequest() {

        $this->wsdlName = 'pickuprequest.wsdl';
        $this->soapLocationUri = CP_PICKUP_REQUEST_URL;

        try{
            $this->response = $this->createClient()->__soapCall('CreatePickupRequest', $this->populateCreatePickupRequest());

            if(isset($this->response->{'pickup-request-info'})) {

                $pickup = $this->response->{'pickup-request-info'}->{'pickup-request-header'};
                $this->store($pickup->{'request-id'});

                return $pickup->{'request-id'};
            }

            //Handle Errors
            foreach ( $this->response->{'messages'}->{'message'} as $error ) {
                $this->errors[] = 'Error Code: ' . $error->code . ' Error Msg: ' . $error->description;
            }

        } catch(SoapFault $exception) {
            $this->errors[] = ' SOAP Fault Code: ' . trim($exception->faultcode) . ' Error Msg: ' . trim($exception->getMessage());
        }
    }



    private function store($confirmationNumber = ''){

        if(empty($confirmationNumber)) {
            $this->errors[] = 'No Pickup Confirmation Number!';
            return;
        }

        $adminID = !empty($_SESSION['AdminID']) ? $_SESSION['AdminID'] : 0;
        $totalWeight = !empty($this->incomingData['pickUpWeight']) ? $this->incomingData['pickUpWeight'] : 0;
        $totalPieces = !empty($this->incomingData['pickUpTotalPieces']) ? $this->incomingData['pickUpTotalPieces'] : 0; 
        $pickUpDate = !empty($this->incomingData['pickUpDate']) ? date("Y-m-d", strtotime($this->incomingData['pickUpDate'])) : '';
        $untilTime = !empty($this->incomingData['untilTime']) ? $this->incomingData['untilTime']: ''; 
        $anyTimeAfter = !empty($this->incomingData['anyTimeAfter']) ? $this->incomingData['anyTimeAfter'] : '';


        $this->db->query("INSERT INTO SchedulledPickups SET 
            AdminID = '" . $adminID . "', 
            PickUpDate = '" . $pickUpDate . "', 
            TotalWeight = '" . $totalWeight . "', 
            TotalPieces = '" . $totalPieces . "', 
            AnyTimeAfter = '" . $anyTimeAfter . "', 
            UntilTime = '" . $untilTime . "', 
            CourierID = 2, 
            ConfirmationNumber = '" . $confirmationNumber . "'");
    }


    //***************************************************
    // Cancel Pickup Request

    private function populateCancelPickupRequest($confirmationNumber){

        $this->request = array(
            'cancel-pickup-request-request' => array(
                'customer-number'   => CP_CUSTOMER_NUMBER,
                'locale'        => 'EN',
                'request-id'   => $confirmationNumber
            )
        );

        return $this->request;
    }


    private function cancelPickupRequest($confirmationNumber) {

        $this->wsdlName = 'pickuprequest.wsdl';
        $this->soapLocationUri = CP_PICKUP_REQUEST_URL;

        try{
            $this->response = $this->createClient()->__soapCall('CancelPickupRequest', $this->populateCancelPickupRequest($confirmationNumber));

            if(isset($this->response->{'cancel-pickup-success'})) {
                return true;
            }

            //Handle Errors
            foreach ( $this->response->{'messages'}->{'message'} as $error ) {
                $this->errors[] = 'Error Code: ' . $error->code . ' Error Msg: ' . $error->description;
            }

        } catch(SoapFault $exception) {
            $this->errors[] = ' SOAP Fault Code: ' . trim($exception->faultcode) . ' Error Msg: ' . trim($exception->getMessage());
        }
    }


    public function getSchedulledPickup(){

        $adminID = !empty($_SESSION['AdminID']) ? $_SESSION['AdminID'] : 0;
        $this->schedulledPickUps = array();

        $result = $this->db->query("SELECT * FROM SchedulledPickups 
                                    WHERE AdminID = " . $adminID . " 
                                    AND CourierID = 2 
                                    AND PickUpDate >= NOW()");

        if($result) {
            while ($row = $result->fetch_assoc()) {
                $this->schedulledPickUps[] = $row;
            }
        }

        return $this->schedulledPickUps;
    }


    public function cancelSchedulledPickup($confirmationNumber = ''){
        if(empty($confirmationNumber)) {
            $this->errors[] = 'No Pickup Confirmation Number!';
            return;
        }

        if(!$this->cancelPickupRequest($confirmationNumber)) {
            return;
        }

        $adminID = !empty($_SESSION['AdminID']) ? $_SESSION['AdminID'] : 0;

        $this->db->query("DELETE FROM SchedulledPickups 
                            WHERE AdminID = " . $adminID . " 
                            AND CourierID = 2 
                            AND ConfirmationNumber = '" . $confirmationNumber . "'
                            LIMIT 1 ");
    }



    private function getCorrectTime($hourMinutes = '00:00') {
        $hour = substr($hourMinutes, 0, 2);
        $minutes = intval(substr($hourMinutes, -2));

        if($minutes >= 45) {
            $minutes = ':45';
        } elseif ($minutes >= 30) {
            $minutes = ':30';
        } elseif($minutes >= 15) {
            $minutes = ':15';
        } else {
            $minutes = ':00';
        }

        return $hour  . $minutes;
    }
}