
<div class="modal fade" id="showSearchShipmentModal" tabindex="-1" role="dialog" aria-labelledby="showSearchShipmentModal" aria-hidden="true" style="z-index: 1400;">
  	<div class="modal-dialog" role="document">
	    <div class="modal-content">

		    <div class="modal-header">
		        <h6 class="modal-title">
		        	Tracking Number:
		        		<a target="_blank" :href="searchOrder.trackingUrl">
		        			{{ searchOrder.pin }}
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
					<div class="alert alert-danger alert-narrow" v-if="searchOrder.void == 1">
						Voided
					</div>

					Shipment created on  <b>{{ searchOrder.date }}</b>

					<span v-if="searchOrder.name">
						<br/>Shipped to: <b>{{ searchOrder.name }}, {{ searchOrder.address }}, {{ searchOrder.city }}, {{ searchOrder.postalCode }}</b>
					</span>

					<br/>Selected Service: 
						<span v-if="searchOrder.serivce !== ''"><b>{{ searchOrder.serivce }}</b></span>
						<span v-else> n/a </span>

					<br/>Sender Location: 
							<b>{{ searchOrder.storeCity }}, {{ searchOrder.storeAddress }}, {{ searchOrder.storePostalCode }}</b>

					<br/>Order ID: <b>{{ searchOrder.orderId }}</b>
				</small>

		    </div>

	    </div>
  	</div>
</div>