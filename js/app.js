var app = new Vue({
  				el: '#app', 
				data: {

					//New Shipment Sender
					locations: [],
					senderLocationName: '',
					senderLocationId: '',
					senderId: '',
					senderName: '',
					senderCompany: '',
					senderStreetNumber: '',
					senderStreetName: '',
					senderCity: '',
					senderAddress: '',
					senderPostalCode: '',
					senderPhoneCountryCode: '1',
					senderPhoneAreaCode: '', 
					senderPhone: '',
					senderProvince: '',
					senderCountry: 'CA',

					//New Shipment Receiver
					incomingOrderId: $('#incomingOrderId').val(),
					orderId: '',
					orderActive: '', 
					receiverCode: '',
					receiverCountry: 'CA',
					receiverName: '',
					receiverAttentionTo: '',
					receiverStreetNumber: '',
					receiverStreetName: '',			
					receiverAddress2: '',
					receiverAddress3: '',
					receiverCity: '',
					receiverProvince: '',
					receiverPostalCode: '',
					receiverPhoneAreaCode: '',
					receiverPhone: '',
					receiverPhoneExtension: '',
					receiverFaxNumber: '',
					receiverEmail: '',
					receiverEmailBody: '',
					specialInstructions: 'No Redirects Permitted',
					sigRequired: true,

					courierSelected: '',
					courierService: '',
					shipDirect: 0,

					packages: [],
					services: [],
					selectedService: '',
					boxes: [],
					selectedBoxId: '',
					orders: [],
					ordersDate: '',

					//Search parameters
					searchOrder: {},
					orderIDSearch: '',
					trackingNumberSearch: '',
					packageReferenceSearch: '',
					phoneNumberSearch: '',
					customerNameSearch: '',
					displaySearchResults: false,

					//Completed Shipment Details
					pins: [],
					pdfLabels: [],
					shipmentId: '',
					shipmentPin: '',
					shipmentCreated: '',
					shipmentService: '',
					shipmentOrderId: '',
					shipmentReceiverName: '',
					shipmentReceiverAddress: '',
					shipmentReceiverCity: '',
					shipmentReceiverPostalCode: '',
					shipmentSenderAddress: '',
					shipmentSenderCity: '',
					shipmentSenderId: '',
					shipmentSenderPostalCode: '',
					shipmentVoided: 0,


					//PickUp Service
					pickUps: [],
					pickUpDate: '',
					anyTimeAfter: '12:00',
					untilTime: '17:00',
					pickUpTotalPieces: 1,
					pickUpLocation: 'Use Back Door',


					//Manifest
					manifestDate: '',
					manifestType: '',
					manifestDescription: '',	
					manifestStatus: '',					
					manifestPdfUrl: '',
					manifests: [],


					areRatesVisible: 0,
					displayLoadServicesSpinner: 0,
					displayLoadShipmentSpinner: 0,
					displayVoidShipmentSpinner: 0,
					displayLoadManifestSpinner: 0,
					emailToCustomerSent: 0,

					voidShipmentId: '',
					voidShipmentPin: '',
					voidShipmentDate: '',

					//Admin section
					retriveManifestForDate: '',
					printManifestId: '',
					//end Admin

					formsValidated: false,
					errors: [],
					confirmation: '',
					pdfUrl: '',
					injectPdfInElementId: '#shipment-label',
					width: '',
					length: '',
					height: '',
					weight: '',
					reference: '',
					note: '',
					returnShipmentState: false,
				    message: 'You loaded this page on ' + new Date().toLocaleString()
				},

				computed: {

					shipmentLoader: function() {
						return (this.isShipmentLoaderVisible > 0 && this.shipmentPin.length !== '') ? true : false;
					},

					getDateForHumans: function() {
						return moment(this.ordersDate).format("LL");
					},

					widthInches: function() {
						return this.convertCmToInches(this.width);
					},

					lengthInches: function() {
						return this.convertCmToInches(this.length);
					},

					heightInches: function() {
						return this.convertCmToInches(this.height);
					},

					weightLbs: function() {
						return this.convertKgToLbs(this.weight);
					},

					availableServiceNames: function() {
						if(this.services.length > 0) {

							this.selectedService = this.services[0].rates[0].service_name;
							return this.services[0].rates;
						}
						return [];
					}

				},

				mounted: function() {

					this.getSenderLocation();
					this.getLocations();
					this.getOrders(); 
					this.activateDatePicker();
					this.setCurrentDate();
					this.getShippingBoxes();


					if(this.incomingOrderId !== '0') {
						this.orderId = this.incomingOrderId;
						this.searchShipmentByOrderId();
					} 

				},

				methods: {

					getSelectedDate: function() {
						this.ordersDate = this.$refs.dateField.value;
						return moment(this.ordersDate);
					},

					getScheduledPickupDate: function() {
						return moment(this.pickUpDate).format("YYYY-MM-DD");
					},

					setCurrentDate: function() {
						this.ordersDate = moment().format('L');
					},

					getTotalWeight: function() {
						var totalWeight = 0;
						this.packages.forEach(function(package) {
							totalWeight += parseFloat(package.weight);
						});

						return totalWeight;
					},

					convertCmToInches: function(cm) {
						var inches = (cm > 0) ? (cm * 1)/2.54 : 0;
						return inches.toFixed(2);
					},

					convertKgToLbs: function(kg) {
						var lb = (kg > 0) ? 2.2 * kg : 0;
						return lb.toFixed(2); 
					},

					constrainInputWeight: function(event) {
						var weight = parseInt(event.target.value);
						this.weight = (weight > 30) ? 30 : weight;
					},

					getTotalPieces: function() {

						return this.packages.length;
					},					


					isEmpty: function(string) {

						return (string === '' || string === 0) ? true : false;
					},


					setPackageSizesBySelectedBox: function() {
						var self = this;

						this.boxes.forEach(function(box) {
							if(box.id === self.selectedBoxId) {
								self.length = box.length;
								self.width = box.width;
								self.height = box.height;
							}
						});
					},


					addPackage: function() {

						if(this.weight === '' || this.length === '' || this.width === '' || this.height === '') {
							return;
						}

						this.packages.push({
							'weight': this.weight,
							'length': this.length,
							'width': this.width,
							'height': this.height,
							'reference': this.reference,
							'note': this.note
						});

						this.clearPackageForm();
						this.getAvailableServices();
					},


					removePackage: function(index) {
						this.packages.splice(index, 1);

						this.getAvailableServices();
					},


					clearPackageForm: function() {
						this.selectedBoxId = '';
						this.weight = '';
						this.length = ''; 
						this.width = '';
						this.height = '';
						this.reference = '';
						this.note = '';
					},


					getLocations: function() {
						var self = this;

						axios.post("", {

							action: "getLocations"
						})
						.then(function (response) {
							self.locations = response.data;
  						});
					},

					getSenderLocation: function() {
						var self = this;

						this.resetAvailableServices();

						axios.post("", {

							action: "getSenderLocation",
							Id: this.senderId
						})
						.then(function (response) {
    						self.populateSender(response.data.sender);
  						});
					},

					getLocationsForReturnLabel: function() { 
						var self = this;

						axios.post("", {

							action: "getSenderLocation",
							Id: this.senderId
						})
						.then(function (response) {
    						self.populateSender(response.data.sender);
  						});
					},

					searchShipmentByOrderId: function() {

						if(this.orderId == '') {
							return;
						}

						this.getReceiverByOrderId();
						this.getSenderByOrderId();
						this.getPackagesByOrderId();
					},


					getReceiverByOrderId: function() {
						var self = this;

						axios.post("", {

							action: "getReceiverByOrderId",
							orderID: this.orderId
						})
						.then(function (response) {
    						self.populateReceiver(response.data.receiver);
  						});
					},


					getSenderByOrderId: function() {
						var self = this;

						axios.post("", {

							action: "getSenderByOrderId",
							orderID: this.orderId
						})
						.then(function (response) {
							self.populateSender(response.data.sender);
  						});
					},


					getPackagesByOrderId: function() { 
						var self = this;

						// Don't get packages if the destination is the Store Location 
						if(this.orderId.length == 4 && this.orderId.toLowerCase().charAt(0) === 'l' ) {
							return;
						}


						axios.post("", {

							action: "getPackagesByOrderId", 
							orderID: this.orderId 
						})
						.then(function (response) {

							if( response.data.packages.length > 0) {

    							self.packages = response.data.packages;
    							self.getAvailableServices();
							}
  						});
					},


					getShippingBoxes: function() { 
						var self = this;

						axios.post("", {

							action: "getShippingBoxes"
						})
						.then(function (response) {

							if( response.data.boxes.length > 0) {

    							self.boxes = response.data.boxes;
							}
  						});						

					},


                    populateSender: function(sender) {

						if(!sender || typeof sender == "undefined" || sender.Id === '') {
							return;
						}

                    	this.senderId = sender.Id;
                    	this.senderLocationId = sender.LocationId;
						this.senderName = sender.Name;
						this.senderCompany = sender.Company;
						this.senderStreetNumber = sender.StreetNumber;
						this.senderStreetName = sender.StreetName;
						this.senderCity = sender.City;
						this.senderPostalCode = sender.PostalCode;
						this.senderPhoneAreaCode = sender.PhoneAreaCode;
						this.senderPhone = sender.Phone;
						this.senderProvince = sender.Province;
						this.senderLocationName = sender.LocationName;
                    }, 


					populateReceiver: function(receiver) {

						if(!receiver || typeof receiver == "undefined") {
							return;
						}

						this.receiverCode = receiver.CustomerCode;
						this.orderActive = receiver.OrderActive;
						this.receiverCountry = receiver.Country;
						this.receiverName = receiver.ShippingName;
						this.receiverAttentionTo = receiver.ShippingName;
						this.receiverStreetNumber = receiver.StreetNumber;
						this.receiverStreetName = receiver.StreetName;
						this.receiverAddress2 = receiver.AddressLine2;
						this.receiverAddress3 = receiver.AddressLine3;
						this.receiverCity = receiver.City;
						this.receiverProvince = receiver.ProvinceCode;
						this.receiverPostalCode = receiver.PostalCode;
						this.receiverPhoneAreaCode = receiver.PhoneAreaCode;
						this.receiverPhone = receiver.Phone;
						this.receiverEmail = receiver.Email;

						this.courierSelected = receiver.CourierSelected || '';
						this.courierService = receiver.CourierService || '';
						this.shipDirect = receiver.shipDirect || 0;

						// Rewrite this parameter if we obtained the real customer with the address
						if(this.receiverName.length > 0 && this.receiverStreetName.length > 0) {
							this.sigRequired = !!receiver.sigRequired;
						}
						
					},


					displayRates: function() {

						this.getAvailableServices();
						this.areRatesVisible = 1;
					},


					resetManifest: function() {

						this.manifestDate = "";
						this.manifestType = "";
						this.manifestDescription = "";	
						this.manifestStatus = "";					
						this.manifestPdfUrl = "";
					},


					resetAvailableServices: function() {
						this.services =  [];
						this.selectedService = '';
						this.errors = [];
					},

					resetReceiver: function() {
						this.orderId = '';
						this.receiverCode = '';
						this.receiverCountry = 'CA';
						this.receiverName = '';
						this.receiverAttentionTo = '';
						this.receiverStreetNumber = '';
						this.receiverStreetName = '';			
						this.receiverAddress2 = '';
						this.receiverAddress3 = '';
						this.receiverCity = '';
						this.receiverProvince = '';
						this.receiverPostalCode = '';
						this.receiverPhoneAreaCode = '';
						this.receiverPhone = '';
						this.receiverPhoneExtension = '';
						this.receiverFaxNumber = '';
						this.receiverEmail = '';
						this.packages = [];

					},


					getAvailableServicesFormValidation: function() {
						this.errors = [];

						if(this.senderPostalCode === '') { this.errors.push("Sender Postal Code is required"); }
						if(this.receiverCity === '') { this.errors.push("Customer City is required"); }
						if(this.receiverProvince === '') { this.errors.push("Customer Province is required"); }
						if(this.receiverPostalCode === '') { this.errors.push("Customer Postal Code is required"); }
						if(this.getTotalPieces() === 0) { this.errors.push("At least one Package is required"); }

						return (this.errors.length === 0) ? true : false;
					},


					getAvailableServices: function() {
						var self = this;

						this.cleanPostalCodes();

						if(!this.getAvailableServicesFormValidation()) {
							return false;
						}

						this.displayLoadServicesSpinner = 1;
						this.resetAvailableServices();	

						axios.post("", {
		
							action: "getAvalableServices",

							//Sender
							senderStreetNumber: this.senderStreetNumber, 
							senderStreetName: this.senderStreetName, 
							senderCity: this.senderCity, 
							senderProvince: this.senderProvince, 
							senderPostalCode: this.senderPostalCode.replace(/\s/g, ""),
							senderPhoneAreaCode: this.senderPhoneAreaCode, 
							senderPhone: this.senderPhone,  

							//Receiver
							receiverName: this.receiverName, 
							receiverStreetNumber: this.receiverStreetNumber, 
							receiverStreetName: this.receiverStreetName, 
							receiverPhoneAreaCode: this.receiverPhoneAreaCode,   
							receiverPhone: this.receiverPhone,   
							receiverCity: this.receiverCity, 
							receiverProvince: this.receiverProvince, 
							receiverPostalCode: this.receiverPostalCode.replace(/\s/g, ""),

							//Packages
							packages: this.packages,
							totalWeight: this.getTotalWeight(),
							totalPieces: this.getTotalPieces()
						})

						.then(function (response) {

							if( response.data.services.length > 0) {

								self.services = response.data.services;
								self.displayLoadServicesSpinner = 0;

								return true;
							}

							self.handleErrors(response.data.errors);
							self.displayLoadServicesSpinner = 2;

  						});
					},


					resetShipmentDetails: function() {

						this.createShipmentFormValidation();

						this.pins = [];
						this.shipmentPin = '';
						this.pdfUrl = '';
						this.injectPdfInElementId = '#shipment-label';
					},


					createShipmentFormValidation: function() {
						this.errors = [];

						if(this.senderName === '') { this.errors.push("Sender Name is required"); }
						if(this.senderPostalCode === '') { this.errors.push("Sender Postal Code is required"); }
						if(this.senderStreetNumber === '') { this.errors.push("Sender Street Number is required"); }
						if(this.senderStreetName === '') { this.errors.push("Sender Street Name is required"); }
						if(this.senderCity === '') { this.errors.push("Sender City is required"); }
						if(this.senderProvince === '') { this.errors.push("Sender Province is required"); }
						if(this.senderPhoneAreaCode === '') { this.errors.push("Sender Phone Area is required"); }						
						if(this.senderPhone === '') { this.errors.push("Sender Phone is required"); }		


						if(this.receiverName === '') { this.errors.push("Customer Name is required"); }
						if(this.receiverCity === '') { this.errors.push("Customer City is required"); }
						if(this.receiverStreetNumber === '') { this.errors.push("Customer Street Number is required"); }
						if(this.receiverStreetName === '') { this.errors.push("Customer Street Name is required"); }
						if(this.receiverProvince === '') { this.errors.push("Customer Province is required"); }
						if(this.receiverPhoneAreaCode === '') { this.errors.push("Customer Phone Area is required"); }						
						if(this.receiverPhone === '') { this.errors.push("Customer Phone is required"); }	

						if(this.receiverPostalCode === '') { this.errors.push("Customer Postal Code is required"); }


						if(this.services.length === 0 && !this.returnShipmentState) { this.errors.push("At least one Service should be selected");}
						if(this.getTotalPieces() === 0 && !this.returnShipmentState) { this.errors.push("At least one Package is required"); }

						this.formsValidated = (this.errors.length === 0) ? true : false;
						return this.formsValidated;
					},

					cleanPostalCodes: function() {

						this.receiverPostalCode = this.receiverPostalCode.replace(/[^a-zA-Z0-9]/g, '');
						this.senderPostalCode = this.senderPostalCode.replace(/[^a-zA-Z0-9]/g, '');
					},


					createShipment: function() {
						var self = this;

						this.cleanPostalCodes();

						if(!this.createShipmentFormValidation()) {
							return false;
						}

						this.pins = [];
						this.displayLoadShipmentSpinner = 1;
						this.shipmentPin = '';
						this.pdfUrl = '';
						this.injectPdfInElementId = '#shipment-label';
						this.returnShipmentState = false;


						axios.post("", {
		
							action: "createShipment", 

							//Sender
							senderLocationId: this.senderLocationId,
							senderName: this.senderName,
							senderCompany: this.senderCompany,
							senderStreetNumber: this.senderStreetNumber, 
							senderStreetName: this.senderStreetName, 
							senderCity: this.senderCity, 
							senderProvince: this.senderProvince, 
							senderCountry: this.senderCountry, 
							senderPostalCode: this.senderPostalCode, 
							senderPhoneAreaCode: this.senderPhoneAreaCode, 
							senderPhone: this.senderPhone,  

							//Receiver
							receiverCode: this.receiverCode, 
							receiverName: this.receiverName, 
							receiverCompany: this.receiverName, 
							receiverStreetNumber: this.receiverStreetNumber, 
							receiverStreetName: this.receiverStreetName, 
							receiverAddress2: this.receiverAddress2, 
							receiverAddress3: this.receiverAddress3, 
							receiverPhoneAreaCode: this.receiverPhoneAreaCode,   
							receiverPhone: this.receiverPhone,   
							receiverPhoneExtension: this.receiverPhoneExtension,   
							receiverFaxNumber: this.receiverFaxNumber, 
							receiverEmail: this.receiverEmail,  	
							receiverCity: this.receiverCity, 
							receiverProvince: this.receiverProvince, 
							receiverCountry: this.receiverCountry,
							receiverPostalCode: this.receiverPostalCode,  
							specialInstructions: this.specialInstructions,
							sigRequired: this.sigRequired,

							//Packages
							packages: this.packages,
							totalWeight: this.getTotalWeight(),
							totalPieces: this.getTotalPieces(),

							//Order ID
							orderID: this.orderId,

							//Selected Service
							serviceID: this.selectedService
						})
						.then(function (response) {

							self.displayLoadShipmentSpinner = 0;

							console.log("In the create shipment response");
							console.log(response);

							if( response.data.errors.length === 0) {
								self.pins = response.data.pins;

								for (var i = 0; i < self.pins.length; i++) { 
  									self.getLabel(self.pins[i], '#shipment-labels-' + i);
								}

								self.getOrders();
								self.activateTab('history');
								self.resetAvailableServices();
								self.resetReceiver();

								return true;
							}

							self.handleErrors(response.data.errors); 

  						});
					},

					setReturnShipmentState: function() {
						var self = this;
						this.returnShipmentState = true;	

						axios.post("", {

							action: "getSenderLocation",
							returnShipment: 1,
							Id: this.senderId
						})
						.then(function (response) {
    						self.populateSender(response.data.sender);
							self.populateReturnEmailBody();
							self.resetShipmentDetails();

  						});
					},


					populateReturnEmailBody: function() {
						this.receiverEmailBody = "Attached is your Canada Post return label. ";
						this.receiverEmailBody += "Please print it, tape it to your parcel, and drop it off at your local Canada Post office. ";
						this.receiverEmailBody += "When we receive your item we will process your return. This could take up to 15 business days. ";
						this.receiverEmailBody += "\n\nThank you for choosing us.";
					},

					createReturnShipment: function() {
						var self = this;

						this.errors = [];
						this.pins = [];
						this.emailToCustomerSent = 0;
						this.displayLoadShipmentSpinner = 1;

						axios.post("", {
		
							action: "createReturnShipment",

							senderLocationId: this.senderLocationId,
							senderName: this.senderName,
							senderCompany: this.senderCompany,
							senderStreetNumber: this.senderStreetNumber, 
							senderStreetName: this.senderStreetName, 
							senderCity: this.senderCity, 
							senderProvince: this.senderProvince, 
							senderCountry: this.senderCountry, 
							senderPostalCode: this.senderPostalCode, 
							senderPhoneAreaCode: this.senderPhoneAreaCode, 
							senderPhone: this.senderPhone,  

							receiverCode: this.receiverCode, 
							receiverName: this.receiverName, 
							receiverCompany: this.receiverName, 
							receiverStreetNumber: this.receiverStreetNumber, 
							receiverStreetName: this.receiverStreetName, 
							receiverAddress2: this.receiverAddress2, 
							receiverAddress3: this.receiverAddress3, 
							receiverPhoneAreaCode: this.receiverPhoneAreaCode,   
							receiverPhone: this.receiverPhone,   
							receiverPhoneExtension: this.receiverPhoneExtension,   
							receiverFaxNumber: this.receiverFaxNumber, 
							receiverEmail: this.receiverEmail,  	
							receiverCity: this.receiverCity, 
							receiverProvince: this.receiverProvince, 
							receiverCountry: this.receiverCountry,
							receiverPostalCode: this.receiverPostalCode,  
							specialInstructions: this.specialInstructions,

							//Packages
							packages: this.packages,
							totalWeight: this.getTotalWeight(),
							totalPieces: this.getTotalPieces(),

							//Order ID
							orderID: this.orderId,

							//Selected Service
							serviceID: this.selectedService


						})
						.then(function (response) {

							self.displayLoadShipmentSpinner = 0;

							if( response.data.errors.length === 0) {
								self.pins = response.data.pins;
								self.getLabel(self.pins[0], '#return-shipment-labels');

								return true;
							}

							self.handleErrors(response.data.errors); 

  						});
					},


					getCompletedShipment: function(shipmentPin, trackingIdentifier) {
						var self = this;

						this.errors = [];
						this.shipmentPin = shipmentPin;
						this.shipmentId = trackingIdentifier  || '';


						axios.post("", {
							action: "getShipmentDetails",
							shipmentPin: this.shipmentPin,
							shipmentId: this.shipmentId,
						})
						.then(function (response) {

							if( response.data.errors.length === 0) {

								self.shipmentCreated = response.data.shipment.date;
								self.shipmentService = response.data.shipment.service || '';
								self.shipmentOrderId = response.data.shipment.orderId;
								self.shipmentReceiverName = response.data.shipment.name || '';
								self.shipmentReceiverAddress = response.data.shipment.address || '';
								self.shipmentReceiverCity = response.data.shipment.city || '';
								self.shipmentReceiverPostalCode = response.data.shipment.postalCode || '';
								self.shipmentSenderAddress = response.data.shipment.senderAddress;
								self.shipmentSenderCity = response.data.shipment.senderCity;
								self.shipmentSenderId = response.data.shipment.senderLocationId;
								self.shipmentSenderPostalCode = response.data.shipment.senderPostalCode;
								self.shipmentVoided = response.data.shipment.voided;
								self.displayLoadServicesSpinner = 0;

								return true;
							}

							self.handleErrors(response.data.errors);

  						});
					},

					shipAgain: function(orderId, locationId) {
						this.orderId = orderId;
						this.senderId = locationId;

						this.getReceiverByOrderId();
						this.getSenderLocation();

						this.packages = [];
						this.services = [];
						this.shipmentPin = '';
						this.pdfUrl = '';

						this.activateTab('shipment');
					},


					reprintLabel: function(labelName, shipmentPin) {	

						this.shipmentPin = shipmentPin;
						this.pdfUrl = './labels/' + labelName;

						this.injectPdf(this.pdfUrl, '#reprint-label');

					},


					getLabel: function(shipmentPin, injectInElement) {
						var self = this;

						this.pdfLabels = [];

						console.log("in the getLabel function");
						console.log("ShipmentPin: " + shipmentPin);
						console.log("injectInElement: " + injectInElement);



						shipmentPin = shipmentPin || this.shipmentPin;
						injectInElement = injectInElement || this.injectPdfInElementId;

						axios.post("", {
							action: "printLabel",
							pin: shipmentPin,
						})
						.then(function (response) {

							if(typeof response.data.pdfUrl == 'undefined' || !response.data.pdfUrl) {
								self.handleErrors("Label for this PIN is not available"); 
								return;
							}

							if( response.data.errors.length === 0) {

								self.pdfUrl = response.data.pdfUrl;
								self.injectPdf(self.pdfUrl, injectInElement);

								setTimeout(function(){ 

									self.pdfLabels.push(self.pdfUrl);
									return true;

								}, 2000);
							}

							self.handleErrors(response.data.errors); 

  						});
					},


					getManifest: function() {
						var self = this;

						this.errors = [];		
						this.displayLoadManifestSpinner = 1;

						axios.post("", {
							action: "getManifest",
							senderLocationId: this.senderLocationId,
							senderPostalCode: this.senderPostalCode,
							senderPhoneAreaCode: this.senderPhoneAreaCode,
							senderPhone: this.senderPhone,
							senderCompany: this.senderCompany,
							senderAddress: this.senderStreetNumber + ' ' + this.senderStreetName,
							senderCity: this.senderCity,
							senderProvince: this.senderProvince
						})
						.then(function (response) {

							self.displayLoadManifestSpinner = 0;

							if( response.data.errors.length === 0) {

								self.pdfUrl = response.data.pdfUrl;
								self.injectPdfInElementId = '#manifest-pdf';				
								self.injectPdf();

								return true;
							}

							self.handleErrors(response.data.errors); 
							if(response.data.pdfUrl !== '') {
								
								self.pdfUrl = response.data.pdfUrl;
								self.injectPdfInElementId = '#manifest-pdf';				
								self.injectPdf();
							}
  						});
					},


					injectPdf: function(pdfUrl, injectInElement) {

						pdfUrl = pdfUrl || this.pdfUrl;
						injectInElement = injectInElement || this.injectPdfInElementId;

						PDFObject.embed(pdfUrl, injectInElement);
					},


					handleErrors: function(errors) {
						this.errors = [];

						if(typeof errors === "undefined") {
							return;
						}

						if(errors.constructor === Array) {
							this.errors = errors;
							return true;
						}

						this.errors.push(errors);
					},


					getOrders: function(date) {
						var self = this;

						this.orders = [];
						this.ordersDate = this.getSelectedDate().format("YYYY-MM-DD");

						if(date === 'today') {
							this.setCurrentDate();
						} 


						axios.post("", {
							action: "getShipmentsByDate",
							date: this.ordersDate
						})
						.then(function (response) {

							if( response.data.errors.length === 0) {
								self.orders = response.data.shipments;
								return true;
							}

							
							self.handleErrors(response.data.errors); 

  						});
					},


					getShippingManifest: function() {
						return false;
					},


					VoidShipment: function() {
						var self = this;
						this.confirmation = '';
						this.displayVoidShipmentSpinner = 1;

						axios.post("", {
							action: "voidShipment",
							shipmentId: this.voidShipmentId,
						})
						.then(function (response) {

							self.displayVoidShipmentSpinner = 0;

							if(response.data.voided === self.voidShipmentId) {

								self.getOrders();
								self.confirmation = "Shipment has been voided";
								return true;
							}

							self.handleErrors(response.data.errors); 
  						});
					},


					sendEmailToCustomer: function() {
						var self = this;

						axios.post("", {
							action: "sendEmail",
							receiverName: this.receiverName,
							receiverEmail: this.receiverEmail,
							receiverEmailBody: this.receiverEmailBody,
							pdfLabels: self.pdfLabels,
							pins: self.pins
						})
						.then(function (response) {

							if(response.data.sent === true) {

								self.emailToCustomerSent = 1;

								self.resetAvailableServices();
								self.resetReceiver();
								return true;
							}

							self.emailToCustomerSent = 2;
  						});
					},


					activateTab: function(id) {
 
						$('.nav-tabs a[href="#' + id + '"]').tab('show');
						window.scrollTo(0, 0);
					},


					activateDatePicker: function() {
						$('#datetimepicker').datetimepicker({
		            		format: 'L'
		        		});
					},

					retrieveManifestForDate: function() {
						console.log('Open Manifest tab');
						this.activateTab('admin');
					},


					getManifestForDate: function() {
						var self = this;
						axios.post("", {
							action: "getManifestForDate",
							date: this.retriveManifestForDate,
						})
						.then(function(response) {

							if(response.data.length < 2){
								self.printManifestId = response.data[0];
								return;
							}

							self.manifests = response.data;

							return true;
						});
					},

					getGroups: function() {
						console.log('getGroups');

						axios.post("", {
							action: "getGroups"
						})
						.then(function (response) {	
							console.log(response.data);
  						});
					},

					printManifest: function() {

						var self = this;

						this.errors = [];		
						this.displayLoadManifestSpinner = 1;

						axios.post("", {
							action: "printManifestId",
							manifestIds: this.printManifestId, 
						})
						.then(function (response) {	


							console.log(response);
							console.log(response.data);
							console.log(response.pdfUrl);

							self.displayLoadManifestSpinner = 0;

							if( typeof response.data.errors === 'undefined' || response.data.errors.length === 0) {

								self.pdfUrl = response.data.pdfUrl;

								console.log("Response data pdfUrl: " + response.data.pdfUrl);

								self.injectPdfInElementId = '#manifest-pdf';				
								self.injectPdf();

								return true;
							}

							self.handleErrors(response.data.errors); 
  						});
					},


					searchByOrderID: function() {

						if(this.orderIDSearch.length === 0 || this.orderIDSearch === '0'){
							this.errors = ['Order ID is empty']
							return;
						}

						var self = this;
						this.orders = [];
						this.errors = [];
						this.displayLoadShipmentSpinner = 1;

						axios.post("", {
							action: "searchByOrderID",
							orderIDSearch: this.orderIDSearch
						})
						.then(function (response) {
							self.displayLoadShipmentSpinner = 0;

							if( response.data.errors.length === 0) {
								self.orders = response.data.shipments;
								self.displaySearchResults = true;

								console.log(self.orders)
								return true;
							}
							self.handleErrors(response.data.errors); 
  						});
					},


					searchByTrackingNumber: function() { 
						if(this.trackingNumberSearch.length === 0 || this.trackingNumberSearch === '0'){
							this.errors = ['Tracking Number is empty']
							return;
						}

						var self = this;
						this.orders = [];
						this.errors = [];
						this.displayLoadShipmentSpinner = 1;

						axios.post("", {
							action: "searchByTrackingNumber",
							trackingNumberSearch: this.trackingNumberSearch
						})
						.then(function (response) {
							self.displayLoadShipmentSpinner = 0;

							if( response.data.errors.length === 0) {
								self.orders = response.data.shipments;
								self.displaySearchResults = true;
								return true;
							}
							
							self.handleErrors(response.data.errors); 
  						});
					},


					searchByPackageReference: function() {
						if(this.packageReferenceSearch.length === 0){
							this.errors = ['Package Reference is empty']
							return;
						}

						var self = this;
						this.orders = [];
						this.errors = [];
						this.displayLoadShipmentSpinner = 1;

						axios.post("", {
							action: "searchByPackageReference",
							packageReferenceSearch: this.packageReferenceSearch
						})
						.then(function (response) {
							self.displayLoadShipmentSpinner = 0;

							if( response.data.errors.length === 0) {
								self.orders = response.data.shipments;
								self.displaySearchResults = true;
								return true;
							}
							
							self.handleErrors(response.data.errors); 
  						});
					},


					searchByPhoneNumber: function() {
						if(this.phoneNumberSearch.length === 0){
							this.errors = ['Phone Number is empty']
							return;
						}

						var self = this;
						this.orders = [];
						this.errors = [];
						this.displayLoadShipmentSpinner = 1;

						axios.post("", {
							action: "searchByPhoneNumber",
							phoneNumberSearch: this.phoneNumberSearch
						})
						.then(function (response) {
							self.displayLoadShipmentSpinner = 0;

							if( response.data.errors.length === 0) {
								self.orders = response.data.shipments;
								self.displaySearchResults = true;
								return true;
							}
							
							self.handleErrors(response.data.errors); 
  						});
					},
					

					searchByCustomerName: function() {
						if(this.customerNameSearch.length === 0){
							this.errors = ['Customer Name is empty']
							return;
						}

						var self = this;
						this.orders = [];
						this.errors = [];
						this.displayLoadShipmentSpinner = 1;

						axios.post("", {
							action: "searchByCustomerName",
							customerNameSearch: this.customerNameSearch
						})
						.then(function (response) {
							self.displayLoadShipmentSpinner = 0;

							if( response.data.errors.length === 0) {
								self.orders = response.data.shipments;
								self.displaySearchResults = true;
								return true;
							}
							
							self.handleErrors(response.data.errors); 
  						});
					},

					schedulePickUp: function(){
						var self = this;

						axios.post("", {

							action: "schedulePickUp",
							pickUpDate: this.getScheduledPickupDate(),
							anyTimeAfter: this.anyTimeAfter,
							untilTime: this.untilTime,
							pickUpTotalPieces: this.pickUpTotalPieces,
							pickUpLocation: this.pickUpLocation,
						
						}).then(function (response) {
    						self.pickUps = response.data.pickups;
    						self.handleErrors(response.data.errors); 

							self.anyTimeAfter = '12:00';
							self.untilTime = '17:00';
							self.pickUpTotalPieces = 1;

    						//Display confirmation modal
    						if(response.data.confirmationNumber) {
    							$('#pickupConfirmationModal').modal('show');
    						}
  						});
					},

					getSchedulePickUps: function(){ 
						var self = this;

						axios.post("", {

							action: "getSchedulePickUps"
						
						}).then(function (response) {
    						self.pickUps = response.data.pickups; 
  						});
					},

					cancelSchedulePickUp: function(confirmationNumber){ 
						var self = this;

						axios.post("", {

							action: "cancelSchedulePickUp",
							confirmationNumber: confirmationNumber
						
						}).then(function (response) {
    						self.pickUps = response.data.pickups; 
    						self.handleErrors(response.data.errors); 
  						});
					},

					pickTotalPiecesOnChangeHandler: function() {
						this.pickUpTotalPieces = this.pickUpTotalPieces > 1 ? this.pickUpTotalPieces : 1;
					},		

					preferredTimeOnChangeHandler: function() {
						this.anyTimeAfter = parseInt(this.anyTimeAfter) < 12 ? '12:00' : this.anyTimeAfter;
						this.anyTimeAfter = parseInt(this.anyTimeAfter) > 16 ? '16:00' : this.anyTimeAfter;
					},		

					untilTimeOnChangeHandler: function() {
						this.untilTime = parseInt(this.untilTime) > 17 ? '17:00' : this.untilTime;
					}


				}
			});


