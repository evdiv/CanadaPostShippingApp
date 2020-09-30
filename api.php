<?php

require_once "./autoloader.php";

//Incoming Parameters 
$jsonData 	= (new CanadaPost\Request())->get(); 


//***************************************************
// Get all Available locations from DB
 
if($jsonData['action'] == "getLocations") {

	$locations = (new CanadaPost\Origin())->getAll();

	echo json_encode($locations); 


//***************************************************
// Get Sender details by location ID

} elseif($jsonData['action'] == "getSenderLocation") {

	$id = !empty($jsonData['Id']) ? $jsonData['Id'] : DEFAULT_LOCATION_ID;
	$location = (new CanadaPost\Origin())->getById($id);

    echo json_encode(array('sender' => $location));


//***************************************************
// Get Receiver details by Order ID

} elseif($jsonData['action'] == "getReceiverByOrderId") { 

	$receiver = (new CanadaPost\Customer())->getByOrderId($jsonData['orderID']);

   	echo json_encode(array('receiver' => $receiver));


//***************************************************
// Get Sender details by Order ID

} elseif($jsonData['action'] == "getSenderByOrderId") { 

	$location = (new CanadaPost\Origin())->getByOrderId($jsonData['orderID']);

    echo json_encode(array('sender' => $location));


//***************************************************
// Get Packages details by Order ID

} elseif($jsonData['action'] == "getPackagesByOrderId") {

	$packages = (new CanadaPost\Shipment())->getPackagesByOrderId($jsonData['orderID']);

    echo json_encode(array('packages' => $packages));


//***************************************************
// Get Available Services

} elseif($jsonData['action'] == "getAvalableServices") {

	$Estimate = new CanadaPost\Estimate($jsonData);
	$Estimate->get();

	echo json_encode(array(
						'services' => $Estimate->services, 
						'errors' => $Estimate->errors
					));


//***************************************************
// Get Available Shipping Boxes

} elseif($jsonData['action'] == "getShippingBoxes") {

	$Shipment = new CanadaPost\Shipment($jsonData);
	$Shipment->getShippingBoxes();

	echo json_encode(array(
						'boxes' => $Shipment->getShippingBoxes(), 
						'errors' => $Shipment->errors
					));


//***************************************************
// Create Shipment

} elseif($jsonData['action'] == "createShipment") { 


	$Shipment = new CanadaPost\Shipment($jsonData);
	$Shipment->create();

	echo json_encode(array(
						'pins' => $Shipment->labels, 
						'errors' => $Shipment->errors
					));


//***************************************************
// Void Shipment by Tracking PIN

} elseif($jsonData['action'] == "voidShipment") {
	
	if(empty($jsonData['shipmentId'])) {
		exit;
	}

	$Shipment = new CanadaPost\Shipment($jsonData);
	$Shipment->void();

	echo json_encode(array(
						'voided' => $Shipment->voided, 
						'errors' => $Shipment->errors
					));


//***************************************************
// Get Shipment Label

} elseif($jsonData['action'] == "printLabel") {

	$Artifact = new CanadaPost\Artifact($jsonData);
	$Artifact->create(); 

	echo json_encode(array(
						'pdfUrl' => $Artifact->pdfUrl, 
						'errors' => $Artifact->errors
					));


//***************************************************
// Get Manifest

} elseif($jsonData['action'] == "getManifest") {


	//Once consolidation completed, the manifest can be produced
	$Manifest = new CanadaPost\Manifest($jsonData);
	$Manifest->create(); 

	//set delay to make sure the manifest data is uploaded
	sleep(1);

	if(count($Manifest->errors) > 0) {

		//Display the error and the last created Manifest
		echo json_encode(array(
			'pdfUrl' => CanadaPost\Artifact::getLastCreatedFileOnServer($type = 'manifests'), 
			'errors' => $Manifest->errors
		));

		exit;
	}

	$Artifact = new CanadaPost\Artifact(array('pin' => $Manifest->manifestArtifacts[0], 'type' => 'manifest'));
	$Artifact->create(); 

	echo json_encode(array(
						'pdfUrl' => $Artifact->pdfUrl, 
						'errors' => $Artifact->errors
					));

//***************************************************
// Create Return Shipment
} elseif($jsonData['action'] == "createReturnShipment") {	


	$ReturnShipment = new CanadaPost\ReturnShipment($jsonData);
	$ReturnShipment->create();
	$ReturnShipment->store();
	
	echo json_encode(array(
						'pins' => $ReturnShipment->labels, 
						'errors' => $ReturnShipment->errors
					));


//***************************************************
// Get All Shipments by selected Date from DB

} elseif($jsonData['action'] == "getShipmentsByDate") { 

	$date = (empty($jsonData['date']) || $jsonData['date'] === "Invalid date") ? date('Y-m-d') : $jsonData['date'];
	$Shipment = new CanadaPost\Shipment();

	echo json_encode(array(
						'shipments' => $Shipment->getByDate($date), 
						'errors' => $Shipment->errors
					));


//***************************************************
// Get Shipment Details from DB by Tracking Identifier

} elseif($jsonData['action'] == "getShipmentDetails") {

	$Shipment = new CanadaPost\Shipment();

	//Depends on the method of Shipment some orders doesn't have Shipment Identifier
	if(empty($jsonData['shipmentId'])) {
		$shipment = $Shipment->getByTrackingNumber($jsonData['shipmentPin']);
	} else {
		$shipment = $Shipment->getByTrackingIdentifier($jsonData['shipmentId']);
	}

	echo json_encode(array(
						'shipment' => $shipment, 
						'errors' => $Shipment->errors
					));


//***************************************************
// Send Email to Customer

} elseif($jsonData['action'] == "sendEmail") {

	if(empty($jsonData['receiverEmail']) || empty($jsonData['pdfLabels'])) {
		exit;
	}

	// Send to Customer
    $SendMail = new CanadaPost\SendMail;
    $SendMail->SenderName  = SITE_NAME;
    $SendMail->SenderEmail = ORDER_CONTACT;
    $SendMail->Subject = "Return Shipment Label - " . $jsonData['receiverName'];
    $SendMail->Body = nl2br($jsonData['receiverEmailBody']);
    $SendMail->AddAttachments("./labels/" . $jsonData['pins'][0] . ".pdf");
    $SendMail->AddRecipients($jsonData['receiverEmail'], $jsonData['receiverEmail']);
	$emailSent = $SendMail->Send();  

	echo json_encode(array('sent' => !!$emailSent));

} elseif($jsonData['action'] == "getGroups") {


//**************************************************
// Get the full list of groups eligible for use in a Transmit Shipments request.

	$Shipment = new CanadaPost\Shipment();
	$Shipment->getGroups();

} elseif($jsonData['action'] == "getManifestForDate") {


//**************************************************
// To retrieve manifests within a given date range

	$Manifest = new CanadaPost\Manifest($jsonData);
	$Manifest->getforDate(); 

	echo json_encode($Manifest->manifestIds);


} elseif($jsonData['action'] == "printManifestId") {


//**************************************************
// Print manifest for a given date

	$Manifest = new CanadaPost\Manifest($jsonData);
	$Manifest->getManifestById();

	if(count($Manifest->errors) > 0) {
		echo json_encode(array('errors' => $Manifest->errors));
		exit;
	}

	sleep(1);

	$Artifact = new CanadaPost\Artifact(array('pin' => $Manifest->manifestArtifacts[0], 'type' => 'manifest'));
	$Artifact->create(); 

	echo json_encode(array(
						'pdfUrl' => $Artifact->pdfUrl, 
						'errors' => $Artifact->errors
					));
}