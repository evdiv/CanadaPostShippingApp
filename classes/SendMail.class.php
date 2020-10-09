<?php 
namespace CanadaPost;

class SendMail
{
	private $incomingData;
	private $mimeBoundary = '';
	private $body = '';


	function __construct($incomingData = '') {
		$this->incomingData = $incomingData;
	}


	public function send(){
		return mail($this->getRecipient(), $this->getSubject(), $this->getBody(), $this->getHeaders());
	}


	private function getMimeBoundary(){
		if(!empty($this->mimeBoundary)) {
			return $this->mimeBoundary;
		}

		$this->mimeBoundary = "==Multipart_Boundary_x" . md5(time()) . "x"; 
		return $this->mimeBoundary;
	}


	private function getHeaders() {

		$headers  = "From: " . SITE_NAME . " <" . ORDER_CONTACT . ">\r\n";
		$headers .= "Reply-To: " . SITE_NAME . " <" . ORDER_CONTACT . ">\r\n";
		$headers .= "MIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . "	boundary=\"{$this->getMimeBoundary()}\""; 

		return $headers;
	}



	private function getRecipient(){
		return $this->incomingData['receiverName'] . " <" . $this->incomingData['receiverEmail'] . ">";
	}



	private function getSubject(){
		return "Return Shipment Label - " . $this->incomingData['receiverName'];
	}


	private function getBody() {

		$emailText = nl2br($this->incomingData['receiverEmailBody']);

		$body = "This is a multi-part message in MIME format.\n\n" .     
					"--{$this->getMimeBoundary()}\n" .     
					"Content-Type: text/html; charset=\"iso-8859-1\"\n" .     
					"Content-Transfer-Encoding: 7bit\n\n" .  $emailText . "\n\n";

		// handle attachments
		if(count($this->incomingData['pins'])){

			foreach($this->incomingData['pins'] as $pin){ 
				$attachedFile = "./labels/" . $pin . ".pdf";

				$file = fopen($attachedFile, "rb");
				$data = fread($file,filesize($attachedFile));
				fclose($file);

				$data = chunk_split(base64_encode($data));
				$fileType = mime_content_type($attachedFile);

				$body .= "--{$this->getMimeBoundary()}\n" .  
						"Content-Type: {$fileType};\n" .     
						" name=\"" . basename($attachedFile) . "\"\n" .     
						"Content-Disposition: attachment;\n" .     
						" filename=\"" . basename($attachedFile) . "\"\n" .     
						"Content-Transfer-Encoding: base64\n\n" .     $data . "\n\n";
			}

			$body .= "--{$this->getMimeBoundary()}--\n";
		} 

		return $body;					
	}
}