
<div class="modal fade" id="showShipmentModal" tabindex="-1" role="dialog" aria-labelledby="showShipmentModal" aria-hidden="true" style="z-index: 1400;">
  	<div class="modal-dialog" role="document">
	    <div class="modal-content">

		    <div class="modal-header">
		        <h6 class="modal-title">
		        	Package Tracking Number:
		        		<a target="_blank" :href="'https://www.canadapost.ca/trackweb/en#/search?searchFor=' + shipmentPin">
		        			{{ shipmentPin }} <img src="https://www.canadapost.ca/cpc/assets/cpc/img/logos/cpc-main-logo.svg" height="20">
		        		</a>
		       	</h6>


		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"> 
		          <span aria-hidden="true">&times;</span>
		        </button>
		    </div>


		    <div class="modal-body">

			    <div class="alert alert-danger" v-for="error in errors">
					{{ error }}<br/>
				</div>
				
				<small>
					<div class="alert alert-danger alert-narrow" v-if="shipmentVoided > 0">
						Voided
					</div>

					Shipment created: {{ shipmentCreated }}
	
					<span v-if="shipmentService !== ''">
						<br/>Selected Service: {{ shipmentService }}
					</span>

					<br/>Sender Location: 
						<a target="_blank" :href="'https://manager.long-mcquade.com/Locations_Modify.php?action=edit&LocationsID=' + shipmentSenderId">
							{{ shipmentSenderCity }}, {{ shipmentSenderAddress }}, {{ shipmentSenderPostalCode }}
						</a>

					<br/>Order ID: 
						<a target="_blank" :href="'https://manager.long-mcquade.com/Orders_Modify.php?action=edit&OrdersID=' + shipmentOrderId">
							{{ shipmentOrderId }}
						</a>
				</small>

		    </div>


		    <div class="modal-footer">
		    	<button type="button" 
		    		class="btn btn-warning" 
		    		data-dismiss="modal" 
		    		@click="shipAgain(shipmentOrderId, shipmentSenderId)">Ship Again</button>


		    	<button type="button" 
		    		class="btn btn-secondary" 
		    		data-toggle="modal" 
					data-target="#printLabelModal" 
					v-if="shipmentVoided < 1"
		    		@click="reprintLabel(shipmentPin)">Print Label</button>
		    		

		    	<button type="button" 
		    		class="btn btn-danger"  
		    		data-toggle="modal" 
			    	data-target="#voidShipmentModal"
			    	v-if="shipmentVoided < 1"
		    		@click="{ voidShipmentPin = shipmentPin; errors = []; }">Void Shipment</button>		 		    		    	
		    </div>

	    </div>
  	</div>
</div>