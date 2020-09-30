<?php
namespace CanadaPost;

/*
Need to override SoapClient because the abstract element 'groupIdOrTransmitShipment' 
is expected to be in the request in order for validation to pass.
So, we give it what it expects, but in __doRequest we modify the request by removing the abstract element and add the correct element.
*/

class CPSoapClient extends \SoapClient {

	function __construct($wsdl, $options = null) {
		parent::__construct($wsdl, $options);
	}

	function __doRequest($request, $location, $action, $version, $one_way = NULL) {
		$dom = new \DOMDocument('1.0');
		$dom->loadXML($request);

		//get element name and values of group-id or transmit-shipment.
		$groupIdOrTransmitShipment =  $dom->getElementsByTagName("groupIdOrTransmitShipment")->item(0);
		$element = $groupIdOrTransmitShipment->firstChild->firstChild->nodeValue;
		$value = $groupIdOrTransmitShipment->firstChild->firstChild->nextSibling->firstChild->nodeValue;

		//remove bad element
		$newDom = $groupIdOrTransmitShipment->parentNode->removeChild($groupIdOrTransmitShipment);

		//append correct element with namespace
		$body =  $dom->getElementsByTagName("shipment")->item(0);
		$newElement = $dom->createElement($element, $value);
		$body->appendChild($newElement);

		//save $dom to string
		$request = $dom->saveXML();

		//echo $request;

		//doRequest
		return parent::__doRequest($request, $location, $action, $version);
	}
}